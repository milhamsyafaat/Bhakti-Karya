<section class="bg-gradient-to-r from-brand-600 to-brand-500 text-white">
    <div class="max-w-6xl mx-auto px-4 py-12">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">Belanja Grosir Lebih Hemat</h1>
        <p class="text-brand-50 mb-6 max-w-xl">Sembako, minuman, snack, dan kebutuhan harian. Antar cepat ke wilayah Tapos &amp; sekitar, atau ambil di toko.</p>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= url('/produk') ?>" class="bg-white text-brand-600 font-semibold px-5 py-2.5 rounded-lg">Mulai Belanja</a>
            <span class="text-sm bg-white/20 px-3 py-1.5 rounded-lg">
                <?= $tokoBuka ? '🟢 Buka' : '🔴 Tutup' ?> · <?= e(substr($jamBuka,0,5)) ?>–<?= e(substr($jamTutup,0,5)) ?> WIB
            </span>
        </div>
    </div>
</section>

<section class="max-w-6xl mx-auto px-4 mt-8">
    <h2 class="text-lg font-bold text-gray-800 mb-3">Kategori</h2>
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-8 gap-3">
        <?php foreach ($kategori as $k): ?>
            <a href="<?= url('/produk?kategori_id=' . $k['id']) ?>"
               class="bg-white border rounded-xl p-3 text-center hover:border-brand-500 hover:shadow transition">
                <div class="text-2xl"><?= e($k['ikon']) ?></div>
                <div class="text-[11px] mt-1 text-gray-600 leading-tight"><?= e($k['nama_kategori']) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="max-w-6xl mx-auto px-4 mt-10">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-bold text-gray-800">⭐ Produk Favorit</h2>
        <a href="<?= url('/produk') ?>" class="text-sm text-brand-600">Lihat semua</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <?php foreach ($favorit as $p): ?>
            <?php $p = $p; require base_path('app/Views/partials/produk-card.php'); ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="max-w-6xl mx-auto px-4 mt-10">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-bold text-gray-800">🔥 Terlaris</h2>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <?php foreach ($terlaris as $p): ?>
            <?php require base_path('app/Views/partials/produk-card.php'); ?>
        <?php endforeach; ?>
    </div>
</section>
