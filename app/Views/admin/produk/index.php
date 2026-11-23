<div class="flex items-center justify-between mb-4">
    <form method="get" class="flex gap-2">
        <input type="text" name="q" value="<?= e($opts['q']) ?>" placeholder="Cari produk..."
               class="border rounded-lg px-3 py-2 text-sm w-56">
        <select name="kategori_id" class="border rounded-lg px-3 py-2 text-sm">
            <option value="0">Semua kategori</option>
            <?php foreach ($kategori as $k): ?>
                <option value="<?= $k['id'] ?>" <?= (int) $opts['kategori_id'] === (int) $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm">Cari</button>
    </form>
    <a href="<?= url('/admin/produk/create') ?>" class="bg-brand-600 text-white rounded-lg px-4 py-2 text-sm font-medium">+ Tambah Produk</a>
</div>

<div class="bg-white border rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500">
            <tr>
                <th class="px-4 py-3">Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Badge</th>
                <th class="text-right px-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($produk as $p): ?>
            <tr class="border-t">
                <td class="px-4 py-2 flex items-center gap-2">
                    <img src="<?= product_image($p['gambar'] ?? null) ?>" onerror="<?= onerror_image() ?>" class="w-9 h-9 rounded object-cover bg-gray-100" alt="">
                    <span class="text-gray-800 line-clamp-1"><?= e($p['nama_produk']) ?></span>
                </td>
                <td class="text-gray-600"><?= e($p['kategori_id']) ?></td>
                <td><?= rupiah($p['harga']) ?></td>
                <td><?= (int) $p['stok'] ?></td>
                <td><span class="text-xs bg-gray-100 px-2 py-0.5 rounded-full"><?= e($p['badge_status']) ?></span></td>
                <td class="text-right px-4">
                    <a href="<?= url('/admin/produk/' . $p['id'] . '/edit') ?>" class="text-brand-600 text-xs">Edit</a>
                    <form action="<?= url('/admin/produk/' . $p['id'] . '/hapus') ?>" method="post" class="inline"
                          onsubmit="return confirm('Nonaktifkan produk ini?')">
                        <?= csrf_field() ?>
                        <button class="text-red-500 text-xs ml-2">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($produk)): ?>
            <tr><td colspan="6" class="text-center py-6 text-gray-400">Tidak ada produk.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<p class="text-xs text-gray-500 mt-3">Total <?= (int) $total ?> produk.</p>
