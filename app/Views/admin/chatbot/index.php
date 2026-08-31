<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-xl-row align-items-xl-end justify-content-between gap-3">
    <div>
        <span>Website Assistant</span>
        <h1>Chatbot <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?></h1>
        <p>Pantau percakapan, pertanyaan yang belum terjawab, dan lead yang masuk melalui chatbot.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/admin/chatbot/sources'); ?>" class="btn btn-admin-outline">
            <i class="bi bi-file-earmark-richtext"></i> Document Knowledge
        </a>
        <?php if (session()->get('admin_role') === 'super_admin') : ?>
            <form action="<?= base_url('/admin/chatbot/knowledge/rebuild-embeddings'); ?>" method="post">
                <?= csrf_field(); ?>
                <button type="submit" class="btn btn-admin-outline" data-confirm="Bangun ulang semantic index Gemini untuk semua knowledge aktif? Proses ini menggunakan kuota API.">
                    <i class="bi bi-diagram-3"></i> Sinkronkan Semantic Index
                </button>
            </form>
            <a href="<?= base_url('/admin/settings'); ?>" class="btn btn-admin-outline">
                <i class="bi bi-sliders"></i> Pengaturan Chatbot
            </a>
        <?php endif; ?>
        <a href="<?= base_url('/'); ?>" target="_blank" class="btn btn-admin-primary">
            <i class="bi bi-box-arrow-up-right"></i> Coba Chatbot
        </a>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php
