<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiteSettingModel;

class SettingController extends BaseController
{
    private function onlySuperAdmin()
    {
        if (session()->get('admin_role') !== 'super_admin') {
            return redirect()
                ->to('/admin/dashboard')
                ->with('error', 'Akses ditolak. Fitur ini hanya untuk Super Admin.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $settingModel = new SiteSettingModel();

        return view('admin/settings/index', [
            'title'    => 'Pengaturan Website',
            'settings' => $settingModel->getAllSettings(),
        ]);
    }

    public function update()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $rules = [
            'brand_primary_color'   => 'permit_empty|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'brand_secondary_color' => 'permit_empty|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'site_logo'             => 'permit_empty|max_size[site_logo,2048]|mime_in[site_logo,image/png]|ext_in[site_logo,png]',
            'site_logo_white'       => 'permit_empty|max_size[site_logo_white,2048]|mime_in[site_logo_white,image/png]|ext_in[site_logo_white,png]',
            'site_favicon'          => 'permit_empty|max_size[site_favicon,512]|mime_in[site_favicon,image/png,image/x-icon,image/vnd.microsoft.icon,image/ico]|ext_in[site_favicon,png,ico]',
            'email_header_logo'     => 'permit_empty|max_size[email_header_logo,2048]|mime_in[email_header_logo,image/png]|ext_in[email_header_logo,png]',
            'about_image'           => 'permit_empty|max_size[about_image,5120]|mime_in[about_image,image/png,image/jpeg,image/webp]|ext_in[about_image,png,jpg,jpeg,webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $settingModel = new SiteSettingModel();

        try {
            $uploadedSettings = $this->storeBrandingUploads();
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menyimpan asset branding: {message}', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Asset branding gagal disimpan. Periksa permission folder upload dan format file.');
        }

        $allowedKeys = [
            'site_name',
            'site_tagline',
            'brand_primary_color',
            'brand_secondary_color',
            'contact_email',
            'contact_phone',
            'social_whatsapp',
            'contact_address',
            'business_hours',
            'bank_name',
            'bank_account_number',
            'bank_account_name',
            'social_instagram',
            'social_facebook',
            'youtube_url',
            'footer_text',
            'about_kicker',
            'about_title',
            'about_description',
            'about_note_label',
            'about_note_title',
            'about_point_1_title',
            'about_point_1_description',
            'about_point_2_title',
            'about_point_2_description',
            'about_point_3_title',
            'about_point_3_description',
            'about_cta_text',
            'about_cta_note',
            'package_program_options',
            'chatbot_enabled',
            'chatbot_name',
            'chatbot_welcome_message',
            'chatbot_fallback_message',
        ];

        foreach ($allowedKeys as $key) {
            $value = $this->request->getPost($key);
            $settingModel->updateSetting($key, $value);
        }

        foreach ($uploadedSettings as $key => $path) {
            $settingModel->updateSetting($key, $path);
        }

        $this->syncLegacySettings($settingModel);

        cache()->delete('site_settings_cache');

        log_admin_activity(
            'Pengaturan Website',
            'Update Settings',
            'Super Admin memperbarui pengaturan website.'
        );

        return redirect()
            ->to('/admin/settings')
            ->with('success', 'Pengaturan website berhasil diperbarui.');
    }

    private function storeBrandingUploads(): array
    {
        $uploadDirectory = FCPATH . 'assets/uploads/branding';

        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
            throw new \RuntimeException('Folder branding tidak dapat dibuat.');
        }

        $fileMap = [
            'site_logo' => [
                'filename' => 'site-logo.png',
            ],
            'site_logo_white' => [
                'filename' => 'site-logo-white.png',
            ],
            'email_header_logo' => [
                'filename' => 'email-header-logo.png',
            ],
        ];

        $uploadedSettings = [];

        foreach ($fileMap as $field => $config) {
            $file = $this->request->getFile($field);

            if (!$file || !$file->isValid() || $file->hasMoved()) {
                continue;
            }

            $file->move($uploadDirectory, $config['filename'], true);
            $uploadedSettings[$field] = 'assets/uploads/branding/' . $config['filename'];
        }

        $aboutImage = $this->request->getFile('about_image');

        if ($aboutImage && $aboutImage->isValid() && !$aboutImage->hasMoved()) {
            $extension = strtolower($aboutImage->getExtension());
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }

            $filename = 'about-section.' . $extension;
            $aboutImage->move($uploadDirectory, $filename, true);
            $uploadedSettings['about_image'] = 'assets/uploads/branding/' . $filename;
        }

        $favicon = $this->request->getFile('site_favicon');

        if ($favicon && $favicon->isValid() && !$favicon->hasMoved()) {
            $extension = strtolower($favicon->getExtension()) === 'png' ? 'png' : 'ico';
            $filename = 'site-favicon.' . $extension;

            $favicon->move($uploadDirectory, $filename, true);
            $uploadedSettings['site_favicon'] = 'assets/uploads/branding/' . $filename;
        }

        return $uploadedSettings;
    }

    private function syncLegacySettings(SiteSettingModel $settingModel): void
    {
        $legacyMap = [
            'site_email'    => 'contact_email',
            'site_phone'    => 'contact_phone',
            'site_address'  => 'contact_address',
            'facebook_url'  => 'social_facebook',
            'instagram_url' => 'social_instagram',
            'site_whatsapp' => 'social_whatsapp',
        ];

        foreach ($legacyMap as $legacyKey => $canonicalKey) {
            $settingModel->updateSetting(
                $legacyKey,
                $this->request->getPost($canonicalKey)
            );
        }
    }
}
