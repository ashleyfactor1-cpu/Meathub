import './bootstrap';

const cart = new Map();
const cartDialog = document.querySelector('#cart-dialog');
const cartItems = document.querySelector('#cart-items');
const cartCount = document.querySelector('#cart-count');
const cartTotal = document.querySelector('#cart-total');

function renderCart() {
    if (!cartDialog || !cartItems || !cartCount || !cartTotal) return;

    const items = [...cart.values()];
    const itemCount = items.reduce((total, item) => total + item.quantity, 0);

    cartCount.textContent = String(itemCount);
    cartCount.setAttribute('aria-label', `${itemCount} ${itemCount === 1 ? 'item' : 'items'} in cart`);
    cartTotal.textContent = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(items.reduce((total, item) => total + item.price * item.quantity, 0));
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
        price.textContent = new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(item.price * item.quantity);
        row.append(details, price);
        cartItems.append(row);
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

    if (target.closest('[data-close-cart]')) {
        cartDialog?.close();
    }
});

renderCart();
