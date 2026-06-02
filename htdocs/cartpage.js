document.addEventListener('DOMContentLoaded', function() {

    updateCart();
    

    document.querySelectorAll('.quantity-increase').forEach(button => {
        button.addEventListener('click', function() {
            const item = this.closest('.cart-item');
            const quantityEl = item.querySelector('.quantity-value');
            const currentQuantity = parseInt(quantityEl.textContent);
            quantityEl.textContent = currentQuantity + 1;
            updateCart();
            showToast('cartToast');
            // sync with server
            const productId = item.getAttribute('data-id');
            sendUpdate(productId, currentQuantity + 1);
        });
    });
    

    document.querySelectorAll('.quantity-decrease').forEach(button => {
        button.addEventListener('click', function() {
            const item = this.closest('.cart-item');
            const quantityEl = item.querySelector('.quantity-value');
            const currentQuantity = parseInt(quantityEl.textContent);
            if (currentQuantity > 1) {
                quantityEl.textContent = currentQuantity - 1;
                updateCart();
                showToast('cartToast');
                const productId = item.getAttribute('data-id');
                sendUpdate(productId, currentQuantity - 1);
            }
        });
    });
    

    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const item = this.closest('.cart-item');
            item.classList.add('fade-out');
            setTimeout(() => {
                const productId = item.getAttribute('data-id');
                // remove on server first
                sendRemove(productId).then(() => {
                    item.remove();
                    updateCart();
                    showToast('cartToast');
                }).catch(() => {
                    // fallback: still remove locally
                    item.remove();
                    updateCart();
                    showToast('cartToast');
                });
                

                const cartItems = document.querySelectorAll('.cart-item');
                if (cartItems.length === 0) {
                    document.getElementById('emptyCart').style.display = 'block';
                    document.getElementById('cartItemsContainer').style.display = 'none';
                }
            }, 500);
        });
    });
    

    const applyPromoBtn = document.getElementById('applyPromo');
    if (applyPromoBtn) {
        applyPromoBtn.addEventListener('click', function() {
        const promoInput = document.getElementById('promoInput');
        const promoCode = promoInput.value.trim().toUpperCase();
        
        if (promoCode === 'SAVE10') {
            document.getElementById('discountPercent').textContent = '-10';
            updateCart();
            showToast('promoToast');
            promoInput.value = '';
        } else if (promoCode === 'SAVE20') {
            document.getElementById('discountPercent').textContent = '-20';
            updateCart();
            showToast('promoToast');
            promoInput.value = '';
        } else if (promoCode === 'SAVE30') {
            document.getElementById('discountPercent').textContent = '-30';
            updateCart();
            showToast('promoToast');
            promoInput.value = '';
        } else if (promoCode === 'FREESHIP') {
            document.getElementById('deliveryFee').textContent = '0';
            updateCart();
            showToast('promoToast');
            promoInput.value = '';
        } else if (promoCode !== '') {
            showToast('errorToast');
            promoInput.value = '';
        }
    });
    }
    
    // Let the href="checkout.php" in the HTML handle navigation
    // checkout.php will internally redirect to signup if the user isn't logged in.
    
    const subscribeBtn = document.getElementById('subscribeBtn');
    if (subscribeBtn) {
        subscribeBtn.addEventListener('click', function() {
            const emailInput = this.previousElementSibling;
        const email = emailInput.value.trim();
        
        if (email && isValidEmail(email)) {
            alert('Thank you for subscribing to our newsletter!');
            emailInput.value = '';
        } else {
            alert('Please enter a valid email address.');
        }
        });
    }
    

    function updateCart() {
        let subtotal = 0;
        let itemCount = 0;
        

        document.querySelectorAll('.cart-item').forEach(item => {
            const price = parseFloat(item.querySelector('.price-value').textContent);
            const quantity = parseInt(item.querySelector('.quantity-value').textContent);
            subtotal += price * quantity;
            itemCount += quantity;
        });
        

        const cartBadge = document.getElementById('cartBadge');
        if (cartBadge) cartBadge.textContent = itemCount;
        
        const subtotalEl = document.getElementById('subtotal');
        if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(0);
        
        const discPctEl = document.getElementById('discountPercent');
        const discountPercent = discPctEl ? Math.abs(parseInt(discPctEl.textContent) || 0) : 0;
        const discountAmount = (subtotal * (discountPercent / 100)).toFixed(0);
        const discAmtEl = document.getElementById('discountAmount');
        if (discAmtEl) discAmtEl.textContent = discountAmount;
        
        const deliveryFeeEl = document.getElementById('deliveryFee');
        const deliveryFee = deliveryFeeEl ? parseInt(deliveryFeeEl.textContent) || 0 : 0;
        const total = subtotal - discountAmount + deliveryFee;
        
        const totalEl = document.getElementById('totalAmount');
        if (totalEl) totalEl.textContent = total.toFixed(0);
        
        const summary = document.querySelector('.order-summary');
        if (summary) {
            summary.classList.add('pulse');
            setTimeout(() => { summary.classList.remove('pulse'); }, 500);
        }
    }

    // helper: send update quantity to server
    function sendUpdate(productId, quantity) {
        return fetch('update_cart_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ product_id: productId, quantity: quantity })
        }).then(r => r.json()).then(data => {
            if (!data.success) console.warn('Update failed', data.message);
        }).catch(err => console.error('Update error', err));
    }

    // helper: remove
    function sendRemove(productId) {
        return fetch('remove_cart_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ product_id: productId })
        }).then(r => r.json()).then(data => {
            if (!data.success) return Promise.reject(data.message);
            return data;
        });
    }
    

    function showToast(toastId) {
        const toastEl = document.getElementById(toastId);
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    }
    

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});