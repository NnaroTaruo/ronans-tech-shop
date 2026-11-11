// Native forms still work without JavaScript; prevent accidental repeat clicks while navigating.
document.querySelectorAll('form[method="post"]').forEach(form => {
    form.addEventListener('submit', event => {
        if (form.dataset.saving === 'true') { event.preventDefault(); return; }
        form.dataset.saving = 'true';
        form.querySelectorAll('button[type="submit"]').forEach(button => {
            button.disabled = true;
            button.textContent = 'Saving…';
        });
    });
});
window.addEventListener('pageshow', event => { if (event.persisted) window.location.reload(); });
const menu = document.querySelector('.admin-menu');
const compact = window.matchMedia('(max-width: 900px)');
function foldNavigation() {
    if (menu) menu.open = !compact.matches;
    document.querySelectorAll('.admin-filters').forEach(filters => { filters.open = !compact.matches; });
}
foldNavigation();
compact.addEventListener('change', foldNavigation);
