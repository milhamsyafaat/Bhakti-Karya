<?php
use App\Core\Session;
$success = flash('success');
$error = flash('error');
$currentUser = Session::get('auth_user');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$menu = [
    ['label' => 'Dashboard', 'href' => '/admin', 'icon' => '📊'],
    ['label' => 'Produk', 'href' => '/admin/produk', 'icon' => '📦'],
    ['label' => 'Pesanan', 'href' => '/admin/pesanan', 'icon' => '🧾'],
    ['label' => 'Stok', 'href' => '/admin/stok', 'icon' => '📈'],
    ['label' => 'Pelanggan', 'href' => '/admin/pelanggan', 'icon' => '👥'],
    ['label' => 'Bon / Piutang', 'href' => '/admin/bon', 'icon' => '📒'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> — <?= e(config('app.name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="flex">
    <!-- Sidebar -->
    <aside class="w-56 bg-gray-900 text-gray-300 min-h-screen p-4 hidden md:block">
        <a href="<?= url('/admin') ?>" class="flex items-center gap-2 text-white font-bold mb-6">
            <span class="text-xl">🛒</span> Bhakti Karya
        </a>
        <nav class="space-y-1">
            <?php foreach ($menu as $item): ?>
                <?php $active = ($path === $item['href']) || ($item['href'] !== '/admin' && strpos($path, $item['href']) === 0); ?>
                <a href="<?= url($item['href']) ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm <?= $active ? 'bg-brand-600 text-white' : 'hover:bg-gray-800' ?>">
                    <span><?= $item['icon'] ?></span> <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="mt-8 border-t border-gray-700 pt-4 text-sm">
            <div class="text-gray-400">Masuk sebagai</div>
            <div class="text-white font-medium"><?= e($currentUser['nama'] ?? '-') ?></div>
            <div class="text-xs text-gray-500 mb-2"><?= e($currentUser['role'] ?? '') ?></div>
            <form action="<?= url('/logout') ?>" method="post">
                <?= csrf_field() ?>
                <button class="text-xs text-red-400 hover:text-red-300">Keluar</button>
            </form>
        </div>
    </aside>

    <!-- Konten -->
    <div class="flex-1 min-w-0">
        <header class="bg-white border-b h-14 flex items-center justify-between px-6">
            <h1 class="font-semibold text-gray-800"><?= e($title ?? 'Admin') ?></h1>
            <a href="<?= url('/') ?>" class="text-sm text-gray-500 hover:text-brand-600">Lihat Toko &rarr;</a>
        </header>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 text-sm px-6 py-2"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 text-sm px-6 py-2"><?= e($error) ?></div>
        <?php endif; ?>

        <main class="p-6">
            <?= $content ?>
        </main>
    </div>
</div>
</body>
</html>
