<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Keranjang Belanja</h1>

    <?php if (empty($lines)): ?>
        <div class="bg-white border rounded-2xl p-12 text-center">
            <div class="text-5xl mb-3">🛒</div>
            <p class="text-gray-500 mb-4">Keranjang masih kosong.</p>
            <a href="<?= url('/produk') ?>" class="bg-brand-600 text-white px-5 py-2.5 rounded-lg">Mulai Belanja</a>
        </div>
    <?php else: ?>
        <div class="bg-white border rounded-2xl divide-y">
            <?php foreach ($lines as $line): $p = $line['produk']; ?>
                <div class="p-4 flex gap-4 items-center">
                    <img src="<?= product_image($p['gambar'] ?? null) ?>" onerror="<?= onerror_image() ?>" class="w-20 h-20 object-cover rounded-lg bg-gray-100" alt="">
                    <div class="flex-1 min-w-0">
                        <a href="<?= url('/produk/' . $p['id']) ?>" class="font-medium text-gray-800 hover:text-brand-600 line-clamp-1"><?= e($p['nama_produk']) ?></a>
                        <div class="text-sm text-brand-600 font-semibold"><?= rupiah($line['harga']) ?></div>
                    </div>
                    <form action="<?= url('/keranjang/update') ?>" method="post" class="flex items-center gap-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="produk_id" value="<?= (int) $p['id'] ?>">
                        <input type="number" name="qty" value="<?= (int) $line['qty'] ?>" min="1"
                               class="w-16 border rounded-lg px-2 py-1 text-center text-sm">
                        <button class="text-xs text-gray-500 hover:text-brand-600">Ubah</button>
                    </form>
                    <div class="w-28 text-right font-semibold text-gray-800"><?= rupiah($line['subtotal']) ?></div>
                    <form action="<?= url('/keranjang/hapus') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="produk_id" value="<?= (int) $p['id'] ?>">
                        <button class="text-red-400 hover:text-red-600 text-lg" title="Hapus">&times;</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-white border rounded-2xl p-5 mt-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <span class="text-gray-500 text-sm">Subtotal</span>
                    <div class="text-2xl font-bold text-gray-900"><?= rupiah($subtotal) ?></div>
                </div>
                <div class="flex gap-2">
                    <?php if (pelanggan_auth()->check()): ?>
                        <form action="<?= url('/rutinitas/simpan') ?>" method="post" class="flex gap-2">
                            <?= csrf_field() ?>
                            <input type="text" name="nama" placeholder="Nama rutinitas" required
                                   class="border rounded-lg px-3 py-2 text-sm w-40">
                            <button class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg">Simpan Rutinitas</button>
                        </form>
                    <?php endif; ?>
                    <a href="<?= url('/checkout') ?>" class="bg-brand-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-brand-700">
                        Lanjut ke Checkout
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
