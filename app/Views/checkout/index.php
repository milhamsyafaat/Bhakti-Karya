<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

    <form action="<?= url('/checkout') ?>" method="post" id="checkout-form" class="grid md:grid-cols-3 gap-6">
        <?= csrf_field() ?>
        <!-- Data pengiriman -->
        <div class="md:col-span-2 bg-white border rounded-2xl p-6 space-y-4">
            <h2 class="font-semibold text-gray-800">Data Pengiriman</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nama</label>
                    <input type="text" name="nama_pelanggan" required class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">No. HP</label>
                    <input type="text" name="no_hp" required class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Alamat</label>
                <textarea name="alamat" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2">Metode Pengiriman</label>
                <div class="flex gap-3">
                    <label class="flex-1 border rounded-lg p-3 cursor-pointer text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                        <input type="radio" name="metode_kirim" value="antar" checked class="mr-1"> Antar ke alamat
                    </label>
                    <label class="flex-1 border rounded-lg p-3 cursor-pointer text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                        <input type="radio" name="metode_kirim" value="ambil" class="mr-1"> Ambil di toko
                    </label>
                </div>
            </div>

            <div id="zona-wrap">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Kelurahan / Zona Ongkir</label>
                <select name="zona_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <?php foreach ($zona as $z): ?>
                        <option value="<?= e($z['id']) ?>"><?= e($z['kelurahan']) ?> — <?= rupiah($z['tarif']) ?> (<?= e($z['jarak_km']) ?> km)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Jadwal (opsional)</label>
                    <input type="text" name="jadwal" placeholder="mis. Besok pagi 08.00-11.00" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan (opsional)</label>
                    <input type="text" name="catatan" placeholder="mis. titip di pos satpam" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2">Metode Pembayaran</label>
                <div class="flex gap-3">
                    <?php foreach (['qris' => 'QRIS', 'cod' => 'COD', 'transfer' => 'Transfer'] as $val => $lbl): ?>
                        <label class="flex-1 border rounded-lg p-3 cursor-pointer text-center text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                            <input type="radio" name="metode_bayar" value="<?= $val ?>" <?= $val === 'qris' ? 'checked' : '' ?> class="mr-1"> <?= $lbl ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="bg-white border rounded-2xl p-6 h-fit sticky top-20">
            <h2 class="font-semibold text-gray-800 mb-4">Ringkasan</h2>
            <div class="space-y-2 text-sm">
                <?php foreach ($lines as $line): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600 truncate pr-2"><?= e($line['produk']['nama_produk']) ?> ×<?= (int) $line['qty'] ?></span>
                        <span class="text-gray-800 whitespace-nowrap"><?= rupiah($line['subtotal']) ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="border-t pt-2 flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium"><?= rupiah($subtotal) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Ongkir</span>
                    <span id="ongkir-value" class="font-medium text-brand-600">—</span>
                </div>
                <div class="border-t pt-2 flex justify-between text-base">
                    <span class="font-semibold">Total</span>
                    <span id="total-value" class="font-bold text-gray-900"><?= rupiah($subtotal) ?></span>
                </div>
            </div>
            <button class="w-full mt-5 bg-brand-600 text-white font-semibold py-3 rounded-lg hover:bg-brand-700">
                Buat Pesanan
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const subtotal = <?= (int) $subtotal ?>;
    const form = document.getElementById('checkout-form');
    const ongkirEl = document.getElementById('ongkir-value');
    const totalEl = document.getElementById('total-value');
    const zonaWrap = document.getElementById('zona-wrap');

    const rupiah = (n) => 'Rp' + Number(n).toLocaleString('id-ID');

    async function refresh() {
        const metode = form.querySelector('input[name="metode_kirim"]:checked').value;
        const zona = form.querySelector('select[name="zona_id"]')?.value || '';
        zonaWrap.style.display = metode === 'antar' ? '' : 'none';

        const res = await fetch(`<?= url('/ongkir') ?>?metode_kirim=${metode}&zona_id=${zona}`);
        const data = await res.json();
        ongkirEl.textContent = data.gratis ? 'GRATIS' : data.ongkir_rp;
        totalEl.textContent = rupiah(subtotal + Number(data.ongkir));
    }

    form.querySelectorAll('input[name="metode_kirim"]').forEach(r => r.addEventListener('change', refresh));
    form.querySelector('select[name="zona_id"]')?.addEventListener('change', refresh);
    refresh();
})();
</script>
