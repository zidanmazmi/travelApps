<?php

use App\Models\SiteSettingModel;

if (!function_exists('site_setting')) {
    function site_setting(string $key, string $default = ''): string
    {
        try {
            $cacheKey = 'site_settings_cache';
            $settings = cache()->get($cacheKey);

            if (!is_array($settings)) {
                $model = new SiteSettingModel();
                $settings = $model->getAllSettings();

                cache()->save($cacheKey, $settings, 3600);
            }

            if (array_key_exists($key, $settings)) {
                return (string) $settings[$key];
            }

            $aliases = [
                'contact_email'    => 'site_email',
                'contact_phone'    => 'site_phone',
                'contact_address'  => 'site_address',
                'social_facebook'  => 'facebook_url',
                'social_instagram' => 'instagram_url',
                'social_whatsapp'  => 'site_whatsapp',

                'site_email'    => 'contact_email',
                'site_phone'    => 'contact_phone',
                'site_address'  => 'contact_address',
                'facebook_url'  => 'social_facebook',
                'instagram_url' => 'social_instagram',
                'site_whatsapp' => 'social_whatsapp',
            ];

            $aliasKey = $aliases[$key] ?? null;

            if ($aliasKey !== null && array_key_exists($aliasKey, $settings)) {
                return (string) $settings[$aliasKey];
            }

            return $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('site_asset')) {
    function site_asset(string $key): string
    {
        $settingKeys = [
            'logo'              => 'site_logo',
            'logo_white'        => 'site_logo_white',
            'favicon'           => 'site_favicon',
            'email_header_logo' => 'email_header_logo',
        ];

        $defaultPaths = [
            'logo'              => 'assets/frontend/images/default-brand/site-logo.png',
            'logo_white'        => 'assets/frontend/images/default-brand/site-logo-white.png',
            'favicon'           => 'assets/frontend/images/default-brand/site-favicon.png',
            'email_header_logo' => 'assets/frontend/images/default-brand/email-header-logo.png',
        ];

        $settingKey = $settingKeys[$key] ?? $key;
        $path = trim(site_setting($settingKey));

        if ($path === '') {
            $path = $defaultPaths[$key] ?? '';
        }

        if ($path === '') {
            return '';
        }

        if (preg_match('#^(?:https?:)?//#i', $path) === 1) {
            return $path;
        }

        helper('url');

        return base_url(ltrim($path, '/'));
    }
}
