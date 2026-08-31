<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =============================
// FRONTEND PUBLIC
// =============================
$routes->get('/', 'Frontend\Home::index');

$routes->get('/paket', 'Frontend\PackageController::index');
$routes->get('/paket/(:segment)', 'Frontend\PackageController::detail/$1');
$routes->post('/paket/(:segment)/pesan-whatsapp', 'Frontend\LeadController::store/$1');

$routes->get('/galeri', 'Frontend\GalleryController::index');
$routes->get('/testimoni', 'Frontend\TestimonialController::index');

// CHATBOT WEBSITE
$routes->post('/chatbot/message', 'Frontend\ChatbotController::message');
$routes->post('/chatbot/create-lead', 'Frontend\ChatbotController::createLead');
$routes->get('/chatbot/history/(:segment)', 'Frontend\ChatbotController::history/$1');

// CONTACT PAGE
// $routes->get('/kontak', 'Frontend\ContactController::index');
// $routes->post('/kontak/kirim', 'Frontend\ContactController::send');


// =============================
// AUTH JAMAAH
// =============================
$routes->get('/login', 'Frontend\AuthController::login');
$routes->post('/login', 'Frontend\AuthController::attemptLogin');

$routes->get('/register', 'Frontend\AuthController::register');
$routes->post('/register', 'Frontend\AuthController::storeRegister');

$routes->get('/verifikasi-email', 'Frontend\AuthController::verifyEmail');
$routes->post('/verifikasi-email', 'Frontend\AuthController::checkVerification');

$routes->get('/logout', 'Frontend\AuthController::logout');


// =============================
// AUTH ADMIN
// =============================
$routes->get('/admin', static function () {
    if (session()->get('admin_logged_in') === true) {
        return redirect()->to('/admin/dashboard');
    }

    return redirect()->to('/login');
});

$routes->get('/admin/login', static function () {
    return redirect()->to('/login');
});
$routes->post('/admin/login', 'Frontend\AuthController::attemptLogin');
$routes->get('/admin/logout', 'Frontend\AuthController::logout');


// =============================
// DAFTAR JAMAAH
// =============================
$routes->get('/daftar/berhasil/(:segment)', 'Frontend\RegistrationController::success/$1');
$routes->post('/daftar/simpan', 'Frontend\RegistrationController::store');
$routes->get('/daftar/(:segment)', 'Frontend\RegistrationController::create/$1');


// =============================
// CEK STATUS PENDAFTARAN
// =============================
$routes->get('/cek-status', 'Frontend\StatusController::index');
$routes->post('/cek-status', 'Frontend\StatusController::check');


// =============================
// PEMBAYARAN MIDTRANS
// =============================
$routes->get('/pembayaran/(:segment)', 'Frontend\PaymentController::show/$1');
$routes->post('/midtrans/notification', 'Frontend\PaymentController::notification');


// =============================
// DASHBOARD JAMAAH
// =============================
$routes->get('/dashboard-jamaah', 'Frontend\JamaahDashboardController::index');


// =============================
// DOKUMEN JAMAAH
// =============================
$routes->get('/dokumen/(:segment)', 'Frontend\DocumentController::index/$1');
$routes->post('/dokumen/upload', 'Frontend\DocumentController::upload');
$routes->get('/dokumen/delete/(:num)', 'Frontend\DocumentController::delete/$1');


// =============================
// INVOICE / BUKTI PEMBAYARAN
// =============================
$routes->get('/invoice/(:segment)', 'Frontend\InvoiceController::show/$1');


