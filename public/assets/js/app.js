// Bhakti Karya — script global
document.addEventListener('DOMContentLoaded', function () {
    // Auto-hide flash message
    document.querySelectorAll('.flash').forEach(function (el) {
        setTimeout(function () { el.remove(); }, 4000);
    });
});
