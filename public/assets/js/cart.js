export const CART_KEY = 'ronan-tech-shop-cart-v1';
const MAX_ID_OR_QUANTITY = 4294967295;

export function normalizeCart(items) {
    if (!Array.isArray(items) || items.length > 100) throw new Error('Your saved cart is invalid. Clear it to start again.');
    const merged = new Map();
    for (const item of items) {
        if (!item || !Number.isInteger(item.id) || item.id < 1 || item.id > MAX_ID_OR_QUANTITY
            || !Number.isInteger(item.quantity) || item.quantity < 1 || item.quantity > MAX_ID_OR_QUANTITY) {
            throw new Error('Your saved cart contains an invalid product or quantity. Clear it to start again.');
        }
        const quantity = (merged.get(item.id) || 0) + item.quantity;
        if (quantity > MAX_ID_OR_QUANTITY) throw new Error('The combined quantity is too large.');
        merged.set(item.id, quantity);
    }
    // Persist only IDs and quantities. Prices and stock always come from PHP.
    return [...merged].map(([id, quantity]) => ({ id, quantity }));
}

export function readCart(storage) {
    try {
        return normalizeCart(JSON.parse((storage ?? globalThis.localStorage).getItem(CART_KEY) || '[]'));
    } catch {
        throw new Error('Your saved cart could not be read. Open the cart and clear it to start again, or check your browser storage settings.');
    }
}

export function writeCart(items, storage) {
    const cart = normalizeCart(items);
    try {
        (storage ?? globalThis.localStorage).setItem(CART_KEY, JSON.stringify(cart));
    } catch {
        throw new Error('Your browser could not save the cart. Check site storage settings and try again.');
    }
    return cart;
}

export function addItem(cart, id, quantity) {
    cart = normalizeCart(cart);
    normalizeCart([{ id, quantity }]);
    const existing = cart.find(item => item.id === id);
    if (existing) return normalizeCart(cart.map(item => item.id === id ? { id, quantity: item.quantity + quantity } : item));
    return normalizeCart([...cart, { id, quantity }]);
}

export function formatMoney(centavos) {
    if (!Number.isSafeInteger(centavos) || centavos < 0) throw new Error('The price could not be displayed safely.');
    return `PHP ${Math.floor(centavos / 100).toLocaleString('en-PH')}.${String(centavos % 100).padStart(2, '0')}`;
}