// =============================
// ADMIN AREA
// =============================
$routes->group('admin', ['filter' => 'adminAuth'], function ($routes) {

    // Dashboard operasional
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // Chatbot website
    $routes->get('chatbot', 'Admin\ChatbotController::index');
    $routes->get('chatbot/conversation/(:num)', 'Admin\ChatbotController::conversation/$1');
    $routes->post('chatbot/unanswered/resolve/(:num)', 'Admin\ChatbotController::resolveUnanswered/$1');
    $routes->post('chatbot/unanswered/ignore/(:num)', 'Admin\ChatbotController::ignoreUnanswered/$1');
    $routes->post('chatbot/knowledge/store', 'Admin\ChatbotController::storeKnowledge');
    $routes->post('chatbot/knowledge/update/(:num)', 'Admin\ChatbotController::updateKnowledge/$1');
    $routes->post('chatbot/knowledge/toggle/(:num)', 'Admin\ChatbotController::toggleKnowledge/$1');
    $routes->post('chatbot/knowledge/delete/(:num)', 'Admin\ChatbotController::deleteKnowledge/$1');
    $routes->post('chatbot/knowledge/rebuild-embeddings', 'Admin\ChatbotController::rebuildEmbeddings');

    // Document Knowledge: gambar/PDF -> ekstraksi -> review -> publikasi.
    $routes->get('chatbot/sources', 'Admin\ChatbotSourceController::index');
    $routes->post('chatbot/sources/upload', 'Admin\ChatbotSourceController::upload');
    $routes->get('chatbot/sources/(:num)', 'Admin\ChatbotSourceController::show/$1');
    $routes->get('chatbot/sources/(:num)/file', 'Admin\ChatbotSourceController::file/$1');
    $routes->post('chatbot/sources/(:num)/update', 'Admin\ChatbotSourceController::update/$1');
    $routes->post('chatbot/sources/(:num)/reprocess', 'Admin\ChatbotSourceController::reprocess/$1');
    $routes->post('chatbot/sources/(:num)/publish', 'Admin\ChatbotSourceController::publish/$1');
    $routes->post('chatbot/sources/(:num)/unpublish', 'Admin\ChatbotSourceController::unpublish/$1');
    $routes->post('chatbot/sources/(:num)/delete', 'Admin\ChatbotSourceController::delete/$1');

    // Reporting leads
    $routes->get('leads', 'Admin\LeadController::index');
    $routes->get('leads/detail/(:num)', 'Admin\LeadController::detail/$1');
    $routes->post('leads/update/(:num)', 'Admin\LeadController::update/$1');
    $routes->post('leads/delete/(:num)', 'Admin\LeadController::delete/$1');
    $routes->post('leads/bulk-delete', 'Admin\LeadController::bulkDelete');
    $routes->get('leads/trash', 'Admin\LeadController::trash');
    $routes->post('leads/restore/(:num)', 'Admin\LeadController::restore/$1');
    $routes->post('leads/force-delete/(:num)', 'Admin\LeadController::forceDelete/$1');
    $routes->post('leads/trash/bulk-action', 'Admin\LeadController::trashBulkAction');
    $routes->post('leads/trash/empty', 'Admin\LeadController::emptyTrash');
    $routes->get('leads/export-csv', 'Admin\LeadController::exportCsv');

    // Paket dan jadwal keberangkatan
    $routes->get('packages', 'Admin\PackageController::index');
    $routes->get('packages/create', 'Admin\PackageController::create');
    $routes->post('packages/store', 'Admin\PackageController::store');
    $routes->get('packages/edit/(:num)', 'Admin\PackageController::edit/$1');
    $routes->post('packages/update/(:num)', 'Admin\PackageController::update/$1');
    $routes->post('packages/delete/(:num)', 'Admin\PackageController::delete/$1');
    $routes->post('packages/bulk-delete', 'Admin\PackageController::bulkDelete');
    $routes->get('packages/trash', 'Admin\PackageController::trash');
    $routes->post('packages/restore/(:num)', 'Admin\PackageController::restore/$1');
    $routes->post('packages/force-delete/(:num)', 'Admin\PackageController::forceDelete/$1');
    $routes->post('packages/trash/bulk-action', 'Admin\PackageController::trashBulkAction');
    $routes->post('packages/trash/empty', 'Admin\PackageController::emptyTrash');

    $routes->get('packages/departures/(:num)', 'Admin\PackageDepartureController::index/$1');
    $routes->get('packages/departures/create/(:num)', 'Admin\PackageDepartureController::create/$1');
    $routes->post('packages/departures/store', 'Admin\PackageDepartureController::store');
    $routes->get('packages/departures/edit/(:num)', 'Admin\PackageDepartureController::edit/$1');
    $routes->post('packages/departures/update/(:num)', 'Admin\PackageDepartureController::update/$1');
    $routes->post('packages/departures/delete/(:num)', 'Admin\PackageDepartureController::delete/$1');
    $routes->post('packages/departures/bulk-delete', 'Admin\PackageDepartureController::bulkDelete');

    // Konten website
    $routes->get('galleries', 'Admin\GalleryController::index');
    $routes->get('galleries/create', 'Admin\GalleryController::create');
    $routes->post('galleries/store', 'Admin\GalleryController::store');
    $routes->get('galleries/edit/(:num)', 'Admin\GalleryController::edit/$1');
    $routes->post('galleries/update/(:num)', 'Admin\GalleryController::update/$1');
    $routes->post('galleries/delete/(:num)', 'Admin\GalleryController::delete/$1');
    $routes->post('galleries/bulk-delete', 'Admin\GalleryController::bulkDelete');
    $routes->get('galleries/trash', 'Admin\GalleryController::trash');
    $routes->post('galleries/restore/(:num)', 'Admin\GalleryController::restore/$1');
    $routes->post('galleries/force-delete/(:num)', 'Admin\GalleryController::forceDelete/$1');
    $routes->post('galleries/trash/bulk-action', 'Admin\GalleryController::trashBulkAction');
    $routes->post('galleries/trash/empty', 'Admin\GalleryController::emptyTrash');

    $routes->get('testimonials', 'Admin\TestimonialController::index');
    $routes->get('testimonials/create', 'Admin\TestimonialController::create');
    $routes->post('testimonials/store', 'Admin\TestimonialController::store');
    $routes->get('testimonials/edit/(:num)', 'Admin\TestimonialController::edit/$1');
    $routes->post('testimonials/update/(:num)', 'Admin\TestimonialController::update/$1');
    $routes->post('testimonials/delete/(:num)', 'Admin\TestimonialController::delete/$1');
    $routes->post('testimonials/bulk-delete', 'Admin\TestimonialController::bulkDelete');
    $routes->get('testimonials/trash', 'Admin\TestimonialController::trash');
    $routes->post('testimonials/restore/(:num)', 'Admin\TestimonialController::restore/$1');
    $routes->post('testimonials/force-delete/(:num)', 'Admin\TestimonialController::forceDelete/$1');
    $routes->post('testimonials/trash/bulk-action', 'Admin\TestimonialController::trashBulkAction');
    $routes->post('testimonials/trash/empty', 'Admin\TestimonialController::emptyTrash');

    $routes->get('faqs', 'Admin\FaqController::index');
    $routes->get('faqs/create', 'Admin\FaqController::create');
    $routes->post('faqs/store', 'Admin\FaqController::store');
    $routes->get('faqs/edit/(:num)', 'Admin\FaqController::edit/$1');
    $routes->post('faqs/update/(:num)', 'Admin\FaqController::update/$1');
    $routes->post('faqs/delete/(:num)', 'Admin\FaqController::delete/$1');
    $routes->post('faqs/bulk-delete', 'Admin\FaqController::bulkDelete');
    $routes->get('faqs/trash', 'Admin\FaqController::trash');
    $routes->post('faqs/restore/(:num)', 'Admin\FaqController::restore/$1');
    $routes->post('faqs/force-delete/(:num)', 'Admin\FaqController::forceDelete/$1');
    $routes->post('faqs/trash/bulk-action', 'Admin\FaqController::trashBulkAction');
    $routes->post('faqs/trash/empty', 'Admin\FaqController::emptyTrash');

    // Super Admin dan sistem
    $routes->get('admin-users', 'Admin\AdminUserController::index');
    $routes->get('admin-users/create', 'Admin\AdminUserController::create');
    $routes->post('admin-users/store', 'Admin\AdminUserController::store');
    $routes->get('admin-users/edit/(:num)', 'Admin\AdminUserController::edit/$1');
    $routes->post('admin-users/update/(:num)', 'Admin\AdminUserController::update/$1');
    $routes->post('admin-users/update-password/(:num)', 'Admin\AdminUserController::updatePassword/$1');
    $routes->post('admin-users/update-status/(:num)', 'Admin\AdminUserController::updateStatus/$1');
    $routes->post('admin-users/delete/(:num)', 'Admin\AdminUserController::delete/$1');
    $routes->post('admin-users/bulk-delete', 'Admin\AdminUserController::bulkDelete');
    $routes->get('admin-users/trash', 'Admin\AdminUserController::trash');
    $routes->post('admin-users/restore/(:num)', 'Admin\AdminUserController::restore/$1');
    $routes->post('admin-users/force-delete/(:num)', 'Admin\AdminUserController::forceDelete/$1');
    $routes->post('admin-users/trash/bulk-action', 'Admin\AdminUserController::trashBulkAction');
    $routes->post('admin-users/trash/empty', 'Admin\AdminUserController::emptyTrash');

    $routes->get('recycle-bin', 'Admin\RecycleBinController::index');

    // Redirect modul operasional lama agar bookmark lama tidak menghasilkan 404.
    $routes->get('registrations', static fn() => redirect()->to('/admin/dashboard'));
    $routes->get('payments', static fn() => redirect()->to('/admin/dashboard'));
    $routes->get('documents', static fn() => redirect()->to('/admin/dashboard'));
    $routes->get('documents/summary', static fn() => redirect()->to('/admin/dashboard'));
    $routes->get('jamaah', static fn() => redirect()->to('/admin/dashboard'));
    $routes->get('reports', static fn() => redirect()->to('/admin/dashboard'));

    // Pembersihan dokumen lama; modul verifikasi dokumen tidak lagi menjadi operasional utama.
    $routes->get('legacy-documents/trash', 'Admin\DocumentController::trash');
    $routes->post('legacy-documents/restore/(:num)', 'Admin\DocumentController::restore/$1');
    $routes->post('legacy-documents/force-delete/(:num)', 'Admin\DocumentController::forceDelete/$1');
    $routes->post('legacy-documents/trash/bulk-action', 'Admin\DocumentController::trashBulkAction');
    $routes->post('legacy-documents/trash/empty', 'Admin\DocumentController::emptyTrash');
    $routes->get('documents/trash', static fn() => redirect()->to('/admin/legacy-documents/trash'));

    $routes->get('activity-logs', 'Admin\ActivityLogController::index');
    $routes->post('activity-logs/clear', 'Admin\ActivityLogController::clear');

    $routes->get('settings', 'Admin\SettingController::index');
    $routes->post('settings/update', 'Admin\SettingController::update');

    // Notification Center tetap dipakai untuk notifikasi lead dan sistem.
    $routes->get('notifications', 'Admin\NotificationController::index');
    $routes->get('notifications/read/(:num)', 'Admin\NotificationController::read/$1');
    $routes->post('notifications/read-all', 'Admin\NotificationController::readAll');
    $routes->post('notifications/delete/(:num)', 'Admin\NotificationController::delete/$1');
    $routes->post('notifications/bulk-action', 'Admin\NotificationController::bulkAction');
    $routes->post('notifications/delete-all', 'Admin\NotificationController::deleteAll');
});
