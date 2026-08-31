<section id="testimoni" class="section-testimonials">
    <div class="container">

        <div class="section-heading text-center" data-reveal="up">
            <h2>Apa Kata Jamaah Kami</h2>
            <div class="heading-line"></div>
        </div>

        <?php if (empty($testimonials)) : ?>

            <div class="text-center">
                <p>Testimoni jamaah belum tersedia.</p>
            </div>

        <?php else : ?>

            <div class="row g-4">

                <?php foreach ($testimonials as $index => $testimonial) : ?>

                    <?php
                    $name    = $testimonial['name'] ?? 'Jamaah';
                    $label   = $testimonial['label'] ?? 'Jamaah';
                    $message = $testimonial['message'] ?? '';
                    $rating  = (int) ($testimonial['rating'] ?? 5);

                    if ($rating < 1) {
                        $rating = 1;
                    }

                    if ($rating > 5) {
                        $rating = 5;
                    }

                    $avatar = strtoupper(substr(trim($name), 0, 1));

                    if (empty($avatar)) {
                        $avatar = 'J';
                    }
                    ?>

                    <div class="col-md-6 col-lg-4" data-reveal="up" data-delay="<?= $index * 90; ?>">
                        <div class="testimonial-card h-100" data-tilt>

                            <div class="testimonial-quote-icon">
                                <i class="bi bi-quote"></i>
                            </div>

                            <div class="testimonial-stars">
                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <?php if ($i <= $rating) : ?>
                                        <i class="bi bi-star-fill"></i>
                                    <?php else : ?>
                                        <i class="bi bi-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>

                            <p class="testimonial-message">
                                “<?= esc($message); ?>”
                            </p>

                            <div class="testimonial-user">
                                <div class="testimonial-avatar">
                                    <?= esc($avatar); ?>
                                </div>

                                <div>
                                    <h3><?= esc($name); ?></h3>

                                    <?php if (!empty($label)) : ?>
                                        <span><?= esc($label); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</section>