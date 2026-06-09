document.addEventListener('DOMContentLoaded', function () {
    var sortSelect = document.querySelector('[data-action="submit-sort"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            document.getElementById('sort-form').submit();
        });
    }

    var priceSlider = document.querySelector('[data-action="update-price-label"]');
    if (priceSlider) {
        priceSlider.addEventListener('input', function () {
            document.getElementById('price-val').innerText = this.value >= 500 ? '500+' : this.value;
        });

        priceSlider.addEventListener('change', function () {
            document.getElementById('filter-form').submit();
        });
    }

    document.querySelectorAll('[data-action="quick-add-cart"]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var productId = this.getAttribute('data-product-id');
            if (typeof quickAddToCart === 'function') {
                quickAddToCart(productId, this);
            }
        });
    });
});
