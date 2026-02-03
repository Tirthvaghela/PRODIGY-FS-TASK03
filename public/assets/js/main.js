// Main JavaScript for Local Pantry

// Cart sidebar functions (defined first to avoid reference errors)
function openCartSidebar() {
    const sidebar = document.getElementById('cart-sidebar');
    const overlay = document.getElementById('cart-sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.remove('translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Load cart items
        loadCartItems();
    }
}

function closeCartSidebar() {
    const sidebar = document.getElementById('cart-sidebar');
    const overlay = document.getElementById('cart-sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function loadCartItems() {
    fetch('cart.php?action=get_items')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartSidebar(data.items, data.total);
        }
    })
    .catch(error => {
        console.error('Error loading cart items:', error);
    });
}

function updateCartSidebar(items, total) {
    const emptyCart = document.getElementById('empty-cart');
    const cartItems = document.getElementById('cart-items');
    const cartFooter = document.getElementById('cart-footer');
    const cartTotal = document.getElementById('cart-total');
    
    if (items.length === 0) {
        emptyCart.classList.remove('hidden');
        cartItems.classList.add('hidden');
        cartFooter.classList.add('hidden');
    } else {
        emptyCart.classList.add('hidden');
        cartItems.classList.remove('hidden');
        cartFooter.classList.remove('hidden');
        
        // Update total
        cartTotal.textContent = `₹${parseFloat(total).toFixed(2)}`;
        
        // Update items
        cartItems.innerHTML = items.map(item => `
            <div class="flex items-center space-x-4 mb-4 pb-4 border-b border-gray-100 last:border-b-0">
                <img src="${item.image ? 'uploads/products/' + item.image : 'assets/images/placeholder.jpg'}" 
                     alt="${item.name}" 
                     class="w-16 h-16 object-cover rounded-lg">
                <div class="flex-1">
                    <h4 class="font-medium text-gray-900">${item.name}</h4>
                    <p class="text-sm text-gray-500">₹${parseFloat(item.price).toFixed(2)} each</p>
                    <div class="flex items-center space-x-2 mt-2">
                        <button onclick="updateCartItemQuantity(${item.id}, ${item.cart_quantity - 1})" 
                                class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50"
                                ${item.cart_quantity <= 1 ? 'disabled' : ''}>-</button>
                        <span class="text-sm font-medium">${item.cart_quantity}</span>
                        <button onclick="updateCartItemQuantity(${item.id}, ${item.cart_quantity + 1})" 
                                class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50">+</button>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-900">₹${parseFloat(item.subtotal).toFixed(2)}</p>
                    <button onclick="removeCartItem(${item.id})" 
                            class="text-red-500 hover:text-red-700 text-sm mt-1">Remove</button>
                </div>
            </div>
        `).join('');
    }
}

function updateCartItemQuantity(productId, newQuantity) {
    if (newQuantity < 1) return;
    
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&product_id=${productId}&quantity=${newQuantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCartItems();
            updateCartCount(data.cart_count);
        }
    })
    .catch(error => {
        console.error('Error updating cart item:', error);
    });
}

function removeCartItem(productId) {
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCartItems();
            updateCartCount(data.cart_count);
        }
    })
    .catch(error => {
        console.error('Error removing cart item:', error);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Cart functionality
    initializeCart();
    
    // Product interactions
    initializeProductInteractions();
    
    // Form enhancements
    initializeForms();
    
    // Mobile menu
    initializeMobileMenu();
    
    // Auto-hide flash messages
    autoHideFlashMessages();
    
    // Load cart count on page load
    loadCartCount();
});

// Global cart functions for inline onclick handlers
function addToCart(productId, quantity = 1) {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<div class="spinner"></div> Adding...';
    button.disabled = true;
    
    // Add to cart via AJAX
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            updateCartCount(data.cartCount || data.cart_count);
            
            // Show success notification
            showCartNotification();
            
            // Open cart sidebar
            setTimeout(() => {
                openCartSidebar();
            }, 500);
            
            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;
        } else {
            showToast(data.message || 'Error adding to cart', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error adding to cart', 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function updateFilters() {
    const sortSelect = document.querySelector('select[name="sort"]');
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('sort', sortSelect.value);
    window.location.href = currentUrl.toString();
}

function loadCartCount() {
    fetch('cart.php?action=count')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.count);
        }
    })
    .catch(error => {
        console.error('Error loading cart count:', error);
    });
}

