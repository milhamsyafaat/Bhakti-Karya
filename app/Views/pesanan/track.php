<div class="max-w-md mx-auto px-4 py-16">
    <h1 class="text-2xl font-bold text-gray-900 mb-2 text-center">Lacak Pesanan</h1>
    <p class="text-sm text-gray-500 text-center mb-6">Masukkan nomor nota untuk melihat status pesanan.</p>

    <form action="<?= url('/lacak') ?>" method="get" class="bg-white border rounded-2xl p-6 space-y-4">
        <input type="text" name="nota" placeholder="mis. WBK-20260101-A1B2"
               class="w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        <button class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-lg">Lacak</button>
    </form>
</div>
