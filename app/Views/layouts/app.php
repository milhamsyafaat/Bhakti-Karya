<?php
use App\Core\Session;
use App\Core\Cart;
$cartCount = Cart::count();
$success = flash('success');
$error = flash('error');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Bhakti Karya') ?> — <?= e(config('app.name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#fff7ed',100:'#ffedd5',500:'#f97316',600:'#ea580c',700:'#c2410c' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<header class="bg-white border-b sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center gap-4">
        <a href="<?= url('/') ?>" class="flex items-center gap-2 font-bold text-brand-600 text-lg">
            <span class="text-2xl">🛒</span> Bhakti Karya
        </a>
        <form action="<?= url('/produk') ?>" method="get" class="flex-1 hidden sm:block">
            <input type="text" name="q" placeholder="Cari sembako, minuman, snack..."
                   class="w-full border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        </form>
        <nav class="flex items-center gap-3 text-sm">
            <a href="<?= url('/produk') ?>" class="hover:text-brand-600 hidden md:inline">Produk</a>
            <a href="<?= url('/lacak') ?>" class="hover:text-brand-600 hidden md:inline">Lacak</a>
            <?php if (Session::has('pelanggan')): ?>
                <a href="<?= url('/rutinitas') ?>" class="hover:text-brand-600 hidden md:inline">Rutinitas</a>
            <?php endif; ?>
            <a href="<?= url('/keranjang') ?>" class="relative hover:text-brand-600">
                🛒
                <?php if ($cartCount > 0): ?>
                    <span class="absolute -top-2 -right-3 bg-brand-600 text-white text-[10px] rounded-full px-1.5"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <?php if (Session::has('auth_user')): ?>
                <a href="<?= url('/admin') ?>" class="bg-brand-600 text-white px-3 py-1.5 rounded-lg">Admin</a>
            <?php elseif (Session::has('pelanggan')): ?>
                <a href="<?= url('/akun') ?>" class="text-gray-600 hover:text-brand-600 hidden sm:inline">
                    <?= e(mb_strimwidth((string) Session::get('pelanggan.nama'), 0, 14, '…')) ?>
                </a>
                <a href="<?= url('/akun') ?>" class="bg-brand-100 text-brand-700 px-2 py-1 rounded-lg text-xs">
                    <?= (int) Session::get('pelanggan.poin') ?> poin
                </a>
            <?php else: ?>
                <a href="<?= url('/masuk') ?>" class="text-gray-600 hover:text-brand-600">Masuk</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php if ($success): ?>
    <div class="bg-green-100 text-green-800 text-sm text-center py-2"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-100 text-red-800 text-sm text-center py-2"><?= e($error) ?></div>
<?php endif; ?>

<main class="flex-1">
    <?= $content ?>
</main>

<footer class="bg-white border-t mt-12">
    <div class="max-w-6xl mx-auto px-4 py-8 text-sm text-gray-500 grid md:grid-cols-3 gap-6">
        <div>
            <div class="font-bold text-gray-700 mb-2">Warung Grosir Bhakti Karya</div>
            <p>Cilangkap, Kec. Tapos, Kota Depok</p>
        </div>
        <div>
            <div class="font-bold text-gray-700 mb-2">Layanan</div>
            <p>Antar ke rumah &amp; ambil di toko</p>
            <p>QRIS · COD · Transfer</p>
        </div>
        <div>
            <div class="font-bold text-gray-700 mb-2">Kontak</div>
            <p>0812-xxxx-xxxx</p>
        </div>
    </div>
    <div class="text-center text-xs text-gray-400 pb-6">&copy; <?= date('Y') ?> <?= e(config('app.name')) ?></div>
</footer>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
