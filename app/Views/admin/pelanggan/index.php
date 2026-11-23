<div class="bg-white border rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500">
            <tr><th class="px-4 py-3">ID</th><th>Nama</th><th>Telepon</th><th>Wilayah</th><th>Tipe</th><th>Poin</th><th>Total Belanja</th></tr>
        </thead>
        <tbody>
        <?php foreach ($pelanggan as $c): ?>
            <tr class="border-t">
                <td class="px-4 py-2 text-gray-500"><?= e($c['id']) ?></td>
                <td class="text-gray-800"><?= e($c['nama_lengkap']) ?></td>
                <td><?= e($c['telepon']) ?></td>
                <td class="text-gray-500"><?= e($c['kelurahan'] ?: '-') ?>, <?= e($c['kecamatan'] ?: '-') ?></td>
                <td>
                    <span class="text-xs px-2 py-0.5 rounded-full <?= $c['tipe'] === 'reseller' ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600' ?>">
                        <?= e($c['tipe']) ?>
                    </span>
                </td>
                <td><?= (int) $c['poin'] ?></td>
                <td><?= rupiah($c['total_belanja']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($pelanggan)): ?>
            <tr><td colspan="7" class="text-center py-6 text-gray-400">Belum ada pelanggan.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
