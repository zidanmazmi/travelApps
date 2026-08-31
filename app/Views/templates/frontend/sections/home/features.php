<section id="layanan" class="section-services wl-section wl-services">
    <div class="container">
        <div class="wl-section-heading text-center" data-reveal="up">
            <span class="wl-section-kicker">Pelayanan Kami</span>
            <h2>Setiap detail perjalanan disiapkan dengan penuh perhatian.</h2>
            <p>Pelayanan yang jelas, terarah, dan nyaman untuk mendukung kekhusyukan ibadah jamaah.</p>
        </div>

        <div class="row g-4">
            <?php
            $services = [
                ['bi-patch-check', 'Informasi Terpercaya', 'Paket, jadwal, fasilitas, dan kebutuhan perjalanan disampaikan secara transparan.'],
                ['bi-person-heart', 'Pendamping Jamaah', 'Jamaah mendapat arahan dan pendampingan sejak persiapan hingga perjalanan.'],
                ['bi-buildings', 'Akomodasi Terpilih', 'Pilihan hotel, transportasi, dan itinerary disusun untuk kenyamanan jamaah.'],
                ['bi-headset', 'Layanan Responsif', 'Tim ' . site_setting('site_name', 'Travel Umroh & Haji') . ' siap membantu konsultasi dan kebutuhan informasi jamaah.'],
            ];
            ?>

            <?php foreach ($services as $index => $service) : ?>
                <div class="col-md-6 col-lg-3" data-reveal="up" data-delay="<?= $index * 90; ?>">
                    <article class="wl-service-card h-100" data-tilt>
                        <div class="wl-service-number">0<?= $index + 1; ?></div>
                        <div class="wl-service-icon">
                            <i class="bi <?= esc($service[0]); ?>"></i>
                        </div>
                        <h3><?= esc($service[1]); ?></h3>
                        <p><?= esc($service[2]); ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
