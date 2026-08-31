<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends BaseController
{
    public function index()
    {
        return view('admin/reports/index', [
            'title' => 'Laporan Admin - ' . site_setting('site_name', 'Travel Umroh & Haji'),
        ]);
    }

    private function cleanValue($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $value = strip_tags((string) $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }


    private function formatDateValue($date, string $format = 'd-m-Y H:i'): string
    {
        if (empty($date)) {
            return '-';
        }

        return date($format, strtotime($date));
    }

    private function formatDateRange($startDate, $endDate): string
    {
        if (empty($startDate) && empty($endDate)) {
            return '-';
        }

        if (!empty($startDate) && !empty($endDate)) {
            return date('d-m-Y', strtotime($startDate)) . ' s/d ' . date('d-m-Y', strtotime($endDate));
        }

        return !empty($startDate) ? date('d-m-Y', strtotime($startDate)) : '-';
    }

    private function getDateFilters(): array
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        if (!empty($startDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = null;
        }

        if (!empty($endDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = null;
        }

        return [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ];
    }

    private function applyDateFilter($builder, string $column)
    {
        $filters = $this->getDateFilters();

        if (!empty($filters['start_date'])) {
            $builder->where($column . ' >=', $filters['start_date'] . ' 00:00:00');
        }

        if (!empty($filters['end_date'])) {
            $builder->where($column . ' <=', $filters['end_date'] . ' 23:59:59');
        }

        return $builder;
    }

    private function downloadExcel(
        string $filename,
        string $title,
        string $sheetName,
        array $headers,
        array $rows,
        array $currencyColumns = []
    ) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle($sheetName);

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $lastRow = count($rows) + 6;

        // =============================
        // TITLE
        // =============================
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A1', $title);

        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->setCellValue('A2', site_setting('site_name', 'Travel Umroh & Haji'));

        $sheet->mergeCells("A3:{$lastColumn}3");
        $filters = $this->getDateFilters();

        $periodText = 'Semua Periode';

        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $periodText =
                (!empty($filters['start_date']) ? date('d-m-Y', strtotime($filters['start_date'])) : 'Awal Data')
                . ' s/d '
                . (!empty($filters['end_date']) ? date('d-m-Y', strtotime($filters['end_date'])) : 'Sekarang');
        }

        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d-m-Y H:i:s') . ' | Periode: ' . $periodText);

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '0D3B2E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => [
                'size' => 11,
                'color' => ['rgb' => '666666'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // =============================
        // HEADER TABLE
        // =============================
        $headerRow = 5;

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D3B2E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // =============================
        // DATA TABLE
        // =============================
        $startDataRow = 6;

        foreach ($rows as $rowIndex => $row) {
            $currentRow = $startDataRow + $rowIndex;

            foreach ($row as $columnIndex => $value) {
                $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $cell = $column . $currentRow;

                $sheet->setCellValue($cell, $value);
            }
        }

        // =============================
        // STYLE TABLE
        // =============================
        if (!empty($rows)) {
            $sheet->getStyle("A{$headerRow}:{$lastColumn}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D9D9D9'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle("A{$startDataRow}:{$lastColumn}{$lastRow}")->applyFromArray([
                'font' => [
                    'size' => 10,
                    'color' => ['rgb' => '1A1A1A'],
                ],
            ]);
        }

        // =============================
        // FORMAT CURRENCY
        // =============================
        foreach ($currencyColumns as $columnIndex) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);

            $sheet->getStyle("{$column}{$startDataRow}:{$column}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');
        }

        // =============================
        // AUTO FILTER & FREEZE
        // =============================
        $sheet->setAutoFilter("A{$headerRow}:{$lastColumn}{$headerRow}");
        $sheet->freezePane('A6');

        // =============================
        // AUTO SIZE COLUMN
        // =============================
        for ($i = 1; $i <= count($headers); $i++) {
            $column = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // =============================
        // ROW HEIGHT
        // =============================
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension($headerRow)->setRowHeight(24);

        // =============================
        // ALIGNMENT
        // =============================
        $sheet->getStyle("A{$startDataRow}:A{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // =============================
        // SAVE TEMP FILE
        // =============================
        $writer = new Xlsx($spreadsheet);

        $folder = WRITEPATH . 'reports';

        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        $filePath = $folder . '/' . $filename;

        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function exportRegistrations()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('registrations')
            ->select("
        registrations.*,
        packages.name AS package_name,
        users.name AS user_name,
        users.email AS user_email,
        users.phone AS user_phone,
        users.nik AS user_nik,
        package_departures.departure_date,
        package_departures.return_date
    ", false)
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->join('package_departures', 'package_departures.id = registrations.departure_id', 'left')
            ->where('registrations.deleted_at', null);

        $builder = $this->applyDateFilter($builder, 'registrations.created_at');

        $registrations = $builder
            ->orderBy('registrations.id', 'DESC')
            ->get()
            ->getResultArray();

        $headers = [
            'No',
            'Nomor Pendaftaran',
            'Nama Jamaah',
            'Email',
            'Nomor HP',
            'NIK',
            'Paket',
            'Jadwal Keberangkatan',
            'Jumlah Peserta',
            'Total Tagihan',
            'Status Pendaftaran',
            'Status Pembayaran',
            'Tanggal Pendaftaran',
            'Catatan',
        ];

        $rows = [];

        foreach ($registrations as $index => $registration) {
            $rows[] = [
                $index + 1,
                $this->cleanValue($registration['registration_no'] ?? '-'),
                $this->cleanValue($registration['user_name'] ?? '-'),
                $this->cleanValue($registration['user_email'] ?? '-'),
                $this->cleanValue($registration['user_phone'] ?? '-'),
                $this->cleanValue($registration['user_nik'] ?? '-'),
                $this->cleanValue($registration['package_name'] ?? '-'),
                $this->formatDateRange($registration['departure_date'] ?? null, $registration['return_date'] ?? null),
                (int) ($registration['total_participants'] ?? 0),
                (float) ($registration['total_amount'] ?? 0),
                $this->cleanValue($registration['registration_status'] ?? '-'),
                $this->cleanValue($registration['payment_status'] ?? '-'),
                $this->formatDateValue($registration['created_at'] ?? null),
                $this->cleanValue($registration['note'] ?? '-'),
            ];
        }

        return $this->downloadExcel(
            'laporan-pendaftaran-jamaah-' . date('Ymd-His') . '.xlsx',
            'LAPORAN PENDAFTARAN JAMAAH',
            'Pendaftaran',
            $headers,
            $rows,
            [10]
        );
    }

    public function exportPayments()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('payments')
            ->select("
        payments.*,
        registrations.registration_no,
        registrations.payment_status,
        packages.name AS package_name,
        users.name AS user_name,
        users.email AS user_email
    ", false)
            ->join('registrations', 'registrations.id = payments.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left');

        $builder = $this->applyDateFilter($builder, 'payments.created_at');

        $payments = $builder
            ->orderBy('payments.id', 'DESC')
            ->get()
            ->getResultArray();

        $headers = [
            'No',
            'Nomor Pendaftaran',
            'Nama Jamaah',
            'Email',
            'Paket',
            'Order ID',
            'Transaction ID',
            'Metode Pembayaran',
            'Nominal Pembayaran',
            'Status Midtrans',
            'Status Sistem',
            'VA Number',
            'Kode Pembayaran',
            'Tanggal Bayar',
            'Tanggal Kedaluwarsa',
            'Tanggal Transaksi',
        ];

        $rows = [];

        foreach ($payments as $index => $payment) {
            $rows[] = [
                $index + 1,
                $this->cleanValue($payment['registration_no'] ?? '-'),
                $this->cleanValue($payment['user_name'] ?? '-'),
                $this->cleanValue($payment['user_email'] ?? '-'),
                $this->cleanValue($payment['package_name'] ?? '-'),
                $this->cleanValue($payment['order_id'] ?? '-'),
                $this->cleanValue($payment['transaction_id'] ?? '-'),
                $this->cleanValue($payment['payment_type'] ?? '-'),
                (float) ($payment['gross_amount'] ?? 0),
                $this->cleanValue($payment['transaction_status'] ?? '-'),
                $this->cleanValue($payment['payment_status'] ?? '-'),
                $this->cleanValue($payment['va_number'] ?? '-'),
                $this->cleanValue($payment['payment_code'] ?? '-'),
                $this->formatDateValue($payment['paid_at'] ?? null),
                $this->formatDateValue($payment['expired_at'] ?? null),
                $this->formatDateValue($payment['created_at'] ?? null),
            ];
        }

        return $this->downloadExcel(
            'laporan-pembayaran-' . date('Ymd-His') . '.xlsx',
            'LAPORAN PEMBAYARAN JAMAAH',
            'Pembayaran',
            $headers,
            $rows,
            [9]
        );
    }

    public function exportDocuments()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('pilgrim_documents')
            ->select("
        pilgrim_documents.*,
        registrations.registration_no,
        registrations.registration_status,
        registrations.payment_status,
        packages.name AS package_name,
        users.name AS user_name,
        users.email AS user_email,
        pilgrims.full_name AS pilgrim_name,
        pilgrims.nik AS pilgrim_nik
    ", false)
            ->join('registrations', 'registrations.id = pilgrim_documents.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->join('pilgrims', 'pilgrims.id = pilgrim_documents.pilgrim_id', 'left');

        $builder = $this->applyDateFilter($builder, 'pilgrim_documents.created_at');

        $documents = $builder
            ->orderBy('pilgrim_documents.id', 'DESC')
            ->get()
            ->getResultArray();

        $headers = [
            'No',
            'Nomor Pendaftaran',
            'Nama Akun',
            'Email Akun',
            'Nama Jamaah',
            'NIK Jamaah',
            'Paket',
            'Jenis Dokumen',
            'Status Dokumen',
            'Catatan Admin',
            'Link File Dokumen',
            'Status Pendaftaran',
            'Status Pembayaran',
            'Tanggal Verifikasi',
            'Tanggal Upload',
        ];

        $rows = [];

        foreach ($documents as $index => $document) {
            $rows[] = [
                $index + 1,
                $this->cleanValue($document['registration_no'] ?? '-'),
                $this->cleanValue($document['user_name'] ?? '-'),
                $this->cleanValue($document['user_email'] ?? '-'),
                $this->cleanValue($document['pilgrim_name'] ?? '-'),
                $this->cleanValue($document['pilgrim_nik'] ?? '-'),
                $this->cleanValue($document['package_name'] ?? '-'),
                $this->cleanValue($document['document_type'] ?? '-'),
                $this->cleanValue($document['status'] ?? '-'),
                $this->cleanValue($document['note'] ?? '-'),
                !empty($document['document_file']) ? base_url($document['document_file']) : '-',
                $this->cleanValue($document['registration_status'] ?? '-'),
                $this->cleanValue($document['payment_status'] ?? '-'),
                $this->formatDateValue($document['verified_at'] ?? null),
                $this->formatDateValue($document['created_at'] ?? null),
            ];
        }

        return $this->downloadExcel(
            'laporan-dokumen-jamaah-' . date('Ymd-His') . '.xlsx',
            'LAPORAN DOKUMEN JAMAAH',
            'Dokumen',
            $headers,
            $rows
        );
    }

    private function getPeriodText(): string
    {
        $filters = $this->getDateFilters();

        if (empty($filters['start_date']) && empty($filters['end_date'])) {
            return 'Semua Periode';
        }

        return (!empty($filters['start_date']) ? date('d-m-Y', strtotime($filters['start_date'])) : 'Awal Data')
            . ' s/d '
            . (!empty($filters['end_date']) ? date('d-m-Y', strtotime($filters['end_date'])) : 'Sekarang');
    }

    private function writeReportSheet(
        $sheet,
        string $title,
        array $headers,
        array $rows,
        array $currencyColumns = []
    ): void {
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $headerRow = 5;
        $startDataRow = 6;
        $lastRow = max(count($rows) + 5, 6);

        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A1', $title);

        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->setCellValue('A2', site_setting('site_name', 'Travel Umroh & Haji'));

        $sheet->mergeCells("A3:{$lastColumn}3");
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d-m-Y H:i:s') . ' | Periode: ' . $this->getPeriodText());

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '0D3B2E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => [
                'size' => 11,
                'color' => ['rgb' => '666666'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D3B2E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        foreach ($rows as $rowIndex => $row) {
            $currentRow = $startDataRow + $rowIndex;

            foreach ($row as $columnIndex => $value) {
                $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $sheet->setCellValue($column . $currentRow, $value);
            }
        }

        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D9D9D9'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        foreach ($currencyColumns as $columnIndex) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);

            $sheet->getStyle("{$column}{$startDataRow}:{$column}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');
        }

        $sheet->setAutoFilter("A{$headerRow}:{$lastColumn}{$headerRow}");
        $sheet->freezePane('A6');

        for ($i = 1; $i <= count($headers); $i++) {
            $column = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension($headerRow)->setRowHeight(25);
    }

    private function downloadSpreadsheet(Spreadsheet $spreadsheet, string $filename)
    {
        $writer = new Xlsx($spreadsheet);

        $folder = WRITEPATH . 'reports';

        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        $filePath = $folder . '/' . $filename;

        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    public function exportAll()
    {
        $db = \Config\Database::connect();

        // =============================
        // DATA PENDAFTARAN
        // =============================
        $registrationBuilder = $db->table('registrations')
            ->select("
            registrations.*,
            packages.name AS package_name,
            users.name AS user_name,
            users.email AS user_email,
            users.phone AS user_phone,
            users.nik AS user_nik,
            package_departures.departure_date,
            package_departures.return_date
        ", false)
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->join('package_departures', 'package_departures.id = registrations.departure_id', 'left')
            ->where('registrations.deleted_at', null);

        $registrationBuilder = $this->applyDateFilter($registrationBuilder, 'registrations.created_at');

        $registrations = $registrationBuilder
            ->orderBy('registrations.id', 'DESC')
            ->get()
            ->getResultArray();

        $registrationHeaders = [
            'No',
            'Nomor Pendaftaran',
            'Nama Jamaah',
            'Email',
            'Nomor HP',
            'NIK',
            'Paket',
            'Jadwal Keberangkatan',
            'Jumlah Peserta',
            'Total Tagihan',
            'Status Pendaftaran',
            'Status Pembayaran',
            'Tanggal Pendaftaran',
            'Catatan',
        ];

        $registrationRows = [];

        foreach ($registrations as $index => $registration) {
            $registrationRows[] = [
                $index + 1,
                $this->cleanValue($registration['registration_no'] ?? '-'),
                $this->cleanValue($registration['user_name'] ?? '-'),
                $this->cleanValue($registration['user_email'] ?? '-'),
                $this->cleanValue($registration['user_phone'] ?? '-'),
                $this->cleanValue($registration['user_nik'] ?? '-'),
                $this->cleanValue($registration['package_name'] ?? '-'),
                $this->formatDateRange($registration['departure_date'] ?? null, $registration['return_date'] ?? null),
                (int) ($registration['total_participants'] ?? 0),
                (float) ($registration['total_amount'] ?? 0),
                $this->cleanValue($registration['registration_status'] ?? '-'),
                $this->cleanValue($registration['payment_status'] ?? '-'),
                $this->formatDateValue($registration['created_at'] ?? null),
                $this->cleanValue($registration['note'] ?? '-'),
            ];
        }

        // =============================
        // DATA PEMBAYARAN
        // =============================
        $paymentBuilder = $db->table('payments')
            ->select("
            payments.*,
            registrations.registration_no,
            registrations.payment_status,
            packages.name AS package_name,
            users.name AS user_name,
            users.email AS user_email
        ", false)
            ->join('registrations', 'registrations.id = payments.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left');

        $paymentBuilder = $this->applyDateFilter($paymentBuilder, 'payments.created_at');

        $payments = $paymentBuilder
            ->orderBy('payments.id', 'DESC')
            ->get()
            ->getResultArray();

        $paymentHeaders = [
            'No',
            'Nomor Pendaftaran',
            'Nama Jamaah',
            'Email',
            'Paket',
            'Order ID',
            'Transaction ID',
            'Metode Pembayaran',
            'Nominal Pembayaran',
            'Status Midtrans',
            'Status Sistem',
            'VA Number',
            'Kode Pembayaran',
            'Tanggal Bayar',
            'Tanggal Kedaluwarsa',
            'Tanggal Transaksi',
        ];

        $paymentRows = [];

        foreach ($payments as $index => $payment) {
            $paymentRows[] = [
                $index + 1,
                $this->cleanValue($payment['registration_no'] ?? '-'),
                $this->cleanValue($payment['user_name'] ?? '-'),
                $this->cleanValue($payment['user_email'] ?? '-'),
                $this->cleanValue($payment['package_name'] ?? '-'),
                $this->cleanValue($payment['order_id'] ?? '-'),
                $this->cleanValue($payment['transaction_id'] ?? '-'),
                $this->cleanValue($payment['payment_type'] ?? '-'),
                (float) ($payment['gross_amount'] ?? 0),
                $this->cleanValue($payment['transaction_status'] ?? '-'),
                $this->cleanValue($payment['payment_status'] ?? '-'),
                $this->cleanValue($payment['va_number'] ?? '-'),
                $this->cleanValue($payment['payment_code'] ?? '-'),
                $this->formatDateValue($payment['paid_at'] ?? null),
                $this->formatDateValue($payment['expired_at'] ?? null),
                $this->formatDateValue($payment['created_at'] ?? null),
            ];
        }

        // =============================
        // DATA DOKUMEN
        // =============================
        $documentBuilder = $db->table('pilgrim_documents')
            ->select("
            pilgrim_documents.*,
            registrations.registration_no,
            registrations.registration_status,
            registrations.payment_status,
            packages.name AS package_name,
            users.name AS user_name,
            users.email AS user_email,
            pilgrims.full_name AS pilgrim_name,
            pilgrims.nik AS pilgrim_nik
        ", false)
            ->join('registrations', 'registrations.id = pilgrim_documents.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->join('pilgrims', 'pilgrims.id = pilgrim_documents.pilgrim_id', 'left');

        $documentBuilder = $this->applyDateFilter($documentBuilder, 'pilgrim_documents.created_at');

        $documents = $documentBuilder
            ->orderBy('pilgrim_documents.id', 'DESC')
            ->get()
            ->getResultArray();

        $documentHeaders = [
            'No',
            'Nomor Pendaftaran',
            'Nama Akun',
            'Email Akun',
            'Nama Jamaah',
            'NIK Jamaah',
            'Paket',
            'Jenis Dokumen',
            'Status Dokumen',
            'Catatan Admin',
            'Link File Dokumen',
            'Status Pendaftaran',
            'Status Pembayaran',
            'Tanggal Verifikasi',
            'Tanggal Upload',
        ];

        $documentRows = [];

        foreach ($documents as $index => $document) {
            $documentRows[] = [
                $index + 1,
                $this->cleanValue($document['registration_no'] ?? '-'),
                $this->cleanValue($document['user_name'] ?? '-'),
                $this->cleanValue($document['user_email'] ?? '-'),
                $this->cleanValue($document['pilgrim_name'] ?? '-'),
                $this->cleanValue($document['pilgrim_nik'] ?? '-'),
                $this->cleanValue($document['package_name'] ?? '-'),
                $this->cleanValue($document['document_type'] ?? '-'),
                $this->cleanValue($document['status'] ?? '-'),
                $this->cleanValue($document['note'] ?? '-'),
                !empty($document['document_file']) ? base_url($document['document_file']) : '-',
                $this->cleanValue($document['registration_status'] ?? '-'),
                $this->cleanValue($document['payment_status'] ?? '-'),
                $this->formatDateValue($document['verified_at'] ?? null),
                $this->formatDateValue($document['created_at'] ?? null),
            ];
        }

        // =============================
        // RINGKASAN
        // =============================
        $totalTagihan = 0;
        $totalPembayaran = 0;
        $totalLunas = 0;
        $totalBelumLunas = 0;
        $totalDokumenDiterima = 0;
        $totalDokumenDitolak = 0;
        $totalDokumenPending = 0;

        foreach ($registrations as $registration) {
            $totalTagihan += (float) ($registration['total_amount'] ?? 0);

            if (($registration['payment_status'] ?? '') === 'Lunas') {
                $totalLunas++;
            } else {
                $totalBelumLunas++;
            }
        }

        foreach ($payments as $payment) {
            $totalPembayaran += (float) ($payment['gross_amount'] ?? 0);
        }

        foreach ($documents as $document) {
            if (($document['status'] ?? '') === 'Diterima') {
                $totalDokumenDiterima++;
            } elseif (($document['status'] ?? '') === 'Ditolak') {
                $totalDokumenDitolak++;
            } else {
                $totalDokumenPending++;
            }
        }

        $summaryHeaders = [
            'Keterangan',
            'Jumlah',
        ];

        $summaryRows = [
            ['Periode Laporan', $this->getPeriodText()],
            ['Total Pendaftaran', count($registrations)],
            ['Pendaftaran Lunas', $totalLunas],
            ['Pendaftaran Belum Lunas', $totalBelumLunas],
            ['Total Tagihan Pendaftaran', $totalTagihan],
            ['Total Transaksi Pembayaran', count($payments)],
            ['Total Nominal Pembayaran', $totalPembayaran],
            ['Total Dokumen Upload', count($documents)],
            ['Dokumen Diterima', $totalDokumenDiterima],
            ['Dokumen Ditolak', $totalDokumenDitolak],
            ['Dokumen Menunggu Verifikasi', $totalDokumenPending],
        ];

        // =============================
        // BUILD EXCEL
        // =============================
        $spreadsheet = new Spreadsheet();

        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Ringkasan');

        $this->writeReportSheet(
            $summarySheet,
            'RINGKASAN LAPORAN TRAVEL UMROH & HAJI',
            $summaryHeaders,
            $summaryRows
        );
        $summaryCurrencyRows = [10, 12];

        foreach ($summaryCurrencyRows as $rowNumber) {
            $summarySheet->getStyle('B' . $rowNumber)
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');
        }

        $registrationSheet = $spreadsheet->createSheet();
        $registrationSheet->setTitle('Pendaftaran');

        $this->writeReportSheet(
            $registrationSheet,
            'LAPORAN PENDAFTARAN JAMAAH',
            $registrationHeaders,
            $registrationRows,
            [10]
        );

        $paymentSheet = $spreadsheet->createSheet();
        $paymentSheet->setTitle('Pembayaran');

        $this->writeReportSheet(
            $paymentSheet,
            'LAPORAN PEMBAYARAN JAMAAH',
            $paymentHeaders,
            $paymentRows,
            [9]
        );

        $documentSheet = $spreadsheet->createSheet();
        $documentSheet->setTitle('Dokumen');

        $this->writeReportSheet(
            $documentSheet,
            'LAPORAN DOKUMEN JAMAAH',
            $documentHeaders,
            $documentRows
        );

        $spreadsheet->setActiveSheetIndex(0);

        return $this->downloadSpreadsheet(
            $spreadsheet,
            'laporan-lengkap-' . date('Ymd-His') . '.xlsx'
        );
    }
}
