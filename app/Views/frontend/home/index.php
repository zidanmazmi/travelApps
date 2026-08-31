<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<?= $this->include('templates/frontend/sections/home/hero'); ?>
<?= $this->include('templates/frontend/sections/home/features'); ?>
<?= $this->include('templates/frontend/sections/home/packages'); ?>
<?= $this->include('templates/frontend/sections/home/about'); ?>
<?= $this->include('templates/frontend/sections/home/stats'); ?>
<?= $this->include('templates/frontend/sections/home/testimonials'); ?>
<?= $this->include('templates/frontend/sections/home/gallery'); ?>
<?= $this->include('templates/frontend/sections/home/cta'); ?>
<?= $this->include('templates/frontend/sections/home/faqs'); ?>

<?= $this->endSection(); ?>