if (typeof document !== 'undefined') {
    const cartPage = document.querySelector('[data-cart-page]');
    const feedback = document.querySelector('#cart-feedback');
    const state = document.querySelector('#cart-state');
    const content = document.querySelector('#cart-content');
    const clearButton = document.querySelector('#cart-clear');
    const retryButton = document.querySelector('#cart-retry');
    const checkoutButton = document.querySelector('#cart-checkout');
    let cartValid = false;
    let busy = false;
    let quoteSequence = 0;
    const show = (element, visible) => element?.classList.toggle('hidden', !visible);

    function announce(message, error = false) {
        feedback.replaceChildren();
        const paragraph = document.createElement('p');
        paragraph.className = 'notice';
        paragraph.textContent = message;
        if (!error) {
            const link = document.createElement('a');
            link.href = '/cart.php';
            link.className = 'text-link ml-3';
            link.textContent = 'View cart';
            paragraph.append(link);
        }
        feedback.append(paragraph);
        feedback.setAttribute('role', error ? 'alert' : 'status');
        show(feedback, true);
    }

    function updateCount() {
        const count = readCart().reduce((sum, item) => sum + item.quantity, 0);
        document.querySelectorAll('[data-cart-count]').forEach(element => { element.textContent = `(${count})`; });
    }

    function setBusy(value) {
        busy = value;
        document.querySelectorAll('[data-enable-cart], .cart-update button, [data-item-remove], #cart-clear, #cart-retry')
            .forEach(button => { button.disabled = value; });
        cartPage?.setAttribute('aria-busy', String(value));
        if (checkoutButton) checkoutButton.disabled = value || !cartValid;
    }

    async function quote(items) {
        let response;
        try {
            response = await fetch('/api/cart.php', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ items }), signal: AbortSignal.timeout(15000),
            });
        } catch {
            throw new Error('We could not check current prices and stock. Try again. Your saved cart is unchanged.');
        }
        let data;
        try { data = await response.json(); } catch { throw new Error('The cart could not be checked. Try again shortly. Your saved cart is unchanged.'); }
        if (!response.ok) throw new Error(data.error || 'The cart could not be checked. Try again.');
        return data;
    }

    function renderCart(data) {
        cartValid = data.valid;
        checkoutButton.disabled = busy || !cartValid;
        const list = document.querySelector('#cart-items');
        list.replaceChildren();
        show(content, data.items.length > 0);
        content.classList.toggle('grid', data.items.length > 0);
        show(clearButton, data.items.length > 0);
        show(retryButton, data.items.length > 0);
        show(state, !data.valid);
        state.textContent = data.items.length ? 'Update or remove the products marked below before continuing.' : 'Your cart is empty. Browse products to find something for your desk.';
        for (const item of data.items) {
            const row = document.querySelector('#cart-item-template').content.cloneNode(true);
            row.querySelector('[data-item-name]').textContent = item.name;
            row.querySelector('[data-item-category]').textContent = item.category_name;
            row.querySelector('[data-item-price]').textContent = item.price_centavos === null ? 'Price unavailable' : `${formatMoney(item.price_centavos)} each`;
            row.querySelector('[data-item-stock]').textContent = item.stock > 0 ? `${item.stock} available` : 'Unavailable';
            row.querySelector('[data-item-total]').textContent = item.line_total_centavos === null ? 'Review quantity' : formatMoney(item.line_total_centavos);
            row.querySelector('[data-item-issue]').textContent = item.issue;
            const image = row.querySelector('img');
            image.src = item.image_path || '/assets/images/accessories.svg';
            image.alt = item.image_path ? `${item.category_name} demo illustration` : '';
            const form = row.querySelector('form');
            const itemFeedback = row.querySelector('[data-item-issue]');
            const input = form.elements.quantity;
            input.value = item.quantity;
            input.setAttribute('aria-label', `Quantity for ${item.name}`);
            if (item.stock > 0) input.max = item.stock;
            input.disabled = item.stock === 0;
            form.querySelector('button[type="submit"]').disabled = item.stock === 0;
            if (item.stock === 0) form.querySelector('button[type="submit"]').setAttribute('data-unavailable', '');
            form.addEventListener('submit', event => {
                event.preventDefault();
                changeCart(async () => {
                    const cart = readCart();
                    if (!cart.some(entry => entry.id === item.id)) throw new Error('This product was already removed. Refresh the cart.');
                    const candidate = normalizeCart(cart.map(entry => entry.id === item.id ? { id: item.id, quantity: Number(input.value) } : entry));
                    const checked = await quote(candidate);
                    const changed = checked.items.find(entry => entry.id === item.id);
                    if (changed.issue) throw new Error(changed.issue);
                    writeCart(candidate);
                    renderCart(checked);
                }, 'Cart quantity updated.', itemFeedback);
            });
            const remove = row.querySelector('[data-item-remove]');
            remove.setAttribute('aria-label', `Remove ${item.name}`);
            remove.addEventListener('click', () => changeCart(async () => {
                writeCart(readCart().filter(entry => entry.id !== item.id));
                await loadCart();
            }, 'Product removed from your cart.'));
            list.append(row);
        }
        document.querySelector('#cart-subtotal').textContent = data.subtotal_centavos === null ? 'Review items' : formatMoney(data.subtotal_centavos);
        document.querySelector('#cart-total').textContent = data.total_centavos === null ? 'Review items' : formatMoney(data.total_centavos);
    }

    async function loadCart() {
        cartValid = false;
        checkoutButton.disabled = true;
        const sequence = ++quoteSequence;
        show(state, true);
        state.textContent = 'Checking current prices and stock...';
        try {
            const cart = readCart();
            updateCount();
            const checked = cart.length ? await quote(cart) : { items: [], valid: false, subtotal_centavos: 0, total_centavos: 0 };
            if (sequence === quoteSequence) renderCart(checked);
        } catch (error) {
            if (sequence !== quoteSequence) return;
            show(content, false);
            state.textContent = error.message;
            show(clearButton, true);
            show(retryButton, true);
        }
    }

    async function changeCart(action, success, localFeedback = null) {
        if (busy) return;
        setBusy(true);
        try {
            await action();
            updateCount();
            announce(success);
            if (localFeedback) localFeedback.textContent = success;
        } catch (error) {
            announce(error.message, true);
            if (localFeedback) localFeedback.textContent = error.message;
        } finally {
            setBusy(false);
            // Out-of-stock controls must stay disabled after a different action completes.
            document.querySelectorAll('[data-unavailable]').forEach(button => { button.disabled = true; });
        }
    }

    document.querySelectorAll('[data-enable-cart]').forEach(button => { button.disabled = false; });
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        const localFeedback = document.createElement('p');
        localFeedback.className = 'mt-3 text-sm';
        form.append(localFeedback);
        form.addEventListener('submit', event => {
            event.preventDefault();
            if (!form.reportValidity()) return;
            const button = form.querySelector('button');
            if (!button.hasAttribute('data-enable-cart')) return;
            changeCart(async () => {
                button.textContent = 'Adding...';
                try {
                    const id = Number(form.elements.id.value);
                    const candidate = addItem(readCart(), id, Number(form.elements.quantity.value));
                    const checked = await quote(candidate);
                    const added = checked.items.find(item => item.id === id);
                    if (added.issue) throw new Error(added.issue);
                    writeCart(candidate);
                } finally { button.textContent = 'Add to cart'; }
            }, `${form.dataset.productName} added to your cart.`, localFeedback);
        });
    });
    clearButton?.addEventListener('click', () => changeCart(async () => {
        writeCart([]);
        await loadCart();
    }, 'Cart cleared.'));
    retryButton?.addEventListener('click', () => { if (!busy) loadCart(); });
    checkoutButton?.addEventListener('click', () => { if (!busy && cartValid) window.location.assign('/checkout.php'); });
    window.addEventListener('storage', event => {
        if (event.key === CART_KEY || event.key === null) {
            try { updateCount(); } catch (error) { announce(error.message, true); }
            if (cartPage) loadCart();
        }
    });
    try { updateCount(); } catch (error) { announce(error.message, true); }
    if (cartPage) loadCart();
}
