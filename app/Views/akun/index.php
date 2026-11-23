<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Akun Saya</h1>
        <form action="<?= url('/keluar') ?>" method="post"><?= csrf_field() ?>
            <button class="text-sm text-gray-500 hover:text-red-600">Keluar</button>
        </form>
    </div>

    <div class="grid sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-brand-600 to-brand-500 text-white rounded-2xl p-5">
            <div class="text-sm opacity-80">Poin Saya</div>
            <div class="text-3xl font-bold"><?= (int) ($user['poin'] ?? 0) ?></div>
            <a href="<?= url('/akun/voucher') ?>" class="text-xs underline">Tukar voucher &rarr;</a>
        </div>
        <a href="<?= url('/rutinitas') ?>" class="bg-white border rounded-2xl p-5 hover:border-brand-500">
            <div class="text-2xl mb-1">🔁</div>
            <div class="font-semibold text-gray-800">Rutinitas</div>
            <div class="text-xs text-gray-500">Pesan ulang cepat</div>
        </a>
        <div class="bg-white border rounded-2xl p-5">
            <div class="text-sm text-gray-500">Nama</div>
            <div class="font-semibold text-gray-800"><?= e($user['nama'] ?? '-') ?></div>
        </div>
        <div class="bg-white border rounded-2xl p-5">
            <div class="text-sm text-gray-500">Tipe</div>
            <div class="font-semibold text-gray-800 capitalize"><?= e($user['tipe'] ?? 'umum') ?></div>
        </div>
    </div>

    <div class="bg-white border rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800">Riwayat Pesanan</h2>
            <a href="<?= url('/akun/poin') ?>" class="text-sm text-brand-600">Lihat poin</a>
        </div>
        <?php if (empty($pesanan)): ?>
            <p class="text-sm text-gray-400">Belum ada pesanan.</p>
        <?php else: ?>
            <div class="divide-y">
                <?php foreach ($pesanan as $o): ?>
                    <div class="py-2 flex items-center justify-between text-sm">
                        <a href="<?= url('/pesanan/' . $o['nota_id']) ?>" class="text-brand-600"><?= e($o['nota_id']) ?></a>
                        <span class="text-gray-400 hidden sm:inline"><?= e(date('d/m/Y H:i', strtotime($o['tanggal']))) ?></span>
                        <span class="text-gray-800"><?= rupiah($o['total_bayar']) ?></span>
                        <span class="text-xs bg-gray-100 px-2 py-0.5 rounded-full"><?= e($o['status']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
