<div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white border rounded-2xl p-5">
        <div class="text-sm text-gray-500">Pesanan Hari Ini</div>
        <div class="text-2xl font-bold text-gray-900"><?= (int) $statPesanan['jumlah'] ?></div>
    </div>
    <div class="bg-white border rounded-2xl p-5">
        <div class="text-sm text-gray-500">Omzet Hari Ini</div>
        <div class="text-2xl font-bold text-brand-600"><?= rupiah($statPesanan['omzet']) ?></div>
    </div>
    <div class="bg-white border rounded-2xl p-5">
        <div class="text-sm text-gray-500">Produk Aktif</div>
        <div class="text-2xl font-bold text-gray-900"><?= (int) $totalProduk ?></div>
    </div>
    <div class="bg-white border rounded-2xl p-5">
        <div class="text-sm text-gray-500">Total Pelanggan</div>
        <div class="text-2xl font-bold text-gray-900"><?= (int) $totalPelanggan ?></div>
    </div>
    <div class="bg-white border rounded-2xl p-5">
        <div class="text-sm text-gray-500">Piutang (Bon)</div>
        <div class="text-2xl font-bold text-red-600"><?= rupiah($piutang['sisa']) ?></div>
        <a href="<?= url('/admin/bon') ?>" class="text-xs text-brand-600"><?= (int) $piutang['jumlah'] ?> bon aktif &rarr;</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white border rounded-2xl p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Pesanan Terbaru</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 border-b">
                    <tr><th class="py-2">Nota</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($pesananTerbaru as $p): ?>
                    <tr class="border-b last:border-0">
                        <td class="py-2"><a href="<?= url('/admin/pesanan/' . $p['nota_id']) ?>" class="text-brand-600"><?= e($p['nota_id']) ?></a></td>
                        <td><?= e($p['nama_pelanggan'] ?: '-') ?></td>
                        <td><?= rupiah($p['total_bayar']) ?></td>
                        <td><span class="text-xs bg-gray-100 px-2 py-0.5 rounded-full"><?= e($p['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($pesananTerbaru)): ?>
                    <tr><td colspan="4" class="py-4 text-center text-gray-400">Belum ada pesanan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white border rounded-2xl p-6">
        <h2 class="font-semibold text-gray-800 mb-4">⚠️ Stok Menipis</h2>
        <?php if (empty($stokMenipis)): ?>
            <p class="text-sm text-gray-400">Semua stok aman.</p>
        <?php else: ?>
            <ul class="space-y-2 text-sm">
                <?php foreach ($stokMenipis as $s): ?>
                    <li class="flex justify-between">
                        <span class="text-gray-700 truncate pr-2"><?= e($s['nama_produk']) ?></span>
                        <span class="text-red-600 font-medium"><?= (int) $s['stok'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="<?= url('/admin/stok') ?>" class="inline-block mt-4 text-sm text-brand-600">Kelola stok &rarr;</a>
    </div>
</div>
