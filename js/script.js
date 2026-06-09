document.addEventListener('DOMContentLoaded', function() {
    var dropdown = document.getElementById('accountDropdown');
    var accountBtn = document.getElementById('accountBtn');
    if (dropdown && accountBtn) {
        accountBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
    }
});

document.addEventListener('click', function() {
    var dropdown = document.getElementById('accountDropdown');
    if (dropdown && dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
    }
});

function quickAddToCart(productId, btnElement) {
    var originalHTML = btnElement.innerHTML;
    btnElement.innerHTML = '<span class="material-symbols-outlined icon-md" style="color:#4CAF50;">check</span>';
    btnElement.style.pointerEvents = 'none';

    var formData = new FormData();
    formData.append('action', 'quick_add');
    formData.append('product_id', productId);
    formData.append('quantity', 1);

    fetch('cart_action.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            var cartBadge = document.getElementById('cart-badge-count');
            if (cartBadge) {
                cartBadge.innerText = data.cartCount;
                cartBadge.style.display = data.cartCount > 0 ? 'flex' : 'none';
                cartBadge.style.transform = 'translate(25%, -25%) scale(1.3)';
                setTimeout(function() { cartBadge.style.transform = 'translate(25%, -25%) scale(1)'; }, 300);
            }
            setTimeout(function() {
                btnElement.innerHTML = originalHTML;
                btnElement.style.pointerEvents = 'auto';
            }, 2000);
        } else {
            alert(data.message || 'Error adding to cart.');
            btnElement.innerHTML = originalHTML;
            btnElement.style.pointerEvents = 'auto';
        }
    })
    .catch(function(err) {
        console.error(err);
        btnElement.innerHTML = originalHTML;
        btnElement.style.pointerEvents = 'auto';
    });
}
