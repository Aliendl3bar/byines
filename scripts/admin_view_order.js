function deleteThisOrder() {
    if (!confirm('Are you absolutely sure you want to permanently delete this order? This action cannot be undone.')) {
        return;
    }
    const orderId = document.querySelector('.admin-view-order-content').dataset.orderId;
    const formData = new FormData();
    formData.append('action', 'delete_order');
    formData.append('order_id', orderId);

    fetch('admin_manage_order.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'admin_dashboard.php#panel-orders';
        } else {
            alert(data.message || 'Failed to delete order.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('A network error occurred.');
    });
}
