<div class="max-w-2xl">
    <a href="<?= url('/admin/bon') ?>" class="text-sm text-gray-500">&larr; Kembali</a>
    <div class="bg-white border rounded-2xl p-6 mt-3">
        <h2 class="font-bold text-lg text-gray-800 mb-4">Bon #<?= (int) $bon['id'] ?></h2>

        <dl class="grid sm:grid-cols-2 gap-y-2 text-sm mb-4">
            <dt class="text-gray-500">Pelanggan</dt><dd class="text-gray-800"><?= e($bon['nama_lengkap'] ?: $bon['pelanggan_id']) ?></dd>
            <dt class="text-gray-500">Telepon</dt><dd class="text-gray-800"><?= e($bon['telepon'] ?: '-') ?></dd>
            <dt class="text-gray-500">Nota</dt><dd class="text-gray-800"><?= e($bon['nota_id'] ?: '-') ?></dd>
            <dt class="text-gray-500">Jatuh Tempo</dt><dd class="text-gray-800"><?= $bon['jatuh_tempo'] ? e(date('d/m/Y', strtotime($bon['jatuh_tempo']))) : '-' ?></dd>
            <dt class="text-gray-500">Keterangan</dt><dd class="text-gray-800"><?= e($bon['keterangan'] ?: '-') ?></dd>
        </dl>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-gray-50 rounded-xl p-3"><div class="text-xs text-gray-500">Total</div><div class="font-bold"><?= rupiah($bon['total']) ?></div></div>
            <div class="bg-gray-50 rounded-xl p-3"><div class="text-xs text-gray-500">Sisa</div><div class="font-bold text-red-600"><?= rupiah($bon['sisa']) ?></div></div>
            <div class="bg-gray-50 rounded-xl p-3"><div class="text-xs text-gray-500">Status</div><div class="font-bold capitalize"><?= e(str_replace('_', ' ', $bon['status'])) ?></div></div>
        </div>

        <?php if ((float) $bon['sisa'] > 0): ?>
            <form action="<?= url('/admin/bon/' . $bon['id'] . '/bayar') ?>" method="post" class="flex items-end gap-2 mb-4">
                <?= csrf_field() ?>
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">Jumlah Bayar</label>
                    <input type="number" name="jumlah" min="1" max="<?= (int) $bon['sisa'] ?>" class="border rounded-lg px-3 py-2 text-sm" required></div>
                <div class="flex-1"><label class="block text-xs font-semibold text-gray-500 mb-1">Keterangan</label>
                    <input type="text" name="keterangan" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <button class="bg-green-600 text-white rounded-lg px-4 py-2 text-sm">Catat Bayar</button>
            </form>
        <?php endif; ?>

        <h3 class="font-semibold text-gray-800 mb-2">Riwayat Pembayaran</h3>
        <div class="divide-y text-sm">
            <?php foreach ($bayar as $by): ?>
                <div class="py-2 flex justify-between">
                    <span class="text-gray-600"><?= e(date('d/m/Y H:i', strtotime($by['tanggal']))) ?> — <?= e($by['keterangan'] ?: 'pembayaran') ?></span>
                    <span class="text-green-600 font-medium"><?= rupiah($by['jumlah']) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($bayar)): ?>
                <p class="text-gray-400 py-2">Belum ada pembayaran.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
