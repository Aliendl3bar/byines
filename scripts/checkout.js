document.addEventListener('DOMContentLoaded', function() {
    calculateShipping();

    document.addEventListener('click', function(e) {
        var target = e.target.closest('[data-action]');
        if (!target) return;
        var action = target.getAttribute('data-action');

        if (action === 'select-payment') {
            selectPayment(target.getAttribute('data-method'));
        } else if (action === 'process-order') {
            processOrder();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.getAttribute('data-action') === 'calculate-shipping') {
            calculateShipping();
        }
    });
});

function calculateShipping() {
    var checkoutMain = document.getElementById('checkout-main');
    var subtotal = parseFloat(checkoutMain ? checkoutMain.getAttribute('data-subtotal') : 0);
    var tax = parseFloat(checkoutMain ? checkoutMain.getAttribute('data-tax') : 0);

    var cityInput = document.getElementById('city');
    var city = cityInput ? cityInput.value.trim().toLowerCase() : '';
    var shippingCost = 0;
    var shippingDisplay = '';

    if (city === '') {
        shippingDisplay = 'Enter city...';
    } else if (city === 'tangier' || city === 'tanger') {
        shippingCost = 0;
        shippingDisplay = 'FREE';
    } else {
        shippingCost = 3.00;
        shippingDisplay = '$3.00';
    }

    var shippingEl = document.getElementById('summary-shipping');
    if (shippingEl) {
        shippingEl.innerText = shippingDisplay;
        shippingEl.style.color = shippingCost === 0 && city !== '' ? '#4CAF50' : 'var(--brand-dark)';
    }

    var totalEl = document.getElementById('summary-total');
    if (totalEl) {
        totalEl.innerText = (subtotal + tax + shippingCost).toFixed(2);
    }
}

function selectPayment(method) {
    var cod = document.getElementById('payment_cod');
    var paypal = document.getElementById('payment_paypal');
    var codWrapper = document.getElementById('payment_cod_wrapper');
    var paypalWrapper = document.getElementById('payment_paypal_wrapper');

    if (cod) cod.checked = (method === 'cod');
    if (paypal) paypal.checked = (method === 'paypal');
    if (codWrapper) codWrapper.classList.toggle('selected', method === 'cod');
    if (paypalWrapper) paypalWrapper.classList.toggle('selected', method === 'paypal');
}

function processOrder() {
    var firstName = document.getElementById('first_name');
    var lastName = document.getElementById('last_name');
    var email = document.getElementById('email');
    var phone = document.getElementById('phone');
    var address = document.getElementById('address');
    var city = document.getElementById('city');

    var fv = firstName ? firstName.value.trim() : '';
    var lv = lastName ? lastName.value.trim() : '';
    var ev = email ? email.value.trim() : '';
    var pv = phone ? phone.value.trim() : '';
    var av = address ? address.value.trim() : '';
    var cv = city ? city.value.trim() : '';
    var pm = document.querySelector('input[name="payment_method"]:checked');

    if (!fv || !lv || !ev || !pv || !av || !cv) {
        alert("Please fill out all required shipping fields.");
        return;
    }

    var btn = document.getElementById('placeOrderBtn');
    var originalText = btn.innerHTML;
    btn.innerHTML = 'Processing...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('first_name', fv);
    formData.append('last_name', lv);
    formData.append('email', ev);
    formData.append('phone', pv);
    formData.append('address', av);
    formData.append('city', cv);
    formData.append('payment_method', pm ? pm.value : '');

    fetch('process_order.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.href = 'order_success.php?order_id=' + data.order_id;
        } else {
            alert(data.message || 'There was an error processing your order.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('A network error occurred.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