$knowledgeErrors = session()->getFlashdata('errors') ?? [];
$editingKnowledgeId = (int) (session()->getFlashdata('edit_knowledge_id') ?? 0);
$isEditingKnowledge = $editingKnowledgeId > 0;
?>
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div>
<?php endif; ?>
<?php if (!empty($knowledgeErrors)) : ?>
    <div class="alert alert-danger rounded-4">
        <strong>Periksa data Knowledge Chatbot:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($knowledgeErrors as $error) : ?>
                <li><?= esc($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="admin-stats-grid mb-4">
    <div class="admin-stat-card">
        <div class="stat-icon"><i class="bi bi-chat-square-dots"></i></div>
        <div><span>Total Percakapan</span><h3><?= esc($stats['sessions'] ?? 0); ?></h3></div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon"><i class="bi bi-send"></i></div>
        <div><span>Pertanyaan Masuk</span><h3><?= esc($stats['messages'] ?? 0); ?></h3></div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon accent"><i class="bi bi-question-diamond"></i></div>
        <div><span>Belum Terjawab</span><h3><?= esc($stats['unanswered'] ?? 0); ?></h3></div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon success"><i class="bi bi-person-check"></i></div>
        <div><span>Lead dari Chatbot</span><h3><?= esc($stats['leads'] ?? 0); ?></h3></div>
    </div>
</div>

<div class="admin-card chatbot-training-card mb-4" id="chatbot-knowledge">
    <div class="admin-card-header">
        <div>
            <span class="lead-card-kicker">Knowledge Center</span>
            <h2>Siapkan Jawaban Sebelum Ditanyakan</h2>
            <p class="mb-0">Masukkan pertanyaan yang mungkin diajukan calon jamaah, beberapa variasi kalimatnya, lalu jawaban resmi yang harus diberikan chatbot.</p>
        </div>
        <span class="status-pill status-success"><?= esc(count(array_filter($knowledge, static fn(array $item): bool => ($item['status'] ?? '') === 'active'))); ?> aktif</span>
    </div>
    <div class="admin-card-body">
        <div class="chatbot-training-guide mb-4">
            <div><span>1</span><strong>Pertanyaan acuan</strong><small>Sebutkan program untuk jawaban khusus, misalnya: Hotel untuk Program Reguler?</small></div>
            <div><span>2</span><strong>Variasi pertanyaan</strong><small>Pisahkan dengan koma atau baris baru.</small></div>
            <div><span>3</span><strong>Jawaban resmi</strong><small>Chatbot memakai jawaban ini saat pertanyaan cocok.</small></div>
        </div>

        <form action="<?= base_url('/admin/chatbot/knowledge/store'); ?>" method="post" class="chatbot-knowledge-form">
            <?= csrf_field(); ?>
            <div class="row g-3">
                <div class="col-lg-8">
                    <label for="knowledge_question" class="admin-form-label">Pertanyaan Acuan <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="question"
                        id="knowledge_question"
                        class="form-control admin-form-control"
                        value="<?= esc(!$isEditingKnowledge ? old('question') : ''); ?>"
                        placeholder="Contoh: Hotel yang digunakan Program Reguler apa?"
                        maxlength="255"
                        required>
                </div>
                <div class="col-lg-4">
                    <label for="knowledge_status" class="admin-form-label">Status</label>
                    <select name="status" id="knowledge_status" class="form-control admin-form-control" required>
                        <option value="active" <?= (!$isEditingKnowledge ? old('status', 'active') : 'active') === 'active' ? 'selected' : ''; ?>>Aktif — langsung digunakan</option>
                        <option value="inactive" <?= (!$isEditingKnowledge ? old('status') : '') === 'inactive' ? 'selected' : ''; ?>>Nonaktif — simpan sebagai draf</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="knowledge_keywords" class="admin-form-label">Variasi Pertanyaan / Frasa Pemicu</label>
                    <textarea
                        name="keywords"
                        id="knowledge_keywords"
                        class="form-control admin-form-control"
                        rows="3"
                        maxlength="1500"
                        placeholder="visa sudah termasuk, biaya visa, apakah urus visa sendiri"><?= esc(!$isEditingKnowledge ? old('keywords') : ''); ?></textarea>
                    <small class="text-muted d-block mt-2">Gunakan frasa yang benar-benar mewakili maksud yang sama. Pisahkan dengan koma, titik koma, atau baris baru.</small>
                </div>
                <div class="col-12">
                    <label for="knowledge_answer" class="admin-form-label">Jawaban Resmi Chatbot <span class="text-danger">*</span></label>
                    <textarea
                        name="answer"
                        id="knowledge_answer"
                        class="form-control admin-form-control"
                        rows="5"
                        maxlength="5000"
                        placeholder="Tulis jawaban lengkap, jelas, dan siap dibaca calon jamaah."
                        required><?= esc(!$isEditingKnowledge ? old('answer') : ''); ?></textarea>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mt-3">
                <small class="text-muted"><i class="bi bi-shield-check me-1"></i> Hanya jawaban berstatus aktif yang dipakai chatbot.</small>
                <button type="submit" class="btn btn-admin-primary">
                    <i class="bi bi-stars"></i> Simpan &amp; Latih Chatbot
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <div>
                    <span class="lead-card-kicker">Riwayat Terbaru</span>
                    <h2>Percakapan Pengunjung</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Pengunjung</th>
                        <th>Pesan Terakhir</th>
                        <th>Intent</th>
                        <th>Aktivitas</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($latestSessions)) : ?>
                        <tr><td colspan="5"><div class="admin-empty"><i class="bi bi-chat-square-dots"></i> Belum ada percakapan chatbot.</div></td></tr>
                    <?php else : ?>
                        <?php foreach ($latestSessions as $chat) : ?>
                            <tr>
                                <td>
                                    <strong><?= esc($chat['visitor_name'] ?: 'Pengunjung Website'); ?></strong><br>
                                    <small><?= esc($chat['visitor_phone'] ?: substr($chat['session_token'], 0, 10) . '...'); ?></small>
                                </td>
                                <td style="max-width:320px;">
                                    <span class="d-block text-truncate"><?= esc($chat['last_visitor_message'] ?: '-'); ?></span>
                                    <small><?= esc($chat['total_messages'] ?? 0); ?> pesan</small>
                                </td>
                                <td><span class="status-pill status-muted"><?= esc($chat['last_intent'] ?: '-'); ?></span></td>
                                <td><?= !empty($chat['last_message_at']) ? date('d M Y H:i', strtotime($chat['last_message_at'])) : '-'; ?></td>
                                <td><a href="<?= base_url('/admin/chatbot/conversation/' . $chat['id']); ?>" class="btn btn-admin-outline btn-sm">Buka</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <div>
                    <span class="lead-card-kicker">Intent Analytics</span>
                    <h2>Pertanyaan Populer</h2>
                </div>
            </div>
            <div class="admin-card-body">
                <?php if (empty($topIntents)) : ?>
                    <div class="admin-empty"><i class="bi bi-bar-chart"></i> Data intent belum tersedia.</div>
                <?php else : ?>
                    <div class="d-grid gap-3">
                        <?php $maxIntent = max(array_map('intval', array_column($topIntents, 'total'))); ?>
                        <?php foreach ($topIntents as $intent) : ?>
                            <?php $percentage = $maxIntent > 0 ? ((int) $intent['total'] / $maxIntent) * 100 : 0; ?>
                            <div>
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <strong class="small"><?= esc($intent['intent'] ?: 'lainnya'); ?></strong>
                                    <span class="small text-muted"><?= esc($intent['total']); ?></span>
                                </div>
                                <div class="progress" style="height:7px;">
                                    <div class="progress-bar" style="width:<?= esc($percentage); ?>%; background:#b77a34;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <div>
                    <span class="lead-card-kicker">Perlu Ditinjau</span>
                    <h2>Pertanyaan Belum Terjawab</h2>
                </div>
                <span class="status-pill status-warning"><?= esc(count($unanswered)); ?> baru</span>
            </div>
            <div class="admin-card-body">
                <?php if (empty($unanswered)) : ?>
                    <div class="admin-empty"><i class="bi bi-check2-circle"></i> Semua pertanyaan sudah tertangani.</div>
                <?php else : ?>
                    <div class="d-grid gap-3">
                        <?php foreach ($unanswered as $item) : ?>
                            <div class="border rounded-4 p-3 bg-white">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <strong><?= esc($item['question']); ?></strong>
                                    <small class="text-muted text-nowrap"><?= !empty($item['created_at']) ? date('d M H:i', strtotime($item['created_at'])) : '-'; ?></small>
                                </div>
                                <form action="<?= base_url('/admin/chatbot/unanswered/resolve/' . $item['id']); ?>" method="post" class="mt-3">
                                    <?= csrf_field(); ?>
                                    <div class="mb-2">
                                        <input type="text" name="keywords" class="form-control admin-form-control" value="<?= esc($item['normalized_question'] ?? ''); ?>" placeholder="Kata kunci">
                                    </div>
                                    <div class="mb-2">
                                        <textarea name="answer" class="form-control admin-form-control" rows="3" placeholder="Masukkan jawaban resmi untuk chatbot..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-admin-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambahkan Jawaban</button>
                                </form>
                                <form action="<?= base_url('/admin/chatbot/unanswered/ignore/' . $item['id']); ?>" method="post" class="mt-2">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="btn btn-admin-outline btn-sm">Abaikan Pertanyaan</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <div>
                    <span class="lead-card-kicker">Knowledge Base</span>
                    <h2>Jawaban Manual Tersimpan</h2>
                </div>
                <span class="status-pill status-muted"><?= esc(count($knowledge)); ?> entri</span>
            </div>
            <div class="admin-card-body">
                <?php if (empty($knowledge)) : ?>
                    <div class="admin-empty"><i class="bi bi-lightbulb"></i> Belum ada jawaban manual. Tambahkan dari form Knowledge Center di atas.</div>
                <?php else : ?>
                    <div class="d-grid gap-3 chatbot-knowledge-list">
                        <?php foreach ($knowledge as $item) : ?>
                            <?php
                            $itemId = (int) $item['id'];
                            $isActive = ($item['status'] ?? '') === 'active';
                            $isCurrentEdit = $editingKnowledgeId === $itemId;
                            ?>
                            <div class="chatbot-knowledge-item" id="knowledge-<?= esc($itemId); ?>">
                                <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                                    <strong><?= esc($item['question']); ?></strong>
                                    <span class="status-pill <?= $isActive ? 'status-success' : 'status-muted'; ?>">
                                        <?= $isActive ? 'Aktif' : 'Draf'; ?>
                                    </span>
                                </div>

                                <?php if (!empty($item['keywords'])) : ?>
                                    <div class="chatbot-keyword-preview">
                                        <i class="bi bi-key"></i>
                                        <span><?= esc($item['keywords']); ?></span>
                                    </div>
                                <?php endif; ?>

                                <p class="small text-muted mt-2 mb-3"><?= esc(mb_strimwidth($item['answer'], 0, 220, '...')); ?></p>

                                <div class="d-flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-admin-outline btn-sm"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#edit-knowledge-<?= esc($itemId); ?>"
                                        aria-expanded="<?= $isCurrentEdit ? 'true' : 'false'; ?>"
                                        aria-controls="edit-knowledge-<?= esc($itemId); ?>">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>

                                    <form action="<?= base_url('/admin/chatbot/knowledge/toggle/' . $itemId); ?>" method="post">
                                        <?= csrf_field(); ?>
                                        <button type="submit" class="btn <?= $isActive ? 'btn-outline-secondary' : 'btn-outline-success'; ?> btn-sm">
                                            <i class="bi <?= $isActive ? 'bi-pause-circle' : 'bi-play-circle'; ?>"></i>
                                            <?= $isActive ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                        </button>
                                    </form>

                                    <?php if (session()->get('admin_role') === 'super_admin') : ?>
                                        <form action="<?= base_url('/admin/chatbot/knowledge/delete/' . $itemId); ?>" method="post" data-confirm="Hapus jawaban ini ke sampah? Jawaban tidak akan lagi digunakan chatbot.">
                                            <?= csrf_field(); ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i> Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <div class="collapse <?= $isCurrentEdit ? 'show' : ''; ?> mt-3" id="edit-knowledge-<?= esc($itemId); ?>">
                                    <form action="<?= base_url('/admin/chatbot/knowledge/update/' . $itemId); ?>" method="post" class="chatbot-inline-edit">
                                        <?= csrf_field(); ?>
                                        <div class="mb-2">
                                            <label class="admin-form-label">Pertanyaan Acuan</label>
                                            <input
                                                type="text"
                                                name="question"
                                                class="form-control admin-form-control"
                                                value="<?= esc($isCurrentEdit ? old('question', $item['question']) : $item['question']); ?>"
                                                maxlength="255"
                                                required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="admin-form-label">Variasi Pertanyaan</label>
                                            <textarea name="keywords" class="form-control admin-form-control" rows="3" maxlength="1500"><?= esc($isCurrentEdit ? old('keywords', $item['keywords'] ?? '') : ($item['keywords'] ?? '')); ?></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="admin-form-label">Jawaban Resmi</label>
                                            <textarea name="answer" class="form-control admin-form-control" rows="5" maxlength="5000" required><?= esc($isCurrentEdit ? old('answer', $item['answer']) : $item['answer']); ?></textarea>
                                        </div>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-sm-7">
                                                <label class="admin-form-label">Status</label>
                                                <?php $editStatus = $isCurrentEdit ? old('status', $item['status']) : $item['status']; ?>
                                                <select name="status" class="form-control admin-form-control" required>
                                                    <option value="active" <?= $editStatus === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                                    <option value="inactive" <?= $editStatus === 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-5">
                                                <button type="submit" class="btn btn-admin-primary w-100">Simpan Edit</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
