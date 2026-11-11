import { normalizeCart, readCart, writeCart, formatMoney } from './cart.js';

export const PENDING_CHECKOUT_KEY = 'ronan-tech-shop-pending-checkout-v1';

export function cartAfterPurchase(current, purchased) {
    const bought = new Map(normalizeCart(purchased).map(item => [item.id, item.quantity]));
    return normalizeCart(current).map(item => ({ id: item.id, quantity: Math.max(0, item.quantity - (bought.get(item.id) || 0)) })).filter(item => item.quantity > 0);
}

function pendingCheckout(storage) {
    const raw = (storage ?? globalThis.sessionStorage).getItem(PENDING_CHECKOUT_KEY);
    if (!raw) return null;
    const pending = JSON.parse(raw);
    if (!/^[a-f0-9]{64}$/.test(pending.key)) throw new Error('Checkout progress could not be read. Review your cart again.');
    return { key: pending.key, items: normalizeCart(pending.items), cleared: pending.cleared === true };
}

export function clearPurchasedCart(saved, cartStorage, pendingStorage) {
    try {
        const pending = pendingCheckout(pendingStorage);
        if (pending && pending.key === saved.checkout_key) {
            if (pending.cleared) return true;
            const progress = pendingStorage ?? globalThis.sessionStorage;
            const remaining = cartAfterPurchase(readCart(cartStorage), saved.items);
            // Record completion first, so failure to remove this receipt cannot subtract twice.
            progress.setItem(PENDING_CHECKOUT_KEY, JSON.stringify({ ...pending, cleared: true }));
            try { writeCart(remaining, cartStorage); } catch (error) {
                progress.setItem(PENDING_CHECKOUT_KEY, JSON.stringify(pending));
                throw error;
            }
            try { progress.removeItem(PENDING_CHECKOUT_KEY); } catch { /* The completion marker safely remains. */ }
            if (typeof document !== 'undefined') {
                const count = remaining.reduce((sum, item) => sum + item.quantity, 0);
                document.querySelectorAll('[data-cart-count]').forEach(element => { element.textContent = `(${count})`; });
            }
        }
        return true;
    } catch { return false; }
}

