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
            }
        });
    });
    

    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const item = this.closest('.cart-item');
            item.classList.add('fade-out');
            setTimeout(() => {
                item.remove();
                updateCart();
                showToast('cartToast');
                

                const cartItems = document.querySelectorAll('.cart-item');
                if (cartItems.length === 0) {
                    document.getElementById('emptyCart').style.display = 'block';
                    document.getElementById('cartItemsContainer').style.display = 'none';
                }
            }, 500);
        });
    });
    

    document.getElementById('applyPromo').addEventListener('click', function() {
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
    

    document.getElementById('checkoutBtn').addEventListener('click', function() {
        window.location.href = 'signup.html';
    });
    

    document.getElementById('subscribeBtn').addEventListener('click', function() {
        const emailInput = this.previousElementSibling;
        const email = emailInput.value.trim();
        
        if (email && isValidEmail(email)) {
            alert('Thank you for subscribing to our newsletter!');
            emailInput.value = '';
        } else {
            alert('Please enter a valid email address.');
        }
    });
    

    function updateCart() {
        let subtotal = 0;
        let itemCount = 0;
        

        document.querySelectorAll('.cart-item').forEach(item => {
            const price = parseFloat(item.querySelector('.price-value').textContent);
            const quantity = parseInt(item.querySelector('.quantity-value').textContent);
            subtotal += price * quantity;
            itemCount += quantity;
        });
        

        document.getElementById('cartBadge').textContent = itemCount;
        

        document.getElementById('subtotal').textContent = subtotal.toFixed(0);
        
        const discountPercent = Math.abs(parseInt(document.getElementById('discountPercent').textContent));
        const discountAmount = (subtotal * (discountPercent / 100)).toFixed(0);
        document.getElementById('discountAmount').textContent = discountAmount;
        
        const deliveryFee = parseInt(document.getElementById('deliveryFee').textContent);
        const total = subtotal - discountAmount + deliveryFee;
        
        document.getElementById('totalAmount').textContent = total.toFixed(0);
        

        document.querySelector('.order-summary').classList.add('pulse');
        setTimeout(() => {
            document.querySelector('.order-summary').classList.remove('pulse');
        }, 500);
    }
    

    function showToast(toastId) {
        const toast = document.getElementById(toastId);
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
    

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});