<div class="max-w-6xl mx-auto px-4 py-6">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="<?= url('/') ?>" class="hover:text-brand-600">Beranda</a> /
        <a href="<?= url('/produk') ?>" class="hover:text-brand-600">Produk</a> /
        <span class="text-gray-700"><?= e($produk['nama_produk']) ?></span>
    </nav>

    <div class="bg-white border rounded-2xl p-6 grid md:grid-cols-2 gap-8">
        <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden">
            <img src="<?= product_image($produk['gambar'] ?? null) ?>" alt="<?= e($produk['nama_produk']) ?>" onerror="<?= onerror_image() ?>" class="w-full h-full object-cover">
        </div>

        <div>
            <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full mb-2"><?= e($produk['badge_status']) ?></span>
            <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= e($produk['nama_produk']) ?></h1>
            <div class="flex items-baseline gap-3 mb-4">
                <span class="text-3xl font-bold text-brand-600"><?= rupiah($produk['harga']) ?></span>
                <?php if (!empty($produk['harga_lama']) && (float) $produk['harga_lama'] > (float) $produk['harga']): ?>
                    <span class="text-gray-400 line-through"><?= rupiah($produk['harga_lama']) ?></span>
                <?php endif; ?>
            </div>

            <p class="text-gray-600 text-sm mb-4"><?= nl2br(e($produk['deskripsi'])) ?></p>

            <dl class="text-sm text-gray-500 space-y-1 mb-6">
                <div class="flex justify-between border-b py-1"><dt>Satuan</dt><dd class="text-gray-700"><?= e($produk['satuan']) ?></dd></div>
                <div class="flex justify-between border-b py-1"><dt>Min. order</dt><dd class="text-gray-700"><?= (int) $produk['min_order'] ?></dd></div>
                <div class="flex justify-between border-b py-1"><dt>Stok</dt>
                    <dd class="<?= (int) $produk['stok'] <= 0 ? 'text-red-600' : 'text-gray-700' ?>">
                        <?= (int) $produk['stok'] <= 0 ? 'Habis' : (int) $produk['stok'] . ' tersedia' ?>
                    </dd>
                </div>
            </dl>

            <?php if ((int) $produk['stok'] > 0): ?>
                <form action="<?= url('/keranjang/tambah') ?>" method="post" class="flex items-center gap-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="produk_id" value="<?= (int) $produk['id'] ?>">
                    <input type="number" name="qty" value="<?= max(1, (int) $produk['min_order']) ?>"
                           min="<?= max(1, (int) $produk['min_order']) ?>" max="<?= (int) $produk['stok'] ?>"
                           class="w-20 border rounded-lg px-3 py-2 text-center">
                    <button class="flex-1 bg-brand-600 text-white font-semibold py-2.5 rounded-lg hover:bg-brand-700">
                        Tambah ke Keranjang
                    </button>
                </form>
            <?php else: ?>
                <form action="<?= url('/stok-alert') ?>" method="post" class="bg-gray-50 border rounded-lg p-4 space-y-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="produk_id" value="<?= (int) $produk['id'] ?>">
                    <p class="text-sm text-gray-600">Stok habis. Tinggalkan nomor HP untuk diberi tahu saat tersedia.</p>
                    <div class="flex gap-2">
                        <input type="text" name="telepon" placeholder="08xxxxxxxxxx" required
                               class="flex-1 border rounded-lg px-3 py-2 text-sm">
                        <button class="bg-gray-800 text-white text-sm px-4 rounded-lg">Ingatkan Saya</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($related)): ?>
        <h2 class="text-lg font-bold text-gray-800 mt-10 mb-3">Produk Serupa</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <?php foreach ($related as $p): ?>
                <?php require base_path('app/Views/partials/produk-card.php'); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
