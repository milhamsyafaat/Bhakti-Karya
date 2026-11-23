<div class="max-w-3xl">
    <a href="<?= url('/admin/pesanan') ?>" class="text-sm text-gray-500">&larr; Kembali</a>

    <div class="bg-white border rounded-2xl p-6 mt-3">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-lg text-gray-800"><?= e($pesanan['nota_id']) ?></h2>
            <span class="text-xs bg-gray-100 px-3 py-1 rounded-full"><?= e($pesanan['status']) ?></span>
        </div>

        <div class="divide-y mb-4">
            <?php foreach ($detail as $d): ?>
                <div class="py-2 flex justify-between text-sm">
                    <span><?= e($d['nama_produk']) ?> ×<?= (int) $d['jumlah'] ?></span>
                    <span><?= rupiah($d['subtotal']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="border-t pt-3 space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span><?= rupiah($pesanan['subtotal']) ?></span></div>
            <div class="flex justify-between"><span class="text-gray-500">Ongkir</span><span><?= rupiah($pesanan['ongkir']) ?></span></div>
            <div class="flex justify-between font-bold text-base"><span>Total</span><span><?= rupiah($pesanan['total_bayar']) ?></span></div>
        </div>

        <dl class="grid sm:grid-cols-2 gap-y-2 text-sm mt-6">
            <dt class="text-gray-500">Pelanggan</dt><dd><?= e($pesanan['nama_pelanggan'] ?: '-') ?></dd>
            <dt class="text-gray-500">No. HP</dt><dd><?= e($pesanan['no_hp'] ?: '-') ?></dd>
            <dt class="text-gray-500">Alamat</dt><dd><?= e($pesanan['alamat'] ?: '-') ?></dd>
            <dt class="text-gray-500">Metode</dt><dd><?= e($pesanan['metode_kirim']) ?> / <?= e(strtoupper((string) $pesanan['metode_bayar'])) ?></dd>
        </dl>

        <form action="<?= url('/admin/pesanan/' . $pesanan['nota_id'] . '/status') ?>" method="post" class="mt-6 flex items-end gap-3">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Ubah Status</label>
                <select name="status" class="border rounded-lg px-3 py-2 text-sm">
                    <?php foreach (['Menunggu Pembayaran','Diproses','Dikemas','Dikirim','Tiba','Diterima','Selesai','Dibatalkan'] as $s): ?>
                        <option value="<?= $s ?>" <?= $pesanan['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="bg-brand-600 text-white rounded-lg px-4 py-2 text-sm font-medium">Simpan</button>
        </form>
    </div>
</div>
