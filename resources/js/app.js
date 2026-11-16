import './bootstrap';

const cart = new Map();
const cartSessionKey = 'meathub-cart';
const cartDialog = document.querySelector('#cart-dialog');
const cartItems = document.querySelector('#cart-items');
const cartCount = document.querySelector('#cart-count');
const cartTotal = document.querySelector('#cart-total');
const checkoutDialog = document.querySelector('#checkout-dialog');
const checkoutForm = document.querySelector('#checkout-form');
const checkoutItems = document.querySelector('#checkout-items');
const checkoutTotal = document.querySelector('#checkout-total');
const checkoutError = document.querySelector('#checkout-error');
const checkoutButton = document.querySelector('[data-checkout]');

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
}

function renderCart() {
    if (!cartDialog || !cartItems || !cartCount || !cartTotal || !(checkoutButton instanceof HTMLButtonElement)) return;

    sessionStorage.setItem(cartSessionKey, JSON.stringify([...cart.entries()]));

    const items = [...cart.values()];
    const itemCount = items.reduce((total, item) => total + item.quantity, 0);

    cartCount.textContent = String(itemCount);
    cartCount.setAttribute('aria-label', `${itemCount} ${itemCount === 1 ? 'item' : 'items'} in cart`);
    cartTotal.textContent = formatCurrency(items.reduce((total, item) => total + item.price * item.quantity, 0));
    checkoutButton.disabled = itemCount === 0;
    cartItems.replaceChildren();

    if (items.length === 0) {
        const emptyMessage = document.createElement('p');
        emptyMessage.className = 'py-8 text-center text-sm text-[#82766e]';
        emptyMessage.textContent = 'Your cart is waiting for something good.';
        cartItems.append(emptyMessage);
        return;
    }

    for (const item of items) {
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between gap-4 rounded-xl border border-[#ece3d9] bg-white p-3';

        const details = document.createElement('div');
        details.className = 'min-w-0';
        const name = document.createElement('p');
        name.className = 'truncate text-sm font-semibold text-[#1f2937]';
        name.textContent = item.name;
        const producer = document.createElement('p');
        producer.className = 'mt-0.5 truncate text-xs text-[#82766e]';
        producer.textContent = `${item.producer} · ${item.quantity} kg`;
        details.append(name, producer);

        const price = document.createElement('p');
        price.className = 'shrink-0 text-sm font-semibold text-[#14532d]';
        price.textContent = formatCurrency(item.price * item.quantity);
        row.append(details, price);
        cartItems.append(row);
    }
}

function prepareCheckout() {
    if (!(checkoutItems instanceof HTMLElement) || !(checkoutTotal instanceof HTMLElement)) return;

    checkoutItems.replaceChildren();
    let index = 0;
    for (const [productId, item] of cart) {
        for (const [name, value] of Object.entries({
            product_id: productId,
            quantity_kg: item.quantity,
        })) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `items[${index}][${name}]`;
            input.value = String(value);
            checkoutItems.append(input);
        }
        index += 1;
    }

    checkoutTotal.textContent = formatCurrency([...cart.values()].reduce(
        (total, item) => total + item.price * item.quantity,
        0,
    ));
}

function restoreCart() {
    const storedCart = sessionStorage.getItem(cartSessionKey);
    if (!storedCart) return;

    let entries;
    try {
        entries = JSON.parse(storedCart);
    } catch {
        sessionStorage.removeItem(cartSessionKey);
        return;
    }

    if (!Array.isArray(entries)) {
        sessionStorage.removeItem(cartSessionKey);
        return;
    }

    for (const entry of entries) {
        if (!Array.isArray(entry) || entry.length !== 2) continue;

        const [productId, item] = entry;
        if (
            typeof productId !== 'string'
            || !/^\d+$/.test(productId)
            || !item
            || typeof item.name !== 'string'
            || typeof item.producer !== 'string'
            || !Number.isFinite(item.price)
            || !Number.isInteger(item.quantity)
            || item.quantity < 1
        ) continue;

        cart.set(productId, item);
    }
}

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;

    const addButton = target.closest('[data-add-to-cart]');
    if (addButton instanceof HTMLButtonElement) {
        const id = addButton.dataset.productId;
        const price = Number(addButton.dataset.productPrice);
        const stock = Number(addButton.dataset.productStock);
        if (!id || !Number.isFinite(price) || !Number.isFinite(stock)) return;

        const item = cart.get(id) ?? {
            name: addButton.dataset.productName ?? 'MeatHub product',
            producer: addButton.dataset.producerName ?? 'Local producer',
            price,
            quantity: 0,
        };
        if (item.quantity >= stock) {
            addButton.textContent = 'Stock limit reached';
            window.setTimeout(() => {
                addButton.textContent = '＋ Add to Cart';
            }, 1500);
            return;
        }

        item.quantity += 1;
        cart.set(id, item);
        renderCart();
        return;
    }

    if (target.closest('#cart-toggle')) {
        cartDialog?.showModal();
        return;
    }

    if (target.closest('[data-checkout]')) {
        if (cart.size === 0) return;

        if (checkoutButton instanceof HTMLButtonElement && checkoutButton.dataset.authenticated !== 'true') {
            sessionStorage.setItem(cartSessionKey, JSON.stringify([...cart.entries()]));
            window.location.assign(checkoutButton.dataset.checkoutUrl ?? '/checkout');
            return;
        }

        prepareCheckout();
        cartDialog?.close();
        checkoutDialog?.showModal();
        return;
    }

    if (target.closest('[data-back-to-cart]')) {
        checkoutDialog?.close();
        cartDialog?.showModal();
        return;
    }

    if (target.closest('[data-close-checkout]')) {
        checkoutDialog?.close();
        return;
    }

    if (target.closest('[data-close-cart]')) {
        cartDialog?.close();
    }
});

checkoutForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!(checkoutForm instanceof HTMLFormElement) || !(checkoutError instanceof HTMLElement)) return;
    if (checkoutForm.dataset.qrConfigured !== 'true') return;

    checkoutError.classList.add('hidden');
    checkoutError.textContent = '';
    const submitButton = checkoutForm.querySelector('#submit-order');
    if (submitButton instanceof HTMLButtonElement) submitButton.disabled = true;

    try {
        const response = await fetch(checkoutForm.action, {
            method: 'POST',
            body: new FormData(checkoutForm),
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const result = await response.json();

        if (!response.ok) {
            const messages = Object.values(result.errors ?? {}).flat();
            checkoutError.textContent = messages.join(' ') || 'We could not place the order. Please try again.';
            checkoutError.classList.remove('hidden');
            return;
        }

        cart.clear();
        sessionStorage.removeItem(cartSessionKey);
        window.location.assign(result.redirect_url);
    } catch {
        checkoutError.textContent = 'We could not connect to place the order. Please try again.';
        checkoutError.classList.remove('hidden');
    } finally {
        if (submitButton instanceof HTMLButtonElement) submitButton.disabled = false;
    }
});

restoreCart();
renderCart();

const checkoutReturnUrl = new URL(window.location.href);
if (checkoutReturnUrl.searchParams.get('checkout') === '1') {
    checkoutReturnUrl.searchParams.delete('checkout');
    window.history.replaceState({}, '', checkoutReturnUrl);

    if (cart.size > 0) {
        prepareCheckout();
        checkoutDialog?.showModal();
    }
}