if (typeof document !== 'undefined') {
    const savedData = document.querySelector('#saved-order-data');
    if (savedData && !clearPurchasedCart(JSON.parse(savedData.textContent))) {
        document.querySelector('#cart-clear-warning').classList.remove('hidden');
    }
    const page = document.querySelector('[data-checkout-page]');
    if (page) {
        const form = document.querySelector('#checkout-form');
        const status = document.querySelector('#checkout-status');
        const saveButton = document.querySelector('#checkout-save');
        const reviewButton = document.querySelector('#checkout-review');
        let checkoutKey = null;
        let reviewed = false;
        let busy = false;

        function message(text, error = false) {
            status.textContent = text;
            status.setAttribute('role', error ? 'alert' : 'status');
        }

        function setBusy(value) {
            busy = value;
            page.setAttribute('aria-busy', String(value));
            saveButton.disabled = value || !reviewed;
            reviewButton.disabled = value;
            saveButton.textContent = value ? 'Please wait...' : 'Place demo order';
        }

        function renderQuote(quote) {
            const list = document.querySelector('#checkout-items');
            list.replaceChildren();
            for (const item of quote.items) {
                const row = document.querySelector('#checkout-item-template').content.cloneNode(true);
                row.querySelector('[data-line-name]').textContent = item.name;
                row.querySelector('[data-line-details]').textContent = `${item.quantity} × ${item.price_centavos === null ? 'unavailable' : formatMoney(item.price_centavos)}`;
                row.querySelector('[data-line-total]').textContent = item.line_total_centavos === null ? 'Review cart' : formatMoney(item.line_total_centavos);
                row.querySelector('[data-line-stock]').textContent = `${item.stock} available`;
                row.querySelector('[data-line-issue]').textContent = item.issue;
                list.append(row);
            }
            document.querySelector('#checkout-subtotal').textContent = quote.subtotal_centavos === null ? 'Review cart' : formatMoney(quote.subtotal_centavos);
            document.querySelector('#checkout-total').textContent = quote.total_centavos === null ? 'Review cart' : formatMoney(quote.total_centavos);
        }

        async function request(body) {
            try {
                const response = await fetch('/api/checkout.php', {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(body), signal: AbortSignal.timeout(20000),
                });
                return { ok: response.ok, data: await response.json() };
            } catch {
                throw new Error('We could not confirm the save. Your cart is unchanged. Retry this checkout to check for a saved order.');
            }
        }

        function finish(saved) {
            reviewed = false;
            clearPurchasedCart(saved);
            window.location.assign(saved.confirmation_url);
        }

        function showChanges(changes = []) {
            const list = document.querySelector('#checkout-changes');
            list.replaceChildren();
            for (const text of changes) {
                const item = document.createElement('li');
                item.textContent = text;
                list.append(item);
            }
        }

        async function review(ignorePending = false) {
            if (busy) return;
            reviewed = false;
            setBusy(true);
            message('Checking current prices and stock...');
            let quoteRendered = false;
            try {
                const progress = ignorePending ? null : pendingCheckout();
                const pending = progress?.cleared ? null : progress;
                const items = readCart();
                if (!items.length && !pending) {
                    renderQuote({ items: [], subtotal_centavos: 0, total_centavos: 0 });
                    quoteRendered = true;
                    message('Your cart is empty. Add a product before checkout.', true);
                    reviewButton.classList.add('hidden');
                    return;
                }
                const result = await request({ action: 'review', items, retry_key: pending?.key ?? null });
                if (result.data.saved) { finish(result.data); return; }
                if (result.data.quote) { renderQuote(result.data.quote); quoteRendered = true; }
                if (!result.ok) throw new Error(result.data.error || 'Some products cannot be ordered. Return to your cart to update them.');
                checkoutKey = result.data.checkout_key;
                reviewed = result.data.quote.valid;
                if (ignorePending) sessionStorage.removeItem(PENDING_CHECKOUT_KEY);
                showChanges();
                message('Current prices and stock are shown below. Check the order and enter your delivery details.');
                reviewButton.classList.add('hidden');
            } catch (error) {
                if (!quoteRendered) {
                    document.querySelector('#checkout-subtotal').textContent = 'Unavailable';
                    document.querySelector('#checkout-total').textContent = 'Unavailable';
                }
                message(error.message, true);
                reviewButton.classList.remove('hidden');
            } finally { setBusy(false); }
        }

        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (busy || !reviewed) return;
            setBusy(true);
            form.querySelectorAll('[data-field-error]').forEach(element => { element.textContent = ''; });
            form.querySelectorAll('[aria-invalid]').forEach(element => element.removeAttribute('aria-invalid'));
            message('Saving your demo order. Please wait...');
            try {
                const items = readCart();
                // Per-tab progress contains only the retry key and cart IDs/quantities, never customer details.
                sessionStorage.setItem(PENDING_CHECKOUT_KEY, JSON.stringify({ key: checkoutKey, items }));
                const result = await request({ action: 'save', checkout_key: checkoutKey, items, customer: Object.fromEntries(new FormData(form)) });
                if (result.data.saved) { finish(result.data); return; }
                if (result.data.quote) renderQuote(result.data.quote);
                showChanges(result.data.changes);
                for (const [field, text] of Object.entries(result.data.errors || {})) {
                    form.querySelector(`[data-field-error="${field}"]`).textContent = text;
                    form.elements[field].setAttribute('aria-invalid', 'true');
                }
                if (result.data.needs_review) {
                    reviewed = false;
                    reviewButton.classList.remove('hidden');
                }
                message(result.data.error || 'The order was not saved. Your cart is unchanged.', true);
                const firstInvalid = form.querySelector('[aria-invalid="true"]');
                (firstInvalid || status).focus();
            } catch (error) {
                message(error.message, true);
            } finally { setBusy(false); }
        });
        reviewButton.addEventListener('click', () => review(true));
        window.addEventListener('storage', event => {
            if (event.key === 'ronan-tech-shop-cart-v1' || event.key === null) {
                reviewed = false;
                saveButton.disabled = true;
                reviewButton.classList.remove('hidden');
                message('Your cart changed in another tab. Review the current order before saving.', true);
            }
        });
        review();
    }
}
