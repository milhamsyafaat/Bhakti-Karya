<?php
$steps = ['Menunggu Pembayaran', 'Diproses', 'Dikemas', 'Dikirim', 'Tiba', 'Diterima', 'Selesai'];
$currentIdx = array_search($pesanan['status'], $steps, true);
$currentIdx = $currentIdx === false ? 0 : $currentIdx;
?>
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5 mb-6 text-center">
        <div class="text-3xl mb-1">✅</div>
        <h1 class="font-bold text-gray-800">Pesanan <?= e($pesanan['nota_id']) ?> diterima</h1>
        <p class="text-sm text-gray-500">Status saat ini: <strong><?= e($pesanan['status']) ?></strong></p>
    </div>

    <!-- Timeline -->
    <div class="bg-white border rounded-2xl p-6 mb-6">
        <div class="flex justify-between items-center">
            <?php foreach ($steps as $i => $step): ?>
                <div class="flex-1 text-center relative">
                    <div class="mx-auto w-7 h-7 rounded-full flex items-center justify-center text-xs
                        <?= $i <= $currentIdx ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-400' ?>">
                        <?= $i + 1 ?>
                    </div>
                    <div class="text-[10px] mt-1 <?= $i <= $currentIdx ? 'text-brand-600 font-medium' : 'text-gray-400' ?>">
                        <?= e($step) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Detail -->
    <div class="bg-white border rounded-2xl p-6 mb-6">
        <h2 class="font-semibold text-gray-800 mb-4">Rincian Item</h2>
        <div class="divide-y">
            <?php foreach ($detail as $d): ?>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray-700"><?= e($d['nama_produk']) ?> ×<?= (int) $d['jumlah'] ?></span>
                    <span class="text-gray-800"><?= rupiah($d['subtotal']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="border-t mt-3 pt-3 space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span><?= rupiah($pesanan['subtotal']) ?></span></div>
            <div class="flex justify-between"><span class="text-gray-500">Ongkir</span><span><?= (float) $pesanan['ongkir'] <= 0 ? 'GRATIS' : rupiah($pesanan['ongkir']) ?></span></div>
            <div class="flex justify-between font-bold text-base"><span>Total</span><span><?= rupiah($pesanan['total_bayar']) ?></span></div>
        </div>
    </div>

    <div class="bg-white border rounded-2xl p-6">
        <h2 class="font-semibold text-gray-800 mb-3">Info Pesanan</h2>
        <dl class="grid sm:grid-cols-2 gap-y-2 text-sm">
            <dt class="text-gray-500">Nama</dt><dd class="text-gray-800"><?= e($pesanan['nama_pelanggan']) ?></dd>
            <dt class="text-gray-500">No. HP</dt><dd class="text-gray-800"><?= e($pesanan['no_hp']) ?></dd>
            <dt class="text-gray-500">Alamat</dt><dd class="text-gray-800"><?= e($pesanan['alamat']) ?></dd>
            <dt class="text-gray-500">Metode kirim</dt><dd class="text-gray-800"><?= e($pesanan['metode_kirim']) ?></dd>
            <dt class="text-gray-500">Pembayaran</dt><dd class="text-gray-800"><?= e(strtoupper($pesanan['metode_bayar'])) ?> · <?= e($pesanan['pembayaran_status']) ?></dd>
            <dt class="text-gray-500">Jadwal</dt><dd class="text-gray-800"><?= e($pesanan['jadwal'] ?: '-') ?></dd>
        </dl>
    </div>

    <div class="text-center mt-6">
        <a href="<?= url('/produk') ?>" class="text-brand-600 text-sm">Belanja lagi &rarr;</a>
    </div>
</div>