function showCartNotification() {
    const notification = document.getElementById('cart-notification');
    if (notification) {
        notification.style.transform = 'translateY(0)';
        setTimeout(() => {
            notification.style.transform = 'translateY(100%)';
        }, 2000);
    }
}

// Product modal functions
function openProductModal(productId) {
    fetch(`product.php?id=${productId}&modal=1`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create modal container if it doesn't exist
            let modalContainer = document.getElementById('product-modal-container');
            if (!modalContainer) {
                modalContainer = document.createElement('div');
                modalContainer.id = 'product-modal-container';
                document.body.appendChild(modalContainer);
            }
            
            // Insert modal HTML
            modalContainer.innerHTML = data.html;
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }
    })
    .catch(error => {
        console.error('Error loading product modal:', error);
    });
}

function closeProductModal(event) {
    // If event is provided and it's not clicking the backdrop, don't close
    if (event && event.target !== event.currentTarget) {
        return;
    }
    
    const modalContainer = document.getElementById('product-modal-container');
    if (modalContainer) {
        modalContainer.remove();
    }
    
    // Restore body scroll
    document.body.style.overflow = '';
}

// Add click handlers to product cards
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to product cards for modal
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't open modal if clicking on add to cart button
            if (e.target.closest('button')) {
                return;
            }
            
            const productId = this.dataset.productId;
            if (productId) {
                openProductModal(productId);
            }
        });
    });
});

// Cart functionality
function initializeCart() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const quantity = this.dataset.quantity || 1;
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<div class="spinner"></div> Adding...';
            this.disabled = true;
            
            // Add to cart via AJAX
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    updateCartCount(data.cartCount);
                    
                    // Show success message
                    showToast('Product added to cart!', 'success');
                    
                    // Reset button
                    this.innerHTML = originalText;
                    this.disabled = false;
                } else {
                    showToast(data.message || 'Error adding to cart', 'error');
                    this.innerHTML = originalText;
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'error');
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    });
    
    // Quantity controls in cart
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const action = this.dataset.action;
            const productId = this.dataset.productId;
            const quantityInput = document.querySelector(`input[data-product-id="${productId}"]`);
            
            if (quantityInput) {
                let currentQuantity = parseInt(quantityInput.value);
                
                if (action === 'increase') {
                    currentQuantity++;
                } else if (action === 'decrease' && currentQuantity > 1) {
                    currentQuantity--;
                }
                
                quantityInput.value = currentQuantity;
                updateCartItem(productId, currentQuantity);
            }
        });
    });
}

// Product interactions
function initializeProductInteractions() {
    // Product image zoom
    document.querySelectorAll('.product-image').forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Product quick view
    document.querySelectorAll('.quick-view-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            openProductModal(productId);
        });
    });
}

// Form enhancements
function initializeForms() {
    // Form validation
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-format phone numbers
    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = formatPhoneNumber(this.value);
        });
    });
    
    // Password strength indicator
    document.querySelectorAll('input[type="password"][data-strength]').forEach(input => {
        input.addEventListener('input', function() {
            updatePasswordStrength(this);
        });
    });
}

// Mobile menu
function initializeMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Utility functions
function updateCartCount(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count || 0;
        cartCountElement.style.display = count > 0 ? 'flex' : 'none';
    }
}

