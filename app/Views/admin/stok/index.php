<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white border rounded-2xl p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Penyesuaian Stok</h2>
        <form action="<?= url('/admin/stok') ?>" method="post" class="space-y-4">
            <?= csrf_field() ?>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Produk</label>
                    <select name="produk_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <?php foreach ($produk as $p): ?>
                            <option value="<?= (int) $p['id'] ?>"><?= e($p['nama_produk']) ?> (stok: <?= (int) $p['stok'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Jenis</label>
                    <select name="jenis" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="masuk">Stok Masuk</option>
                        <option value="keluar">Stok Keluar</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Jumlah</label>
                    <input type="number" name="jumlah" value="1" min="1" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="mis. kulakan / rusak" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <button class="bg-brand-600 text-white rounded-lg px-4 py-2 text-sm font-medium">Simpan</button>
        </form>
    </div>

    <div class="bg-white border rounded-2xl p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Riwayat Terakhir</h2>
        <ul class="space-y-2 text-sm">
            <?php foreach ($logs as $l): ?>
                <li class="flex justify-between border-b pb-1">
                    <span class="truncate pr-2 <?= $l['jenis'] === 'masuk' ? 'text-green-600' : 'text-red-500' ?>">
                        <?= $l['jenis'] === 'masuk' ? '+' : '-' ?><?= (int) $l['jumlah'] ?> <?= e($l['nama_produk']) ?>
                    </span>
                    <span class="text-gray-400 text-xs"><?= e(date('d/m H:i', strtotime($l['tanggal']))) ?></span>
                </li>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
                <li class="text-gray-400">Belum ada mutasi.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<div class="bg-white border rounded-2xl p-6 mt-6">
    <h2 class="font-semibold text-gray-800 mb-4">Daftar Stok Produk</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-gray-500 border-b">
                <tr><th class="py-2">Produk</th><th>Stok</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($produk as $p): ?>
                <tr class="border-b last:border-0">
                    <td class="py-2"><?= e($p['nama_produk']) ?></td>
                    <td><?= (int) $p['stok'] ?> <?= e($p['satuan']) ?></td>
                    <td>
                        <?php if ((int) $p['stok'] <= $lowStock): ?>
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Menipis</span>
                        <?php else: ?>
                            <span class="text-xs bg-green-100 text-green-600 px-2 py-0.5 rounded-full">Aman</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border rounded-2xl p-6 mt-6">
    <h2 class="font-semibold text-gray-800 mb-4">🔔 Permintaan Notifikasi Stok</h2>
    <?php if (empty($alert)): ?>
        <p class="text-sm text-gray-400">Tidak ada permintaan notifikasi.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 border-b">
                    <tr><th class="py-2">Produk</th><th>Nama</th><th>Telepon</th><th>Stok Kini</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                <?php foreach ($alert as $a): ?>
                    <tr class="border-b last:border-0">
                        <td class="py-2"><?= e($a['nama_produk']) ?></td>
                        <td><?= e($a['nama'] ?: '-') ?></td>
                        <td class="font-mono"><?= e($a['telepon']) ?></td>
                        <td>
                            <?php if ((int) $a['stok'] > 0): ?>
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Tersedia (<?= (int) $a['stok'] ?>)</span>
                            <?php else: ?>
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Habis</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-gray-400"><?= e(date('d/m/Y', strtotime($a['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
