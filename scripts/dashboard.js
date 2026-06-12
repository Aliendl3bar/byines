        // switch panel tabs dynamically
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

        // save account profile details to localstorage
        function saveProfileDetails(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('profileFirstName').value;
            const lastName = document.getElementById('profileLastName').value;
            const email = document.getElementById('profileEmail').value;
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmNewPassword = document.getElementById('confirmNewPassword').value;

            // password change validation
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

            // save updated profile
            const userData = {
                firstName,
                lastName,
                email
            };

            localStorage.setItem('userData', JSON.stringify(userData));
            
            // reload components
            if(window.loadUserProfile) window.loadUserProfile();
            
            // clear password fields
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

        // wishlist items management
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
