<?php
$success = flash('success');
$error = flash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Masuk') ?> — <?= e(config('app.name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-6">
            <div class="text-4xl">🛒</div>
            <h1 class="text-xl font-bold text-gray-800 mt-2"><?= e(config('app.name')) ?></h1>
            <p class="text-sm text-gray-500">Panel Administrasi</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 text-sm rounded-lg px-3 py-2 mb-3"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 text-sm rounded-lg px-3 py-2 mb-3"><?= e($error) ?></div>
        <?php endif; ?>

        <?= $content ?>

        <p class="text-center text-xs text-gray-400 mt-6">
            <a href="<?= url('/') ?>" class="hover:text-brand-600">&larr; Kembali ke toko</a>
        </p>
    </div>
</body>
</html>
