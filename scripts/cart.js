        function increaseItemQty(itemId) {
            const inputs = document.querySelectorAll('.cart-item input[readonly]');
            const input = inputs[itemId - 1];
            input.value = parseInt(input.value) + 1;
            updateCartSummary();
        }

        function decreaseItemQty(itemId) {
            const inputs = document.querySelectorAll('.cart-item input[readonly]');
            const input = inputs[itemId - 1];
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
            updateCartSummary();
        }

        function removeItem(itemId) {
            const items = document.querySelectorAll('.cart-item');
            items[itemId - 1].remove();
            
            // Check if cart is empty
            if (document.querySelectorAll('.cart-item').length === 0) {
                document.getElementById('cartContent').style.display = 'none';
                document.getElementById('emptyCart').style.display = 'block';
            }
            
            updateCartSummary();
        }

        function updateCartSummary() {
            // This would typically update the totals dynamically
            // For now, it's a placeholder for the functionality
            console.log('Cart updated');
        }
