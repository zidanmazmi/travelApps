<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\BulkActionSupport;
use App\Controllers\BaseController;
use App\Models\GalleryModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;
use Throwable;

class GalleryController extends BaseController
{
    use BulkActionSupport;

    private const IMAGE_MAX_KB = 5120;
    private const VIDEO_MAX_KB = 102400;
    private const THUMBNAIL_MAX_KB = 5120;

    protected GalleryModel $galleryModel;

    public function __construct()
    {
        $this->galleryModel = new GalleryModel();
    }

    public function index()
    {
        $rows = $this->galleryModel
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $galleries = array_map(fn(array $row): array => $this->decorateGallery($row), $rows);

        return view('admin/galleries/index', [
            'title'        => 'Media Gallery',
            'galleries'    => $galleries,
            'totalImages'  => count(array_filter($galleries, static fn(array $item): bool => $item['media_type'] === 'image')),
            'totalVideos'  => count(array_filter($galleries, static fn(array $item): bool => $item['media_type'] === 'video')),
            'imageMaxMb'   => (int) (self::IMAGE_MAX_KB / 1024),
            'videoMaxMb'   => (int) (self::VIDEO_MAX_KB / 1024),
        ]);
    }

    public function create()
    {
        return view('admin/galleries/create', [
            'title'          => 'Tambah Media',
            'imageMaxMb'     => (int) (self::IMAGE_MAX_KB / 1024),
            'videoMaxMb'     => (int) (self::VIDEO_MAX_KB / 1024),
            'thumbnailMaxMb' => (int) (self::THUMBNAIL_MAX_KB / 1024),
        ]);
    }

