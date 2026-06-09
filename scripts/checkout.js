document.addEventListener('DOMContentLoaded', function() {
    // Initial calculation on load
    calculateShipping();
});

function calculateShipping() {
    const cityInput = document.getElementById('city').value.trim().toLowerCase();
    let shippingCost = 0;
    let shippingDisplay = '';

    if (cityInput === '') {
        shippingDisplay = 'Enter city...';
    } else if (cityInput === 'tangier' || cityInput === 'tanger') {
        shippingCost = 0;
        shippingDisplay = 'FREE';
    } else {
        shippingCost = 3.00;
        shippingDisplay = '$3.00';
    }

    document.getElementById('summary-shipping').innerText = shippingDisplay;
    document.getElementById('summary-shipping').style.color = shippingCost === 0 && cityInput !== '' ? '#4CAF50' : 'var(--brand-dark)';

    const total = PHP_SUBTOTAL + PHP_TAX + shippingCost;
    
    // Format to 2 decimal places
    document.getElementById('summary-total').innerText = total.toFixed(2);
}

function selectPayment(method) {
    document.getElementById('payment_cod').checked = (method === 'cod');
    document.getElementById('payment_paypal').checked = (method === 'paypal');
    
    document.getElementById('payment_cod_wrapper').classList.toggle('selected', method === 'cod');
    document.getElementById('payment_paypal_wrapper').classList.toggle('selected', method === 'paypal');
}

function processOrder() {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();
    const city = document.getElementById('city').value.trim();
    
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

    if (!firstName || !lastName || !email || !phone || !address || !city) {
        alert("Please fill out all required shipping fields.");
        return;
    }

    const btn = document.getElementById('placeOrderBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Processing...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('first_name', firstName);
    formData.append('last_name', lastName);
    formData.append('email', email);
    formData.append('phone', phone);
    formData.append('address', address);
    formData.append('city', city);
    formData.append('payment_method', paymentMethod);

    fetch('process_order.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'order_success.php?order_id=' + data.order_id;
        } else {
            alert(data.message || 'There was an error processing your order.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert('A network error occurred.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
