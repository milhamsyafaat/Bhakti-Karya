<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border rounded-2xl p-5">
        <div class="text-sm text-gray-500">Total Piutang</div>
        <div class="text-2xl font-bold text-gray-900"><?= rupiah($ringkasan['total']) ?></div>
    </div>
    <div class="bg-white border rounded-2xl p-5">
        <div class="text-sm text-gray-500">Sisa Belum Terbayar</div>
        <div class="text-2xl font-bold text-red-600"><?= rupiah($ringkasan['sisa']) ?></div>
    </div>
    <div class="bg-white border rounded-2xl p-5">
        <div class="text-sm text-gray-500">Bon Belum Lunas</div>
        <div class="text-2xl font-bold text-brand-600"><?= (int) $ringkasan['belum_lunas'] ?></div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Form buat bon -->
    <div class="bg-white border rounded-2xl p-6 h-fit">
        <h2 class="font-semibold text-gray-800 mb-4">Buat Bon Baru</h2>
        <form action="<?= url('/admin/bon') ?>" method="post" class="space-y-3" id="bon-form">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Pesanan (opsional)</label>
                <select name="nota_id" id="nota-select" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">— Tanpa pesanan —</option>
                    <?php foreach ($pesananBelumBon as $o): ?>
                        <option value="<?= e($o['nota_id']) ?>"
                                data-pelanggan="<?= e($o['pelanggan_id']) ?>"
                                data-total="<?= (int) $o['total_bayar'] ?>">
                            <?= e($o['nota_id']) ?> — <?= e($o['nama_pelanggan'] ?: $o['pelanggan_id']) ?> (<?= rupiah($o['total_bayar']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Pelanggan</label>
                <select name="pelanggan_id" id="pel-select" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                    <option value="">— Pilih pelanggan —</option>
                    <?php foreach ($pelanggan as $c): ?>
                        <option value="<?= e($c['id']) ?>"><?= e($c['nama_lengkap']) ?> (<?= e($c['telepon']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Total Bon</label>
                    <input type="number" name="total" id="total-input" min="1" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Jatuh Tempo</label>
                    <input type="date" name="jatuh_tempo" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Keterangan</label>
                <input type="text" name="keterangan" placeholder="mis. tempo 1 minggu" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <button class="w-full bg-brand-600 text-white rounded-lg py-2 text-sm font-medium">Simpan Bon</button>
        </form>
    </div>

    <!-- Daftar bon -->
    <div class="lg:col-span-2 bg-white border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-3">Pelanggan</th><th>Total</th><th>Sisa</th><th>Jatuh Tempo</th><th>Status</th><th class="text-right px-4">Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($bons as $b): ?>
                <tr class="border-t">
                    <td class="px-4 py-2">
                        <a href="<?= url('/admin/bon/' . $b['id']) ?>" class="text-gray-800 hover:text-brand-600"><?= e($b['nama_lengkap'] ?: $b['pelanggan_id']) ?></a>
                        <?php if (!empty($b['nota_id'])): ?><div class="text-xs text-gray-400"><?= e($b['nota_id']) ?></div><?php endif; ?>
                    </td>
                    <td><?= rupiah($b['total']) ?></td>
                    <td class="font-medium <?= (float) $b['sisa'] > 0 ? 'text-red-600' : 'text-green-600' ?>"><?= rupiah($b['sisa']) ?></td>
                    <td class="text-gray-500"><?= $b['jatuh_tempo'] ? e(date('d/m/Y', strtotime($b['jatuh_tempo']))) : '-' ?></td>
                    <td>
                        <?php $warna = ['belum_lunas' => 'bg-red-100 text-red-600', 'sebagian' => 'bg-yellow-100 text-yellow-700', 'lunas' => 'bg-green-100 text-green-700'][$b['status']] ?? 'bg-gray-100'; ?>
                        <span class="text-xs px-2 py-0.5 rounded-full <?= $warna ?>"><?= e(str_replace('_', ' ', $b['status'])) ?></span>
                    </td>
                    <td class="text-right px-4">
                        <?php if ((float) $b['sisa'] > 0): ?>
                            <form action="<?= url('/admin/bon/' . $b['id'] . '/bayar') ?>" method="post" class="flex items-center gap-1 justify-end">
                                <?= csrf_field() ?>
                                <input type="number" name="jumlah" min="1" max="<?= (int) $b['sisa'] ?>" placeholder="Bayar"
                                       class="w-24 border rounded px-2 py-1 text-xs">
                                <button class="bg-green-600 text-white text-xs px-2 py-1 rounded">Catat</button>
                            </form>
                        <?php else: ?>
                            <span class="text-xs text-green-600">Lunas</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($bons)): ?>
                <tr><td colspan="6" class="text-center py-6 text-gray-400">Belum ada bon.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('nota-select').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.pelanggan) {
        document.getElementById('pel-select').value = opt.dataset.pelanggan;
        document.getElementById('total-input').value = opt.dataset.total;
    }
});
</script>
