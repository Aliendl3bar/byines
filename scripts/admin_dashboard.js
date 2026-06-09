document.addEventListener('DOMContentLoaded', function() {

    // ============================
    // 1. TAB SWITCHER (Sidebar)
    // ============================
    document.querySelectorAll('.admin-menu-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.admin-menu-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            const targetPanel = document.getElementById('panel-' + this.getAttribute('data-tab'));
            if (targetPanel) targetPanel.classList.add('active');
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab) {
        const tabBtn = document.querySelector('.admin-menu-btn[data-tab="' + activeTab + '"]');
        if (tabBtn) tabBtn.click();
    }

    // ============================
    // 2. MODAL OPEN / CLOSE
    // ============================
    window.openModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', function() {
            closeModal(this.getAttribute('data-close-modal'));
        });
    });

    document.querySelectorAll('.admin-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.admin-modal-backdrop.show').forEach(m => m.classList.remove('show'));
            document.body.style.overflow = '';
        }
    });

    // ============================
    // 3. PRODUCT MODALS
    // ============================
    const btnAddProduct = document.getElementById('btn-open-add-product');
    if (btnAddProduct) {
        btnAddProduct.addEventListener('click', () => openModal('modal-add-product'));
    }

    let currentProductId = null;
    let localImages = [];
    let localVariants = [];
    let tempVariantIdCounter = -1;

    document.querySelectorAll('.btn-manage-product').forEach(btn => {
        btn.addEventListener('click', function() {
            currentProductId = this.dataset.productId;
            const name = this.dataset.productName;
            
            localImages = JSON.parse(JSON.stringify(window.productImagesData?.[currentProductId] || []));
            localVariants = JSON.parse(JSON.stringify(window.productVariantsData?.[currentProductId] || []));

            document.getElementById('edit-id').value = currentProductId;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-sku').value = this.dataset.productSku;
            document.getElementById('edit-price').value = this.dataset.productPrice;
            document.getElementById('edit-category').value = this.dataset.productCategory;
            document.getElementById('edit-description').value = this.dataset.productDescription;
            document.getElementById('edit-is-active').checked = (this.dataset.productActive === '1');
            const titleEl = document.getElementById('manage-modal-title');
            if (titleEl) titleEl.textContent = 'Manage: ' + name;

            const uploadIdEl = document.getElementById('upload-product-id');
            if (uploadIdEl) uploadIdEl.value = currentProductId;

            if (typeof renderImageGallery === 'function') renderImageGallery();
            if (typeof renderVariantsTable === 'function') renderVariantsTable();

            switchManageTab('details');
            openModal('modal-manage-product');
        });
    });

    window.uiAddVariant = function() {
        const color = document.getElementById('new-variant-color').value.trim();
        const size = document.getElementById('new-variant-size').value;
        const stock = parseInt(document.getElementById('new-variant-stock').value);
        const price = parseFloat(document.getElementById('new-variant-price').value || 0);
        if(!color || !size) return;
        
        localVariants.push({
            id: tempVariantIdCounter--,
            color: color,
            size: size,
            stock_quantity: stock,
            price_modifier: price
        });
        document.getElementById('new-variant-color').value = '';
        document.getElementById('new-variant-stock').value = '0';
        document.getElementById('new-variant-price').value = '0.00';
        renderVariantsTable();
        renderImageGallery();
    };

    window.uiDeleteVariant = function(vid) {
        if(!confirm('Remove this variant?')) return;
        localVariants = localVariants.filter(v => v.id != vid);
        renderVariantsTable();
        renderImageGallery();
    };

    window.uiEditVariantSubmit = function(e) {
        e.preventDefault();
        const vid = document.getElementById('ev-variant-id').value;
        const color = document.getElementById('ev-color').value.trim();
        const size = document.getElementById('ev-size').value;
        const stock = parseInt(document.getElementById('ev-stock').value);
        const price = parseFloat(document.getElementById('ev-price-mod').value || 0);
        
        const idx = localVariants.findIndex(v => v.id == vid);
        if(idx !== -1) {
            localVariants[idx].color = color;
            localVariants[idx].size = size;
            localVariants[idx].stock_quantity = stock;
            localVariants[idx].price_modifier = price;
        }
        closeModal('modal-edit-variant');
        renderVariantsTable();
        renderImageGallery();
    };
    
    const editVariantForm = document.querySelector('#modal-edit-variant form');
    if (editVariantForm) editVariantForm.onsubmit = window.uiEditVariantSubmit;

    window.uiDeleteImage = function(imgId) {
        if(!confirm('Remove this image?')) return;
        localImages = localImages.filter(i => i.id != imgId);
        renderImageGallery();
    };

    window.uiSetMainImage = function(imgId) {
        localImages.forEach(i => i.is_main = 0);
        const img = localImages.find(i => i.id == imgId);
        if(img) img.is_main = 1;
        renderImageGallery();
    };

    window.uiUpdateImageColor = function(imgId, color) {
        const img = localImages.find(i => i.id == imgId);
        if(img) img.color = color;
    };

    window.uiReorderImage = function(index, dir) {
        if (dir === 'prev' && index > 0) {
            const temp = localImages[index - 1];
            localImages[index - 1] = localImages[index];
            localImages[index] = temp;
        } else if (dir === 'next' && index < localImages.length - 1) {
            const temp = localImages[index + 1];
            localImages[index + 1] = localImages[index];
            localImages[index] = temp;
        }
        
        localImages.forEach((img, idx) => img.sort_order = idx);
        renderImageGallery();
    };

    window.uploadImagesAjax = function() {
        const form = document.getElementById('form-upload-images');
        const formData = new FormData(form);
        const btn = document.getElementById('btn-upload-images');
        btn.disabled = true;
        btn.textContent = 'Uploading...';
        
        fetch('admin_manage_product.php', {
            method: 'POST',
            body: formData
        }).then(res => res.json()).then(data => {
            btn.disabled = false;
            btn.textContent = 'Upload Images';
            if(data.success) {
                data.images.forEach(dbImg => {
                    if (!localImages.find(li => li.id == dbImg.id)) {
                        localImages.push(dbImg);
                    }
                });
                renderImageGallery();
                document.getElementById('upload-new-images').value = '';
                document.getElementById('upload-images-preview').innerHTML = '';
            } else {
                alert(data.message || 'Upload failed');
            }
        }).catch(err => {
            btn.disabled = false;
            btn.textContent = 'Upload Images';
            alert('Error uploading');
        });
    };

    window.saveProductAssets = function() {
        const formData = new FormData();
        formData.append('action', 'save_product_assets');
        formData.append('product_id', currentProductId);
        localImages.forEach((img, idx) => {
            img.sort_order = idx;
            img.is_main = (idx === 0) ? 1 : 0;
        });
        formData.append('images', JSON.stringify(localImages));
        formData.append('variants', JSON.stringify(localVariants));

        const btns = document.querySelectorAll('.btn-save-assets');
        btns.forEach(b => { b.disabled = true; b.textContent = 'Saving...'; });

        fetch('admin_manage_product.php', {
            method: 'POST',
            body: formData
        }).then(res => res.json()).then(data => {
            btns.forEach(b => { b.disabled = false; b.textContent = 'Save Changes'; });
            if(data.success) {
                if (window.productImagesData) window.productImagesData[currentProductId] = data.images;
                if (window.productVariantsData) window.productVariantsData[currentProductId] = data.variants;
                closeModal('modal-manage-product');
                alert('Changes saved successfully!');
            } else {
                alert(data.message || 'Save failed');
            }
        }).catch(err => {
            btns.forEach(b => { b.disabled = false; b.textContent = 'Save Changes'; });
            alert('Error saving changes');
        });
    };

    function getVariantColors() {
        return [...new Set(localVariants.map(v => v.color))].sort();
    }

    window.renderImageGallery = function() {
        const gallery = document.getElementById('manage-image-gallery');
        if (!gallery) return;
        
        localImages.forEach((img, idx) => {
            img.is_main = (idx === 0) ? 1 : 0;
        });
        
        const images = localImages;
        const variantColors = getVariantColors();
        
        if (images.length === 0) {
            gallery.innerHTML = '<p style="color:var(--gray-400);font-size:0.85rem;grid-column:1/-1;">No images uploaded yet.</p>';
            return;
        }

        let html = '';
        images.forEach((img, index) => {
            const imgSrc = '../products/' + currentProductId + '/img/' + img.image_name;
            const isMain = index === 0;
            const currentColor = img.color || '';

            html += '<div class="admin-image-edit-card' + (isMain ? ' is-main-card' : '') + '">';
            html += '  <img src="' + imgSrc + '" alt="Product image" loading="lazy">';
            
            if (index > 0) {
                html += '  <a href="javascript:void(0)" onclick="window.uiReorderImage(' + index + ', \'prev\')" class="admin-image-reorder-btn reorder-prev" title="Move Left">';
                html += '    <svg viewBox="0 0 24 24" width="16" height="16"><path fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/></svg>';
                html += '  </a>';
            }
            if (index < images.length - 1) {
                html += '  <a href="javascript:void(0)" onclick="window.uiReorderImage(' + index + ', \'next\')" class="admin-image-reorder-btn reorder-next" title="Move Right">';
                html += '    <svg viewBox="0 0 24 24" width="16" height="16"><path fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/></svg>';
                html += '  </a>';
            }

            html += '  <div class="admin-image-top-actions">';
            if (isMain) {
                html += '    <div class="admin-image-star-badge active" title="Main Thumbnail">';
                html += '      <svg viewBox="0 0 24 24" width="12" height="12"><path fill="currentColor" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
                html += '      <span>Main</span>';
                html += '    </div>';
            } else {
                html += '    <div></div>';
            }
            html += '    <a href="javascript:void(0)" onclick="window.uiDeleteImage(' + img.id + ')" class="admin-image-action-btn delete-btn" title="Delete Image">';
            html += '      <svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>';
            html += '    </a>';
            html += '  </div>';

            html += '  <div class="admin-image-color-bar">';
            html += '      <select name="color" onchange="window.uiUpdateImageColor(' + img.id + ', this.value)" title="Assign color">';
            html += '        <option value=""' + (currentColor === '' ? ' selected' : '') + '>No color</option>';
            variantColors.forEach(c => {
                const selected = (currentColor === c) ? ' selected' : '';
                html += '        <option value="' + c + '"' + selected + '>' + c + '</option>';
            });
            if (currentColor && !variantColors.includes(currentColor)) {
                html += '        <option value="' + currentColor + '" selected>' + currentColor + '</option>';
            }
            html += '      </select>';
            html += '  </div>';
            html += '</div>';
        });

        gallery.innerHTML = html;
    };

    window.renderVariantsTable = function() {
        const tbody = document.getElementById('stock-variants-body');
        if (!tbody) return;
        const variants = localVariants;
        
        if (variants.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--gray-400);">No variants yet. Add one below.</td></tr>';
            return;
        }

        let html = '';
        variants.forEach(v => {
            const stockClass = parseInt(v.stock_quantity) === 0 ? ' style="color:#A34848;font-weight:600;"' : '';
            html += '<tr>';
            html += '  <td>' + v.color + '</td>';
            html += '  <td>' + v.size + '</td>';
            html += '  <td' + stockClass + '>' + v.stock_quantity + '</td>';
            html += '  <td>$' + parseFloat(v.price_modifier).toFixed(2) + '</td>';
            html += '  <td>';
            html += '    <button type="button" class="admin-btn admin-btn-sm btn-edit-variant" '
                  + 'data-vid="' + v.id + '" '
                  + 'data-color="' + v.color + '" '
                  + 'data-size="' + v.size + '" '
                  + 'data-stock="' + v.stock_quantity + '" '
                  + 'data-pricemod="' + v.price_modifier + '"'
                  + '>Edit</button> ';
            html += '    <button type="button" class="admin-btn admin-btn-sm admin-btn-danger" '
                  + 'onclick="window.uiDeleteVariant(' + v.id + ')"'
                  + '>Delete</button>';
            html += '  </td>';
            html += '</tr>';
        });

        tbody.innerHTML = html;

        tbody.querySelectorAll('.btn-edit-variant').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('ev-variant-id').value = this.dataset.vid;
                document.getElementById('ev-color').value = this.dataset.color;
                document.getElementById('ev-size').value = this.dataset.size;
                document.getElementById('ev-stock').value = this.dataset.stock;
                document.getElementById('ev-price-mod').value = parseFloat(this.dataset.pricemod).toFixed(2);
                openModal('modal-edit-variant');
            });
        });
    };

    window.switchManageTab = function(tabName) {
        document.querySelectorAll('.admin-modal-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.admin-modal-tab-panel').forEach(p => p.classList.remove('active'));

        const activeTab = document.querySelector('.admin-modal-tab[data-manage-tab="' + tabName + '"]');
        const activePanel = document.getElementById('manage-tab-' + tabName);
        if (activeTab) activeTab.classList.add('active');
        if (activePanel) activePanel.classList.add('active');
    };

    document.querySelectorAll('.admin-modal-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            switchManageTab(this.dataset.manageTab);
        });
    });

    // ============================
    // IMAGE PREVIEW ON FILE INPUT
    // ============================
    function setupImagePreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;

        input.addEventListener('change', function() {
            preview.innerHTML = '';
            if (this.files.length === 0) return;
            Array.from(this.files).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width:60px;height:80px;object-fit:cover;border-radius:4px;border:1px solid var(--brand-earth);';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    setupImagePreview('add-images', 'add-images-preview');
    setupImagePreview('upload-new-images', 'upload-images-preview');
    setupImagePreview('add-col-image', 'add-col-preview');
    setupImagePreview('edit-col-image', 'edit-col-preview');
    setupImagePreview('add-cat-image', 'add-cat-preview');
    setupImagePreview('edit-cat-image', 'edit-cat-preview');

    // ============================
    // CATEGORY MODALS
    // ============================
    const btnAddCategory = document.getElementById('btn-open-add-category');
    if (btnAddCategory) {
        btnAddCategory.addEventListener('click', () => openModal('modal-add-category'));
    }

    document.querySelectorAll('.btn-edit-category').forEach(btn => {
        btn.addEventListener('click', function() {
            const idEl = document.getElementById('edit-cat-id');
            const nameEl = document.getElementById('edit-cat-name');
            const slugEl = document.getElementById('edit-cat-slug');
            if (idEl) idEl.value = this.dataset.id;
            if (nameEl) nameEl.value = this.dataset.name;
            if (slugEl) slugEl.value = this.dataset.slug;

            const imgEl = document.getElementById('edit-cat-current-image');
            if (imgEl && this.dataset.imageUrl) {
                imgEl.src = this.dataset.imageUrl;
                imgEl.style.display = 'block';
            } else if (imgEl) {
                imgEl.style.display = 'none';
            }
            openModal('modal-edit-category');
        });
    });

    // ============================
    // COLLECTION MODALS
    // ============================
    const btnAddCollection = document.getElementById('btn-open-add-collection');
    if (btnAddCollection) {
        btnAddCollection.addEventListener('click', () => openModal('modal-add-collection'));
    }

    document.querySelectorAll('.btn-edit-collection').forEach(btn => {
        btn.addEventListener('click', function() {
            const idEl = document.getElementById('edit-col-id');
            const titleEl = document.getElementById('edit-col-title');
            const productsEl = document.getElementById('edit-col-products-ids');
            
            if (idEl) idEl.value = this.dataset.id;
            if (titleEl) titleEl.value = this.dataset.title;
            if (productsEl) productsEl.value = this.dataset.productsIds;
            
            const imgEl = document.getElementById('edit-col-current-image');
            if (imgEl && this.dataset.imagePath) {
                imgEl.src = this.dataset.imagePath;
                imgEl.style.display = 'block';
            } else if (imgEl) {
                imgEl.style.display = 'none';
            }
            openModal('modal-edit-collection');
        });
    });

    // ============================
    // AUTO-DISMISS FLASH MESSAGES
    // ============================
    document.querySelectorAll('.admin-alert-banner').forEach(banner => {
        setTimeout(() => {
            banner.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(-10px)';
            setTimeout(() => banner.remove(), 500);
        }, 5000);
    });

});