function updateCartItem(productId, quantity) {
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart total
            if (data.cartTotal) {
                const totalElements = document.querySelectorAll('.cart-total');
                totalElements.forEach(element => {
                    element.textContent = formatPrice(data.cartTotal);
                });
            }
            
            // Update item subtotal
            const subtotalElement = document.querySelector(`[data-subtotal="${productId}"]`);
            if (subtotalElement && data.itemSubtotal) {
                subtotalElement.textContent = formatPrice(data.itemSubtotal);
            }
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 z-50 px-6 py-3 rounded-lg text-white font-medium animate-slide-in-bottom ${getToastClass(type)}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function getToastClass(type) {
    switch (type) {
        case 'success':
            return 'bg-green-500';
        case 'error':
            return 'bg-red-500';
        case 'warning':
            return 'bg-yellow-500';
        default:
            return 'bg-blue-500';
    }
}

function formatPrice(price) {
    return '₹' + parseFloat(price).toFixed(2);
}

function formatPhoneNumber(value) {
    // Remove all non-digits
    const digits = value.replace(/\D/g, '');
    
    // Format as (XXX) XXX-XXXX
    if (digits.length >= 10) {
        return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
    } else if (digits.length >= 6) {
        return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`;
    } else if (digits.length >= 3) {
        return `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
    } else {
        return digits;
    }
}

function validateForm(form) {
    let isValid = true;
    
    // Clear previous errors
    form.querySelectorAll('.error-message').forEach(error => error.remove());
    
    // Validate required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });
    
    // Validate email fields
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Validate password confirmation
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    
    if (password && confirmPassword && password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match');
        isValid = false;
    }
    
    return isValid;
}

function showFieldError(field, message) {
    const error = document.createElement('div');
    error.className = 'error-message text-red-500 text-sm mt-1';
    error.textContent = message;
    
    field.parentNode.appendChild(error);
    field.classList.add('border-red-500');
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function updatePasswordStrength(input) {
    const password = input.value;
    const strengthIndicator = document.querySelector(`[data-strength-for="${input.name}"]`);
    
    if (!strengthIndicator) return;
    
    let strength = 0;
    let strengthText = '';
    let strengthClass = '';
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    switch (strength) {
        case 0:
        case 1:
            strengthText = 'Very Weak';
            strengthClass = 'text-red-500';
            break;
        case 2:
            strengthText = 'Weak';
            strengthClass = 'text-orange-500';
            break;
        case 3:
            strengthText = 'Fair';
            strengthClass = 'text-yellow-500';
            break;
        case 4:
            strengthText = 'Good';
            strengthClass = 'text-blue-500';
            break;
        case 5:
            strengthText = 'Strong';
            strengthClass = 'text-green-500';
            break;
    }
    
    strengthIndicator.textContent = strengthText;
    strengthIndicator.className = `text-sm ${strengthClass}`;
}

function autoHideFlashMessages() {
    const flashMessages = document.querySelectorAll('[role="alert"]');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });
}

// Export functions for use in other scripts
window.LocalPantry = {
    showToast,
    updateCartCount,
    formatPrice,
    validateForm
};

// User dropdown functionality
function toggleUserDropdown() {
    const dropdown = document.getElementById('dropdownMenu');
    const arrow = document.getElementById('dropdownArrow');
    
    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
    } else {
        dropdown.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const userDropdown = document.getElementById('userDropdown');
    const dropdown = document.getElementById('dropdownMenu');
    
    if (userDropdown && dropdown && !userDropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
        const arrow = document.getElementById('dropdownArrow');
        if (arrow) {
            arrow.style.transform = 'rotate(0deg)';
        }
    }
});

// Wishlist functionality
function toggleWishlist(productId) {
    const button = document.getElementById(`wishlist-btn-${productId}`);
    const icon = button.querySelector('svg');
    
    fetch('wishlist-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=toggle&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'added') {
                // Fill the heart icon
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" fill="currentColor" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>';
                icon.classList.remove('text-gray-400');
                icon.classList.add('text-red-500');
                button.title = 'Remove from Wishlist';
                showNotification('Added to wishlist! ❤️', 'success');
            } else {
                // Empty the heart icon
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>';
                icon.classList.remove('text-red-500');
                icon.classList.add('text-gray-400');
                button.title = 'Add to Wishlist';
                showNotification('Removed from wishlist', 'info');
            }
        } else {
            showNotification(data.message || 'Failed to update wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to update wishlist', 'error');
    });
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
        type === 'success' ? 'bg-green-600' : 
        type === 'error' ? 'bg-red-600' : 
        type === 'info' ? 'bg-blue-600' : 'bg-gray-600'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}