        // Switch panel tabs dynamically
        function switchTab(tabId) {
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.sidebar-menu-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('panel-' + tabId).classList.add('active');
            const targetBtn = document.querySelector(`.sidebar-menu-btn[data-tab="${tabId}"]`);
            if (targetBtn) {
                targetBtn.classList.add('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.sidebar-menu-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    switchTab(tabName);
                });
            });
        });

        // Save Account Profile details back to localStorage
        function saveProfileDetails(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('profileFirstName').value;
            const lastName = document.getElementById('profileLastName').value;
            const email = document.getElementById('profileEmail').value;
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmNewPassword = document.getElementById('confirmNewPassword').value;

            // Password Change validation
            if (newPassword || confirmNewPassword) {
                const currentPassword = document.getElementById('currentPassword').value;
                if (!currentPassword) {
                    alert('Please enter your current password to save password changes.');
                    return;
                }
                if (newPassword !== confirmNewPassword) {
                    alert('New passwords do not match.');
                    return;
                }
                if (newPassword.length < 8) {
                    alert('New password must be at least 8 characters long.');
                    return;
                }
            }

            // Save updated profile
            const userData = {
                firstName,
                lastName,
                email
            };

            localStorage.setItem('userData', JSON.stringify(userData));
            
            // Reload components (Note: assumes window.firstName/lastName are updated if needed or reloads)
            if(window.loadUserProfile) window.loadUserProfile();
            
            // Clear passwords fields
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmNewPassword').value = '';

            alert('Account details updated successfully!');
            
            if (typeof checkAuthStatus === 'function') {
                checkAuthStatus();
            }
        }

        function confirmDeleteAccount() {
            if (confirm("Are you absolutely sure you want to delete your account? This action cannot be undone and you will lose all your order history.")) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_account.php';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Wishlist items management
        let wishlistCount = 3;
        function removeWishlistItem(itemId) {
            const itemElement = document.getElementById(`wishlist-item-${itemId}`);
            if (itemElement) {
                itemElement.remove();
                wishlistCount--;
                document.getElementById('statWishlistCount').textContent = wishlistCount;
                
                if (wishlistCount === 0) {
                    document.getElementById('wishlistGrid').style.display = 'none';
                    document.getElementById('wishlistEmptyMessage').style.display = 'block';
                }
            }
        }