    public function store()
    {
        $mediaType = $this->normalizeMediaType($this->request->getPost('media_type'));

        if (!$this->validate($this->validationRules($mediaType, true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $mediaUpload = null;
        $thumbnailUpload = null;

        try {
            $this->ensureUploadDirectories();

            $mediaUpload = $this->storeUploadedFile(
                $this->request->getFile('media_file'),
                $mediaType === 'video' ? 'videos' : 'images'
            );

            $thumbnailFile = $this->request->getFile('thumbnail_file');
            if ($mediaType === 'video' && $this->isUsableUpload($thumbnailFile)) {
                $thumbnailUpload = $this->storeUploadedFile($thumbnailFile, 'thumbnails');
            }

            $insertId = $this->galleryModel->insert([
                'title'            => trim((string) $this->request->getPost('title')),
                'image'            => $mediaType === 'image' ? $mediaUpload['filename'] : '',
                'media_type'       => $mediaType,
                'video_file'       => $mediaType === 'video' ? $mediaUpload['filename'] : null,
                'thumbnail'        => $thumbnailUpload['filename'] ?? null,
                'mime_type'        => $mediaUpload['mime_type'],
                'file_size'        => $mediaUpload['file_size'],
                'aspect_ratio'     => $this->normalizeAspectRatio($this->request->getPost('aspect_ratio'), $mediaType),
                'duration_seconds' => $mediaType === 'video' ? $this->normalizeDuration($this->request->getPost('duration_seconds')) : null,
                'description'      => trim((string) $this->request->getPost('description')),
                'sort_order'       => (int) ($this->request->getPost('sort_order') ?: 0),
                'status'           => (string) $this->request->getPost('status'),
            ], true);

            if (!$insertId) {
                throw new RuntimeException('Data media gagal disimpan ke database.');
            }
        } catch (Throwable $e) {
            $this->removeUploadResult($mediaUpload);
            $this->removeUploadResult($thumbnailUpload);

            log_message('error', 'Local gallery upload failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Media gagal disimpan: ' . $e->getMessage());
        }

        $label = $mediaType === 'video' ? 'video Reels' : 'foto';

        log_admin_activity(
            'Media Gallery',
            'Tambah Media',
            'Admin menambahkan ' . $label . ': ' . trim((string) $this->request->getPost('title'))
        );

        return redirect()
            ->to('/admin/galleries')
            ->with('success', ucfirst($label) . ' berhasil diunggah dan disimpan.');
    }

    public function edit($id)
    {
        $gallery = $this->galleryModel->find($id);

        if (!$gallery) {
            throw PageNotFoundException::forPageNotFound('Data media tidak ditemukan.');
        }

        return view('admin/galleries/edit', [
            'title'          => 'Edit Media',
            'gallery'        => $this->decorateGallery($gallery),
            'imageMaxMb'     => (int) (self::IMAGE_MAX_KB / 1024),
            'videoMaxMb'     => (int) (self::VIDEO_MAX_KB / 1024),
            'thumbnailMaxMb' => (int) (self::THUMBNAIL_MAX_KB / 1024),
        ]);
    }

    public function update($id)
    {
        $gallery = $this->galleryModel->find($id);

        if (!$gallery) {
            return redirect()->to('/admin/galleries')->with('error', 'Data media tidak ditemukan.');
        }

        $oldMediaType = $this->normalizeMediaType($gallery['media_type'] ?? 'image');
        $mediaType = $this->normalizeMediaType($this->request->getPost('media_type'));
        $mediaFile = $this->request->getFile('media_file');
        $hasNewMedia = $this->isUsableUpload($mediaFile);

        if ($mediaType !== $oldMediaType && !$hasNewMedia) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Saat mengganti jenis media, pilih file media baru.');
        }

        if (!$this->validate($this->validationRules($mediaType, false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newMedia = null;
        $newThumbnail = null;
        $thumbnailFile = $this->request->getFile('thumbnail_file');
        $hasNewThumbnail = $this->isUsableUpload($thumbnailFile);
        $removeThumbnail = $mediaType !== 'video' || $this->request->getPost('remove_thumbnail') === '1';

        try {
            $this->ensureUploadDirectories();

            if ($hasNewMedia) {
                $newMedia = $this->storeUploadedFile(
                    $mediaFile,
                    $mediaType === 'video' ? 'videos' : 'images'
                );
            }

            if ($mediaType === 'video' && $hasNewThumbnail) {
                $newThumbnail = $this->storeUploadedFile($thumbnailFile, 'thumbnails');
            }

            $data = [
                'title'            => trim((string) $this->request->getPost('title')),
                'media_type'       => $mediaType,
                'aspect_ratio'     => $this->normalizeAspectRatio($this->request->getPost('aspect_ratio'), $mediaType),
                'duration_seconds' => $mediaType === 'video'
                    ? ($this->normalizeDuration($this->request->getPost('duration_seconds')) ?? ($gallery['duration_seconds'] ?? null))
                    : null,
                'description'      => trim((string) $this->request->getPost('description')),
                'sort_order'       => (int) ($this->request->getPost('sort_order') ?: 0),
                'status'           => (string) $this->request->getPost('status'),
            ];

            if ($newMedia !== null) {
                $data['image'] = $mediaType === 'image' ? $newMedia['filename'] : '';
                $data['video_file'] = $mediaType === 'video' ? $newMedia['filename'] : null;
                $data['mime_type'] = $newMedia['mime_type'];
                $data['file_size'] = $newMedia['file_size'];
            }

            if ($newThumbnail !== null) {
                $data['thumbnail'] = $newThumbnail['filename'];
            } elseif ($removeThumbnail) {
                $data['thumbnail'] = null;
            }

            if (!$this->galleryModel->update($id, $data)) {
                throw new RuntimeException('Perubahan media gagal disimpan ke database.');
            }

            if ($newMedia !== null) {
                $this->deleteMainMedia($gallery);
            }

            if ($newThumbnail !== null || $removeThumbnail) {
                $this->deleteThumbnail($gallery['thumbnail'] ?? null);
            }
        } catch (Throwable $e) {
            $this->removeUploadResult($newMedia);
            $this->removeUploadResult($newThumbnail);

            log_message('error', 'Local gallery update failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Media gagal diperbarui: ' . $e->getMessage());
        }

        log_admin_activity('Media Gallery', 'Update Media', 'Admin memperbarui media ID #' . $id . '.');

        return redirect()->to('/admin/galleries')->with('success', 'Media berhasil diperbarui.');
    }

    public function delete($id)
    {
        $gallery = $this->galleryModel->find($id);

        if (!$gallery) {
            return redirect()->to('/admin/galleries')->with('error', 'Data media tidak ditemukan.');
        }

        $this->galleryModel->delete($id);
        log_admin_activity('Media Gallery', 'Pindah ke Sampah', 'Media #' . $id . ' dipindahkan ke Sampah.');

        return redirect()->to('/admin/galleries')->with('success', 'Media berhasil dipindahkan ke Sampah.');
    }

    public function bulkDelete()
    {
        $ids = $this->selectedIds();

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu media.');
        }

        $deleted = 0;

        foreach ($ids as $id) {
            if (!$this->galleryModel->find($id)) {
                continue;
            }

            $this->galleryModel->delete($id);
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('Media Gallery', 'Pindah Massal ke Sampah', $deleted . ' media dipindahkan ke Sampah.');
        }

        return redirect()->to('/admin/galleries')->with('success', $deleted . ' media dipindahkan ke Sampah.');
    }

    public function trash()
    {
        $rows = $this->galleryModel
            ->onlyDeleted()
            ->orderBy('deleted_at', 'DESC')
            ->findAll();

        return view('admin/galleries/trash', [
            'title'     => 'Sampah Media Gallery',
            'galleries' => array_map(fn(array $row): array => $this->decorateGallery($row), $rows),
        ]);
    }

    public function restore($id)
    {
        $gallery = $this->galleryModel->withDeleted()->find($id);

        if (!$gallery || empty($gallery['deleted_at'])) {
            return redirect()->back()->with('error', 'Media tidak ditemukan di Sampah.');
        }

        $this->galleryModel->builder()->where('id', $id)->update([
            'deleted_at' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        log_admin_activity('Media Gallery', 'Pulihkan Media', 'Media #' . $id . ' dipulihkan.');

        return redirect()->to('/admin/galleries/trash')->with('success', 'Media berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus media permanen.');
        }

        if (!$this->forceDeleteGallery((int) $id)) {
            return redirect()->back()->with('error', 'Media harus berada di Sampah.');
        }

        log_admin_activity('Media Gallery', 'Hapus Permanen', 'Media #' . $id . ' dan file fisiknya dihapus permanen.');

        return redirect()->to('/admin/galleries/trash')->with('success', 'Media dan file berhasil dihapus permanen.');
    }

    public function trashBulkAction()
    {
        $ids = $this->selectedIds();
        $action = (string) $this->request->getPost('bulk_action');

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu media di Sampah.');
        }

        if (!in_array($action, ['restore', 'force_delete'], true)) {
            return redirect()->back()->with('error', 'Aksi massal tidak valid.');
        }

        if ($action === 'force_delete' && !$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus media permanen.');
        }

        $processed = 0;

        foreach ($ids as $id) {
            $gallery = $this->galleryModel->withDeleted()->find($id);

            if (!$gallery || empty($gallery['deleted_at'])) {
                continue;
            }

            if ($action === 'restore') {
                $this->galleryModel->builder()->where('id', $id)->update([
                    'deleted_at' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $processed++;
                continue;
            }

            if ($this->forceDeleteGallery($id)) {
                $processed++;
            }
        }

        $label = $action === 'restore' ? 'dipulihkan' : 'dihapus permanen';
        log_admin_activity('Media Gallery', 'Aksi Massal Sampah', $processed . ' media ' . $label . '.');

        return redirect()->to('/admin/galleries/trash')->with('success', $processed . ' media berhasil ' . $label . '.');
    }

    public function emptyTrash()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat mengosongkan Sampah media.');
        }

        $rows = $this->galleryModel->onlyDeleted()->findAll();
        $deleted = 0;

        foreach ($rows as $row) {
            if ($this->forceDeleteGallery((int) $row['id'])) {
                $deleted++;
            }
        }

        if ($deleted > 0) {
            log_admin_activity('Media Gallery', 'Kosongkan Sampah', $deleted . ' media dan file dihapus permanen.');
        }

        return redirect()
            ->to('/admin/galleries/trash')
            ->with('success', 'Sampah media dikosongkan. ' . $deleted . ' data dihapus permanen.');
    }

    private function validationRules(string $mediaType, bool $requiredFile): array
    {
        $filePrefix = $requiredFile ? 'uploaded[media_file]|' : 'permit_empty|';

        $mediaRule = $mediaType === 'video'
            ? $filePrefix . 'ext_in[media_file,mp4,webm]|mime_in[media_file,video/mp4,video/webm]|max_size[media_file,' . self::VIDEO_MAX_KB . ']'
            : $filePrefix . 'is_image[media_file]|ext_in[media_file,jpg,jpeg,png,webp]|mime_in[media_file,image/jpg,image/jpeg,image/png,image/webp]|max_size[media_file,' . self::IMAGE_MAX_KB . ']';

        return [
            'title'            => 'required|min_length[3]|max_length[150]',
            'media_type'       => 'required|in_list[image,video]',
            'media_file'       => $mediaRule,
            'thumbnail_file'   => 'permit_empty|is_image[thumbnail_file]|ext_in[thumbnail_file,jpg,jpeg,png,webp]|mime_in[thumbnail_file,image/jpg,image/jpeg,image/png,image/webp]|max_size[thumbnail_file,' . self::THUMBNAIL_MAX_KB . ']',
            'aspect_ratio'     => 'required|in_list[portrait,landscape,square]',
            'duration_seconds' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[86400]',
            'status'           => 'required|in_list[active,inactive]',
            'sort_order'       => 'permit_empty|integer',
        ];
    }

    private function forceDeleteGallery(int $id): bool
    {
        $gallery = $this->galleryModel->withDeleted()->find($id);

        if (!$gallery || empty($gallery['deleted_at'])) {
            return false;
        }

        $this->deleteMainMedia($gallery);
        $this->deleteThumbnail($gallery['thumbnail'] ?? null);
        $this->galleryModel->delete($id, true);

        return true;
    }

    private function decorateGallery(array $gallery): array
    {
        $gallery['media_type'] = $this->normalizeMediaType($gallery['media_type'] ?? 'image');
        $gallery['preview_url'] = $this->mainMediaUrl($gallery);
        $gallery['poster_url'] = !empty($gallery['thumbnail'])
            ? base_url('uploads/gallery/thumbnails/' . rawurlencode((string) $gallery['thumbnail']))
            : '';
        $gallery['file_size_hr'] = $this->humanFileSize((int) ($gallery['file_size'] ?? 0));
        $gallery['duration_hr'] = $this->humanDuration((int) ($gallery['duration_seconds'] ?? 0));

        return $gallery;
    }

    private function mainMediaUrl(array $gallery): string
    {
        if ($this->normalizeMediaType($gallery['media_type'] ?? 'image') === 'video') {
            if (empty($gallery['video_file'])) {
                return '';
            }

            return base_url('uploads/gallery/videos/' . rawurlencode((string) $gallery['video_file']));
        }

        $image = (string) ($gallery['image'] ?? '');

        if ($image === '') {
            return '';
        }

        $newPath = FCPATH . 'uploads/gallery/images/' . $image;

        if (is_file($newPath)) {
            return base_url('uploads/gallery/images/' . rawurlencode($image));
        }

        return base_url('uploads/gallery/' . rawurlencode($image));
    }

    private function deleteMainMedia(array $gallery): void
    {
        if ($this->normalizeMediaType($gallery['media_type'] ?? 'image') === 'video') {
            if (!empty($gallery['video_file'])) {
                $this->removePublicFile(
                    'uploads/gallery/videos/' . $gallery['video_file'],
                    ['uploads/gallery/videos']
                );
            }

            return;
        }

        $image = (string) ($gallery['image'] ?? '');

        if ($image === '') {
            return;
        }

        if (is_file(FCPATH . 'uploads/gallery/images/' . $image)) {
            $this->removePublicFile('uploads/gallery/images/' . $image, ['uploads/gallery/images']);
            return;
        }

        $this->removePublicFile('uploads/gallery/' . $image, ['uploads/gallery']);
    }

    private function deleteThumbnail(?string $filename): void
    {
        if (empty($filename)) {
            return;
        }

        $this->removePublicFile(
            'uploads/gallery/thumbnails/' . $filename,
            ['uploads/gallery/thumbnails']
        );
    }

    /**
     * @return array{filename:string,relative_path:string,mime_type:string,file_size:int}
     */
    private function storeUploadedFile(?UploadedFile $file, string $folder): array
    {
        if (!$this->isUsableUpload($file)) {
            throw new RuntimeException('File upload tidak valid.');
        }

        $targetDirectory = FCPATH . 'uploads/gallery/' . trim($folder, '/');

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('Folder upload tidak dapat dibuat.');
        }

        $filename = $file->getRandomName();
        $mimeType = (string) $file->getMimeType();
        $fileSize = (int) $file->getSize();

        if (!$file->move($targetDirectory, $filename)) {
            throw new RuntimeException('File gagal dipindahkan ke folder upload.');
        }

        return [
            'filename'      => $filename,
            'relative_path' => 'uploads/gallery/' . trim($folder, '/') . '/' . $filename,
            'mime_type'     => $mimeType,
            'file_size'     => $fileSize,
        ];
    }

    private function isUsableUpload(?UploadedFile $file): bool
    {
        return $file instanceof UploadedFile
            && $file->isValid()
            && !$file->hasMoved()
            && $file->getError() === UPLOAD_ERR_OK;
    }

    /** @param array{relative_path?:string}|null $upload */
    private function removeUploadResult(?array $upload): void
    {
        if (!empty($upload['relative_path'])) {
            $this->removePublicFile($upload['relative_path'], ['uploads/gallery']);
        }
    }

    private function ensureUploadDirectories(): void
    {
        $base = FCPATH . 'uploads/gallery';
        $directories = [$base, $base . '/images', $base . '/videos', $base . '/thumbnails'];

        foreach ($directories as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException('Folder galeri tidak dapat dibuat: ' . $directory);
            }

            $indexFile = $directory . '/index.html';
            if (!is_file($indexFile)) {
                @file_put_contents($indexFile, '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access Denied</title></head><body></body></html>');
            }
        }

        $htaccess = $base . '/.htaccess';
        if (!is_file($htaccess)) {
            @file_put_contents($htaccess, "Options -Indexes\n\n<FilesMatch \\\"\\.(php|php3|php4|php5|php7|php8|phtml|phar|cgi|pl|py|sh)$\\\">\n    Require all denied\n</FilesMatch>\n");
        }
    }

    private function normalizeMediaType($value): string
    {
        return strtolower((string) $value) === 'video' ? 'video' : 'image';
    }

    private function normalizeAspectRatio($value, string $mediaType): string
    {
        $value = strtolower((string) $value);

        if (in_array($value, ['portrait', 'landscape', 'square'], true)) {
            return $value;
        }

        return $mediaType === 'video' ? 'portrait' : 'landscape';
    }

    private function normalizeDuration($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return max(0, min(86400, (int) $value));
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $index = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return number_format($bytes / (1024 ** $index), $index === 0 ? 0 : 1, ',', '.') . ' ' . $units[$index];
    }

    private function humanDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '-';
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }
}
