<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\RegistrationModel;
use App\Models\PaymentModel;
use App\Models\PilgrimModel;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class PaymentController extends BaseController
{
    private function setupMidtrans()
    {
        Config::$serverKey = env('midtrans.serverKey');
        Config::$isProduction = filter_var(env('midtrans.isProduction'), FILTER_VALIDATE_BOOLEAN);
        Config::$isSanitized = filter_var(env('midtrans.isSanitized'), FILTER_VALIDATE_BOOLEAN);
        Config::$is3ds = filter_var(env('midtrans.is3ds'), FILTER_VALIDATE_BOOLEAN);
    }

    private function mapPaymentStatus($transactionStatus, $fraudStatus = null)
    {
        if ($transactionStatus === 'settlement') {
            return 'Lunas';
        }

        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'accept' ? 'Lunas' : 'Menunggu Verifikasi';
        }

        if ($transactionStatus === 'pending') {
            return 'Menunggu Pembayaran';
        }

        if (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
            return 'Gagal';
        }

        return 'Menunggu Pembayaran';
    }

    private function getVaNumber($status)
    {
        if (!empty($status['va_numbers'][0]['va_number'])) {
            return $status['va_numbers'][0]['va_number'];
        }

        if (!empty($status['permata_va_number'])) {
            return $status['permata_va_number'];
        }

        return null;
    }

    private function syncMidtransStatus($payment, $registrationModel, $paymentModel)
    {
        if (empty($payment['order_id'])) {
            return;
        }

        try {
            $this->setupMidtrans();

            $status = Transaction::status($payment['order_id']);
            $status = json_decode(json_encode($status), true);

            $transactionStatus = $status['transaction_status'] ?? 'pending';
            $fraudStatus       = $status['fraud_status'] ?? null;
            $paymentType       = $status['payment_type'] ?? null;

            $paymentStatus = $this->mapPaymentStatus($transactionStatus, $fraudStatus);

            $paymentModel->update($payment['id'], [
                'transaction_id'     => $status['transaction_id'] ?? null,
                'payment_type'       => $paymentType,
                'transaction_status' => $transactionStatus,
                'fraud_status'       => $fraudStatus,
                'va_number'          => $this->getVaNumber($status),
                'payment_code'       => $status['payment_code'] ?? null,
                'raw_response'       => json_encode($status),
                'paid_at'            => $paymentStatus === 'Lunas'
                    ? date('Y-m-d H:i:s')
                    : ($payment['paid_at'] ?? null),
                'expired_at'         => !empty($status['expiry_time'])
                    ? date('Y-m-d H:i:s', strtotime($status['expiry_time']))
                    : ($payment['expired_at'] ?? null),
            ]);

            $registrationModel->update($payment['registration_id'], [
                'payment_status' => $paymentStatus,
            ]);
            if ($paymentStatus === 'Lunas') {
                $this->sendPaymentSuccessEmailIfNeeded($payment['id']);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Gagal sync status Midtrans: ' . $e->getMessage());
        }
    }

    public function show($registrationNo)
    {
        if (site_setting('payment_enabled', '0') !== '1') {
            return redirect()
                ->to('/paket')
                ->with('error', 'Pembayaran online sementara dinonaktifkan. Silakan pilih paket dan konsultasikan melalui WhatsApp.');
        }

        if (!session()->get('jamaah_logged_in')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $registrationModel = new RegistrationModel();
        $paymentModel      = new PaymentModel();
        $pilgrimModel      = new PilgrimModel();

        $registration = $registrationModel->getByRegistrationNo($registrationNo);

        if (!$registration) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pendaftaran tidak ditemukan');
        }

        if ((int) $registration['user_id'] !== (int) session()->get('jamaah_user_id')) {
            return redirect()
                ->to('/')
                ->with('error', 'Anda tidak memiliki akses ke pembayaran ini.');
        }

        $payment = $paymentModel->getByRegistration($registration['id']);

        if (!$payment) {
            $this->setupMidtrans();

            $orderId = $registration['registration_no'] . '-' . time();
            $amount  = (int) $registration['total_amount'];

            $pilgrims = $pilgrimModel->getByRegistration($registration['id']);
            $leader   = $pilgrims[0] ?? null;

            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => $amount,
                ],
                'item_details' => [
                    [
                        'id'       => $registration['package_id'],
                        'price'    => $amount,
                        'quantity' => 1,
                        'name'     => $registration['package_name'] ?? 'Paket Umroh',
                    ],
                ],
                'customer_details' => [
                    'first_name' => $leader['full_name'] ?? session()->get('jamaah_name'),
                    'email'      => $leader['email'] ?? session()->get('jamaah_email'),
                    'phone'      => $leader['phone'] ?? '',
                ],
            ];

            $snapTransaction = Snap::createTransaction($params);

            $paymentModel->insert([
                'registration_id'     => $registration['id'],
                'order_id'            => $orderId,
                'gross_amount'        => $amount,
                'payment_type'        => null,
                'transaction_status'  => 'pending',
                'fraud_status'        => null,
                'snap_token'          => $snapTransaction->token,
                'snap_redirect_url'   => $snapTransaction->redirect_url,
                'raw_response'        => null,
            ]);

            $registrationModel->update($registration['id'], [
                'payment_status' => 'Menunggu Pembayaran',
            ]);

            $payment = $paymentModel->getByRegistration($registration['id']);
            $registration = $registrationModel->getByRegistrationNo($registrationNo);
        }

        if ($payment && ($payment['transaction_status'] ?? '') !== 'settlement') {
            $this->syncMidtransStatus($payment, $registrationModel, $paymentModel);

            $payment = $paymentModel->getByRegistration($registration['id']);
            $registration = $registrationModel->getByRegistrationNo($registrationNo);
        }

        $data = [
            'title'        => 'Pembayaran Midtrans - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'registration' => $registration,
            'payment'      => $payment,
            'clientKey'    => env('midtrans.clientKey'),
            'isProduction' => filter_var(env('midtrans.isProduction'), FILTER_VALIDATE_BOOLEAN),
        ];

        return view('frontend/payments/midtrans', $data);
    }

