<div class="flex items-center justify-between mb-4">
    <form method="get" class="flex gap-2 items-center">
        <label class="text-sm text-gray-500">Filter status:</label>
        <select name="status" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">Semua</option>
            <?php foreach (['Menunggu Pembayaran','Diproses','Dikemas','Dikirim','Tiba','Diterima','Selesai','Dibatalkan'] as $s): ?>
                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
        <button class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm">Terapkan</button>
    </form>
</div>

<div class="bg-white border rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500">
            <tr><th class="px-4 py-3">Nota</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th><th class="text-right px-4">Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($pesanan as $p): ?>
            <tr class="border-t">
                <td class="px-4 py-2 text-brand-600"><?= e($p['nota_id']) ?></td>
                <td><?= e($p['nama_pelanggan'] ?: '-') ?></td>
                <td class="text-gray-500"><?= e(date('d/m/Y H:i', strtotime($p['tanggal']))) ?></td>
                <td><?= rupiah($p['total_bayar']) ?></td>
                <td><span class="text-xs bg-gray-100 px-2 py-0.5 rounded-full"><?= e($p['status']) ?></span></td>
                <td class="text-right px-4"><a href="<?= url('/admin/pesanan/' . $p['nota_id']) ?>" class="text-brand-600 text-xs">Detail</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($pesanan)): ?>
            <tr><td colspan="6" class="text-center py-6 text-gray-400">Tidak ada pesanan.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
