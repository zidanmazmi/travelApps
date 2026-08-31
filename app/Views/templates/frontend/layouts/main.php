<!DOCTYPE html>
<html lang="id">

<head>
    <?= $this->include('templates/frontend/partials/head'); ?>
</head>

<body>

    <?= $this->include('templates/frontend/partials/navbar'); ?>

    <?= $this->renderSection('content'); ?>

    <?= $this->include('templates/frontend/partials/footer'); ?>

    <?= $this->include('templates/frontend/partials/login_modal'); ?>

    <?= $this->include('templates/frontend/partials/chatbot'); ?>
    <?= $this->include('templates/frontend/partials/scripts'); ?>
</body>

</html>