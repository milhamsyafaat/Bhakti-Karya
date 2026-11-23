<div class="bg-white border rounded-2xl shadow-sm p-6">
    <h2 class="font-semibold text-gray-800 mb-1">Masuk / Daftar Pelanggan</h2>
    <p class="text-xs text-gray-500 mb-4">Gunakan nomor HP. Nomor baru akan otomatis terdaftar jika mengisi nama.</p>
    <form action="<?= url('/masuk') ?>" method="post" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Nomor HP</label>
            <input type="text" name="telepon" value="<?= old('telepon') ?>" required autofocus
                   placeholder="08xxxxxxxxxx"
                   class="w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Nama (untuk pendaftaran baru)</label>
            <input type="text" name="nama"
                   class="w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        </div>
        <button class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-lg hover:bg-brand-700">Masuk</button>
    </form>
</div>
