<?php
/** Partial kartu produk. Variabel: $p (array produk) */
$badgeColor = match ($p['badge_status'] ?? 'Standar') {
    'Terlaris'   => 'bg-red-500',
    'Promo'      => 'bg-brand-600',
    'Komoditas'  => 'bg-green-600',
    default      => 'bg-gray-400',
};
$habis = (int) $p['stok'] <= 0;
?>
<a href="<?= url('/produk/' . $p['id']) ?>"
   class="group bg-white rounded-xl border hover:shadow-lg transition overflow-hidden flex flex-col">
    <div class="relative aspect-square bg-gray-100 overflow-hidden">
        <img src="<?= product_image($p['gambar'] ?? null) ?>" alt="<?= e($p['nama_produk']) ?>"
             loading="lazy" onerror="<?= onerror_image() ?>"
             class="w-full h-full object-cover group-hover:scale-105 transition">
        <span class="absolute top-2 left-2 <?= $badgeColor ?> text-white text-[10px] px-2 py-0.5 rounded-full">
            <?= e($p['badge_status']) ?>
        </span>
        <?php if ($habis): ?>
            <span class="absolute inset-0 bg-black/50 text-white text-sm flex items-center justify-center">Stok Habis</span>
        <?php endif; ?>
    </div>
    <div class="p-3 flex flex-col flex-1">
        <h3 class="text-sm font-medium text-gray-800 line-clamp-2 mb-1"><?= e($p['nama_produk']) ?></h3>
        <div class="mt-auto">
            <div class="flex items-baseline gap-2">
                <span class="font-bold text-brand-600"><?= rupiah($p['harga']) ?></span>
                <?php if (!empty($p['harga_lama']) && (float) $p['harga_lama'] > (float) $p['harga']): ?>
                    <span class="text-xs text-gray-400 line-through"><?= rupiah($p['harga_lama']) ?></span>
                <?php endif; ?>
            </div>
            <div class="text-[11px] text-gray-400 mt-0.5">Stok <?= (int) $p['stok'] ?> · <?= e($p['satuan']) ?></div>
        </div>
    </div>
</a>