public function notification()
{
    $serverKey = env('midtrans.serverKey');
    $notification = json_decode($this->request->getBody(), true);

    if (!$notification) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['message' => 'Invalid notification payload']);
    }

    $orderId      = $notification['order_id'] ?? null;
    $statusCode   = $notification['status_code'] ?? null;
    $grossAmount  = $notification['gross_amount'] ?? null;
    $signatureKey = $notification['signature_key'] ?? null;

    $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

    if ($signatureKey !== $validSignature) {
        return $this->response
            ->setStatusCode(403)
            ->setJSON(['message' => 'Invalid signature']);
    }

    $transactionStatus = $notification['transaction_status'] ?? 'pending';
    $fraudStatus       = $notification['fraud_status'] ?? null;
    $paymentType       = $notification['payment_type'] ?? null;

    $paymentModel      = new PaymentModel();
    $registrationModel = new RegistrationModel();

    $payment = $paymentModel->getByOrderId($orderId);

    if (!$payment) {
        return $this->response
            ->setStatusCode(404)
            ->setJSON(['message' => 'Payment not found']);
    }

    /*
     * Cek status lama sebelum update.
     * Ini untuk mencegah notifikasi pembayaran berhasil muncul berkali-kali
     * kalau Midtrans mengirim callback lebih dari sekali.
     */
    $successTransactionStatuses = ['settlement', 'capture'];

    $oldTransactionStatus = strtolower($payment['transaction_status'] ?? '');
    $wasAlreadyPaid = !empty($payment['paid_at'])
        || in_array($oldTransactionStatus, $successTransactionStatuses, true);

    $paymentStatus = $this->mapPaymentStatus($transactionStatus, $fraudStatus);

    $paymentModel->update($payment['id'], [
        'transaction_id'     => $notification['transaction_id'] ?? null,
        'payment_type'       => $paymentType,
        'transaction_status' => $transactionStatus,
        'fraud_status'       => $fraudStatus,
        'va_number'          => $this->getVaNumber($notification),
        'payment_code'       => $notification['payment_code'] ?? null,
        'raw_response'       => json_encode($notification),
        'paid_at'            => $paymentStatus === 'Lunas' ? date('Y-m-d H:i:s') : null,
        'expired_at'         => !empty($notification['expiry_time'])
            ? date('Y-m-d H:i:s', strtotime($notification['expiry_time']))
            : null,
    ]);

    $registrationModel->update($payment['registration_id'], [
        'payment_status' => $paymentStatus,
    ]);

    /*
     * Kirim email dan buat notifikasi hanya ketika pembayaran benar-benar lunas.
     */
    if ($paymentStatus === 'Lunas') {
        $this->sendPaymentSuccessEmailIfNeeded($payment['id']);

        if (!$wasAlreadyPaid && function_exists('create_notification')) {
            create_notification(
                'Pembayaran Berhasil',
                'Pembayaran jamaah untuk order ' . ($payment['order_id'] ?? $orderId) . ' telah berhasil diterima.',
                'payment',
                (int) $payment['id']
            );
        }
    }

    return $this->response->setJSON([
        'message' => 'Notification processed',
    ]);
}

    private function getPaymentDetailForEmail($paymentId)
    {
        $db = \Config\Database::connect();

        return $db->table('payments')
            ->select("
            payments.*,
            payments.id AS payment_id,
            registrations.registration_no,
            registrations.payment_status,
            packages.name AS package_name,
            users.name AS user_name,
            users.email AS user_email
        ", false)
            ->join('registrations', 'registrations.id = payments.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->where('payments.id', $paymentId)
            ->get()
            ->getRowArray();
    }

    private function sendPaymentSuccessEmail(array $payment): bool
    {
        if (empty($payment['user_email'])) {
            return false;
        }

        try {
            $email = \Config\Services::email();

            $email->setTo($payment['user_email']);
            $email->setSubject('Pembayaran Anda Telah Lunas - ' . site_setting('site_name', 'Travel Umroh & Haji'));
            $email->setMessage(view('emails/payment_success', [
                'payment' => $payment,
            ]));

            return $email->send(false);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal kirim email pembayaran lunas: ' . $e->getMessage());
            return false;
        }
    }

    private function sendPaymentSuccessEmailIfNeeded($paymentId): void
    {
        $paymentModel = new \App\Models\PaymentModel();

        $payment = $paymentModel->find($paymentId);

        if (!$payment) {
            return;
        }

        if (!empty($payment['paid_email_sent_at'])) {
            return;
        }

        $paymentDetail = $this->getPaymentDetailForEmail($paymentId);

        if (!$paymentDetail) {
            return;
        }

        if (($paymentDetail['payment_status'] ?? '') !== 'Lunas') {
            return;
        }

        $emailSent = $this->sendPaymentSuccessEmail($paymentDetail);

        if ($emailSent) {
            $paymentModel->update($paymentId, [
                'paid_email_sent_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
