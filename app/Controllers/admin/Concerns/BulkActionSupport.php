<?php

namespace App\Controllers\Admin\Concerns;

trait BulkActionSupport
{
    protected function selectedIds(): array
    {
        $ids = $this->request->getPost('selected_ids');

        if (!is_array($ids)) {
            return [];
        }

        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, static fn(int $id): bool => $id > 0);

        return array_values(array_unique($ids));
    }

    protected function isSuperAdmin(): bool
    {
        return session()->get('admin_role') === 'super_admin';
    }

    protected function removePublicFile(?string $relativePath, array $allowedPrefixes = ['uploads/']): bool
    {
        $relativePath = ltrim((string) $relativePath, '/\\');

        if ($relativePath === '') {
            return false;
        }

        $normalized = str_replace('\\', '/', $relativePath);
        $allowed = false;

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($normalized, trim($prefix, '/') . '/')) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return false;
        }

        $candidatePath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalized);
        $realPublic = realpath(FCPATH);
        $realFile = is_file($candidatePath) ? realpath($candidatePath) : false;

        if (!$realPublic || !$realFile || !str_starts_with($realFile, $realPublic . DIRECTORY_SEPARATOR)) {
            return false;
        }

        return @unlink($realFile);
    }
}
