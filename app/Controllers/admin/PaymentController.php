<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\RegistrationModel;

class PaymentController extends BaseController
{
    public function index()
    {
        $paymentModel = new PaymentModel();

        $payments = $paymentModel
            ->select('
                payments.*,
                registrations.registration_no,
                registrations.payment_status,
                registrations.registration_status,
                packages.name AS package_name,
                users.name AS user_name,
                users.email AS user_email
            ')
            ->join('registrations', 'registrations.id = payments.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->orderBy('payments.id', 'DESC')
            ->findAll();

        return view('admin/payments/index', [
            'title'    => 'Data Pembayaran Jamaah',
            'payments' => $payments,
        ]);
    }

    public function detail($id)
    {
        $paymentModel = new PaymentModel();

        $payment = $paymentModel
            ->select('
                payments.*,
                registrations.registration_no,
                registrations.total_amount,
                registrations.payment_status,
                registrations.registration_status,
                packages.name AS package_name,
                users.name AS user_name,
                users.email AS user_email,
                users.phone AS user_phone
            ')
            ->join('registrations', 'registrations.id = payments.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->where('payments.id', $id)
            ->first();

        if (!$payment) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data pembayaran tidak ditemukan');
        }

        return view('admin/payments/detail', [
            'title'   => 'Detail Pembayaran Jamaah',
            'payment' => $payment,
        ]);
    }
}