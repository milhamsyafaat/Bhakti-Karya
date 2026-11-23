<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="<?= url('/akun') ?>" class="text-sm text-gray-500">&larr; Kembali</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2 mb-1">Tukar Poin dengan Voucher</h1>
    <p class="text-sm text-gray-500 mb-6">Poin Anda: <strong><?= (int) ($user['poin'] ?? 0) ?></strong></p>

    <?php if (flash('kode_voucher')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">
            Kode voucher Anda: <strong><?= e(flash('kode_voucher')) ?></strong>
        </div>
    <?php endif; ?>

    <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <?php foreach ($katalog as $v): $cukup = (int) ($user['poin'] ?? 0) >= (int) $v['poin_dibutuhkan']; ?>
            <div class="bg-white border rounded-2xl p-5 flex flex-col">
                <div class="text-3xl mb-2">🎟️</div>
                <div class="font-semibold text-gray-800"><?= e($v['nama']) ?></div>
                <div class="text-xs text-gray-500 mb-3">Min. belanja <?= rupiah($v['min_belanja']) ?></div>
                <div class="mt-auto flex items-center justify-between">
                    <span class="text-brand-600 font-bold"><?= (int) $v['poin_dibutuhkan'] ?> poin</span>
                    <form action="<?= url('/akun/tukar-voucher') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="katalog_id" value="<?= e($v['id']) ?>">
                        <button <?= $cukup ? '' : 'disabled' ?>
                                class="text-xs px-3 py-1.5 rounded-lg <?= $cukup ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' ?>">
                            Tukar
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="font-semibold text-gray-800 mb-3">Voucher Saya</h2>
    <div class="bg-white border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-3">Kode</th><th>Voucher</th><th>Status</th><th>Kedaluwarsa</th></tr>
            </thead>
            <tbody>
            <?php foreach ($punya as $vp): ?>
                <tr class="border-t">
                    <td class="px-4 py-2 font-mono text-gray-700"><?= e($vp['kode']) ?></td>
                    <td><?= e($vp['nama_voucher']) ?></td>
                    <td><span class="text-xs px-2 py-0.5 rounded-full bg-gray-100"><?= e($vp['status']) ?></span></td>
                    <td class="text-gray-500"><?= e(date('d/m/Y', strtotime($vp['tgl_kedaluwarsa']))) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($punya)): ?>
                <tr><td colspan="4" class="text-center py-6 text-gray-400">Belum ada voucher.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
