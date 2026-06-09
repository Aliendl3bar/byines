        // Switch panel tabs dynamically
        function switchTab(tabName) {
            // Update Active navigation button
            document.querySelectorAll('.sidebar-menu-btn').forEach(btn => {
                if (btn.getAttribute('data-tab') === tabName) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // Switch active panel
            document.querySelectorAll('.tab-panel').forEach(panel => {
                if (panel.id === `panel-${tabName}`) {
                    panel.classList.add('active');
                } else {
                    panel.classList.remove('active');
                }
            });
        }

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
            
            // Force header username updates
            if (typeof checkAuthStatus === 'function') {
                checkAuthStatus();
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
