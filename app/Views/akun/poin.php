<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="<?= url('/akun') ?>" class="text-sm text-gray-500">&larr; Kembali</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2 mb-1">Riwayat Poin</h1>
    <p class="text-sm text-gray-500 mb-6">Saldo saat ini: <strong><?= (int) ($user['poin'] ?? 0) ?> poin</strong></p>

    <div class="bg-white border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-3">Tanggal</th><th>Keterangan</th><th class="text-right">Poin</th><th class="text-right px-4">Saldo</th></tr>
            </thead>
            <tbody>
            <?php foreach ($log as $l): ?>
                <tr class="border-t">
                    <td class="px-4 py-2 text-gray-500"><?= e(date('d/m/Y', strtotime($l['tanggal']))) ?></td>
                    <td class="text-gray-700"><?= e($l['keterangan']) ?></td>
                    <td class="text-right <?= $l['jenis'] === 'masuk' ? 'text-green-600' : 'text-red-500' ?>">
                        <?= $l['jenis'] === 'masuk' ? '+' : '-' ?><?= (int) $l['jumlah'] ?>
                    </td>
                    <td class="text-right px-4 text-gray-700"><?= (int) $l['saldo_sesudah'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($log)): ?>
                <tr><td colspan="4" class="text-center py-6 text-gray-400">Belum ada riwayat poin.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
