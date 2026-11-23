<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Filter -->
        <aside class="md:w-52 shrink-0">
            <form method="get" class="bg-white border rounded-xl p-4 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Cari</label>
                    <input type="text" name="q" value="<?= e($opts['q']) ?>"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori</label>
                    <select name="kategori_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="0">Semua kategori</option>
                        <?php foreach ($kategori as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= (int) $opts['kategori_id'] === (int) $k['id'] ? 'selected' : '' ?>>
                                <?= e($k['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Urutkan</label>
                    <select name="sort" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <?php foreach (['terbaru' => 'Terbaru', 'termurah' => 'Termurah', 'termahal' => 'Termahal', 'terlaris' => 'Terlaris'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= $opts['sort'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="w-full bg-brand-600 text-white rounded-lg py-2 text-sm font-medium">Terapkan</button>
            </form>
        </aside>

        <!-- Grid -->
        <div class="flex-1">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-500"><?= (int) $total ?> produk ditemukan</p>
            </div>

            <?php if (empty($produk)): ?>
                <div class="bg-white border rounded-xl p-10 text-center text-gray-500">
                    Produk tidak ditemukan. Coba ubah kata kunci atau filter.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($produk as $p): ?>
                        <?php require base_path('app/Views/partials/produk-card.php'); ?>
                    <?php endforeach; ?>
                </div>

                <?php
                $pages = (int) ceil($total / 24);
                $current = max(1, (int) floor($opts['offset'] / 24) + 1);
                ?>
                <?php if ($pages > 1): ?>
                    <div class="flex justify-center gap-2 mt-8">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a href="<?= url('/produk?' . http_build_query(['q' => $opts['q'], 'kategori_id' => $opts['kategori_id'], 'sort' => $opts['sort'], 'page' => $i])) ?>"
                               class="px-3 py-1.5 text-sm rounded-lg border <?= $i === $current ? 'bg-brand-600 text-white border-brand-600' : 'bg-white hover:border-brand-500' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
