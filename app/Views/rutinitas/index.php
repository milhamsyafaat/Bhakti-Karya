<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Rutinitas Belanja</h1>
        <a href="<?= url('/produk') ?>" class="text-sm text-brand-600">Belanja dulu</a>
    </div>
    <p class="text-sm text-gray-500 mb-6">Simpan kombinasi belanjaan rutin Anda, lalu pesan ulang sekali klik.</p>

    <?php if (empty($rutinitas)): ?>
        <div class="bg-white border rounded-2xl p-12 text-center">
            <div class="text-5xl mb-3">🔁</div>
            <p class="text-gray-500 mb-4">Belum ada rutinitas tersimpan.<br>Isi keranjang, lalu klik "Simpan sebagai Rutinitas" di halaman keranjang.</p>
            <a href="<?= url('/produk') ?>" class="bg-brand-600 text-white px-5 py-2.5 rounded-lg">Lihat Produk</a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($rutinitas as $rt): ?>
                <div class="bg-white border rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-800">🔁 <?= e($rt['nama']) ?></h2>
                        <span class="text-xs text-gray-400"><?= e(date('d/m/Y', strtotime($rt['created_at']))) ?></span>
                    </div>
                    <div class="text-sm text-gray-600 divide-y mb-3">
                        <?php foreach ($rt['detail'] as $d): ?>
                            <div class="py-1.5 flex justify-between">
                                <span><?= e($d['nama']) ?> ×<?= $d['jumlah'] ?>
                                    <?php if ($d['stok'] <= 0): ?><span class="text-red-500 text-xs">(habis)</span><?php endif; ?>
                                </span>
                                <span><?= rupiah($d['harga'] * $d['jumlah']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-gray-800">Estimasi: <?= rupiah($rt['total']) ?></span>
                        <div class="flex gap-2">
                            <form action="<?= url('/rutinitas/' . $rt['id'] . '/pesan') ?>" method="post">
                                <?= csrf_field() ?>
                                <button class="bg-brand-600 text-white text-sm px-4 py-1.5 rounded-lg">Pesan Ulang</button>
                            </form>
                            <form action="<?= url('/rutinitas/' . $rt['id'] . '/hapus') ?>" method="post"
                                  onsubmit="return confirm('Hapus rutinitas ini?')">
                                <?= csrf_field() ?>
                                <button class="text-red-500 text-sm px-2">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
