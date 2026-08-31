<section id="faq" class="section-faq">
    <div class="container">

        <div class="section-heading text-center" data-reveal="up">
            <h2>Pertanyaan yang Sering Diajukan</h2>
            <div class="heading-line"></div>
        </div>

        <?php if (empty($faqs)) : ?>

            <div class="text-center">
                <p>FAQ belum tersedia.</p>
            </div>

        <?php else : ?>

            <div class="faq-wrapper">
                <?php foreach ($faqs as $index => $faq) : ?>

                    <?php
                    $question   = $faq['question'] ?? 'Pertanyaan';
                    $answer     = $faq['answer'] ?? '-';
                    $collapseId = 'faqCollapse' . $index;
                    ?>

                    <div class="faq-item" data-reveal="up" data-delay="<?= $index * 70; ?>">
                        <button 
                            class="faq-question <?= $index === 0 ? '' : 'collapsed'; ?>"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?= esc($collapseId); ?>"
                            aria-expanded="<?= $index === 0 ? 'true' : 'false'; ?>"
                            aria-controls="<?= esc($collapseId); ?>">

                            <span><?= esc($question); ?></span>
                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <div 
                            id="<?= esc($collapseId); ?>" 
                            class="collapse <?= $index === 0 ? 'show' : ''; ?>"
                            data-bs-parent=".faq-wrapper">

                            <div class="faq-answer">
                                <?= nl2br(esc($answer)); ?>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</section>