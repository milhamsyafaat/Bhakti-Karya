<?php
$isEdit = !empty($produk);
$action = $isEdit ? url('/admin/produk/' . $produk['id']) : url('/admin/produk');
$val = fn ($k, $d = '') => e($produk[$k] ?? $d);
?>
<div class="max-w-2xl">
    <a href="<?= url('/admin/produk') ?>" class="text-sm text-gray-500">&larr; Kembali</a>

    <form action="<?= $action ?>" method="post" enctype="multipart/form-data" class="bg-white border rounded-2xl p-6 mt-3 space-y-4">
        <?= csrf_field() ?>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Produk</label>
            <input type="text" name="nama_produk" required value="<?= $val('nama_produk') ?>"
                   class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori</label>
                <select name="kategori_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="0">— Pilih kategori —</option>
                    <?php foreach ($kategori as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= (int) ($produk['kategori_id'] ?? 0) === (int) $k['id'] ? 'selected' : '' ?>>
                            <?= e($k['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Badge</label>
                <select name="badge_status" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <?php foreach (['Standar', 'Terlaris', 'Komoditas', 'Promo'] as $b): ?>
                        <option value="<?= $b ?>" <?= ($produk['badge_status'] ?? 'Standar') === $b ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Harga</label>
                <input type="number" name="harga" value="<?= $val('harga', 0) ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Harga Coret</label>
                <input type="number" name="harga_lama" value="<?= $val('harga_lama', 0) ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Harga Reseller</label>
                <input type="number" name="harga_reseller" value="<?= $val('harga_reseller', 0) ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Stok</label>
                <input type="number" name="stok" value="<?= $val('stok', 0) ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Satuan</label>
                <input type="text" name="satuan" value="<?= $val('satuan', 'pcs') ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Min. Order</label>
                <input type="number" name="min_order" value="<?= $val('min_order', 1) ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Upload Gambar (jpg/png/webp/gif, maks 2MB)</label>
                <input type="file" name="gambar_file" accept="image/jpeg,image/png,image/webp,image/gif"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Atau URL Gambar</label>
                <input type="text" name="gambar" value="<?= $val('gambar') ?>" placeholder="https://..."
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <?php if ($isEdit && !empty($produk['gambar'])): ?>
            <div>
                <span class="block text-xs font-semibold text-gray-500 mb-1">Gambar saat ini</span>
                <img src="<?= product_image($produk['gambar']) ?>" onerror="<?= onerror_image() ?>" class="w-24 h-24 object-cover rounded-lg border" alt="">
            </div>
        <?php endif; ?>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"><?= $val('deskripsi') ?></textarea>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_favorit" value="1" <?= !empty($produk['is_favorit']) ? 'checked' : '' ?>>
            Jadikan produk favorit
        </label>

        <button class="bg-brand-600 text-white font-semibold px-5 py-2.5 rounded-lg">
            <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' ?>
        </button>
    </form>
</div>
