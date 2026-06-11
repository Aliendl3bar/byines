// Mobile nav toggle
(function() {
    const hamburger = document.getElementById('hamburgerBtn');
    const overlay = document.getElementById('mobileNavOverlay');
    const closeBtn = document.getElementById('mobileNavClose');

    if (hamburger && overlay) {
        function openNav() {
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeNav() {
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        }
        hamburger.addEventListener('click', openNav);
        if (closeBtn) closeBtn.addEventListener('click', closeNav);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeNav();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('open')) closeNav();
        });
    }
})();

// Dropdown toggle logic
(function() {
    const accountBtn = document.getElementById('accountBtn');
    const dropdown = document.getElementById('accountDropdown');
    if (accountBtn && dropdown) {
        accountBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        document.addEventListener('click', function() {
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        });
    }
})();

// Global function for quick adding to cart from grid views
function quickAddToCart(productId, btnElement) {
    const originalHTML = btnElement.innerHTML;
    btnElement.innerHTML = '<span class="material-symbols-outlined icon-md" style="color:#4CAF50;">check</span>';
    btnElement.style.pointerEvents = 'none';

    const formData = new FormData();
    formData.append('action', 'quick_add');
    formData.append('product_id', productId);
    formData.append('quantity', 1);

    fetch('cart_action.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const cartBadge = document.getElementById('cart-badge-count');
            if (cartBadge) {
                cartBadge.innerText = data.cartCount;
                cartBadge.style.display = data.cartCount > 0 ? 'flex' : 'none';
                cartBadge.style.transform = 'translate(25%, -25%) scale(1.3)';
                setTimeout(() => { cartBadge.style.transform = 'translate(25%, -25%) scale(1)'; }, 300);
            }
            setTimeout(() => {
                btnElement.innerHTML = originalHTML;
                btnElement.style.pointerEvents = 'auto';
            }, 2000);
        } else {
            alert(data.message || 'Error adding to cart.');
            btnElement.innerHTML = originalHTML;
            btnElement.style.pointerEvents = 'auto';
        }
    })
    .catch(err => {
        console.error(err);
        btnElement.innerHTML = originalHTML;
        btnElement.style.pointerEvents = 'auto';
    });
}
