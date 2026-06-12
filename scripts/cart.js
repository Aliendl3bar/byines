/** Cart page interactions. */

function updateCartItem(cartKey, newQuantity) {
    if (newQuantity < 1) return;

    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('cart_key', cartKey);
    formData.append('quantity', newQuantity);

    fetch('cart_action.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // update input value
            document.getElementById('qty_' + cartKey).value = newQuantity;
            // update totals
            updateCartSummary(data);
        } else {
            alert(data.message || 'Error updating cart.');
            // revert value
            location.reload();
        }
    })
    .catch(err => {
        console.error(err);
        alert('A network error occurred.');
    });
}

function increaseItemQty(cartKey) {
    const input = document.getElementById('qty_' + cartKey);
    const currentVal = parseInt(input.value);
    updateCartItem(cartKey, currentVal + 1);
}

function decreaseItemQty(cartKey) {
    const input = document.getElementById('qty_' + cartKey);
    const currentVal = parseInt(input.value);
    if (currentVal > 1) {
        updateCartItem(cartKey, currentVal - 1);
    }
}

function removeItem(cartKey) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('cart_key', cartKey);

    fetch('cart_action.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // remove dom element
            const itemEl = document.getElementById('item_' + cartKey);
            if (itemEl) itemEl.remove();

            // check if cart is empty
            if (document.querySelectorAll('.cart-item').length === 0) {
                document.getElementById('cartContent').style.display = 'none';
                document.getElementById('emptyCart').style.display = 'block';
                document.querySelector('.order-summary').style.display = 'none';
            }

            // update totals
            updateCartSummary(data);
        } else {
            alert(data.message || 'Error removing item.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('A network error occurred.');
    });
}

function updateCartSummary(data) {
    // update header badge
    const cartBadge = document.getElementById('cart-badge-count');
    if (cartBadge) {
        cartBadge.innerText = data.cartCount;
        cartBadge.style.display = data.cartCount > 0 ? 'flex' : 'none';
    }

    // update order summary sidebar
    const subtotalEl = document.getElementById('summary-subtotal');
    const taxEl = document.getElementById('summary-tax');
    const totalEl = document.getElementById('summary-total');
    const subtotalLabelEl = document.getElementById('summary-subtotal-label');

    if (subtotalEl) subtotalEl.innerText = '$' + data.subtotal;
    if (taxEl) taxEl.innerText = '$' + data.tax;
    if (totalEl) totalEl.innerText = '$' + data.total;
    if (subtotalLabelEl) subtotalLabelEl.innerText = `Subtotal (${data.cartCount} items)`;
}
