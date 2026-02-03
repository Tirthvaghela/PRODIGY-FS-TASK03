<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-100">
        <div class="w-full px-6">
            <div class="relative flex items-center h-16">
                <!-- Logo - Left Side -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-emerald-700 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </div>
                    <a href="index.php" class="text-xl font-bold text-gray-800 tracking-wide">LOCAL PANTRY</a>
                </div>

                <!-- Navigation - Centered -->
                <nav class="absolute left-1/2 transform -translate-x-1/2 hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium">Shop</a>
                    <?php if ($currentUser): ?>
                    <a href="my-orders.php" class="text-gray-500 hover:text-gray-700 font-medium">My Orders</a>
                    <a href="wishlist.php" class="text-gray-500 hover:text-gray-700 font-medium">Wishlist</a>
                    <?php endif; ?>
                    <a href="track-order.php" class="text-gray-500 hover:text-gray-700 font-medium">Track Order</a>
                    <a href="support.php" class="text-emerald-700 font-medium border-b-2 border-emerald-700 pb-1">Support</a>
                </nav>

                <!-- Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <?php if ($currentUser): ?>
                        <!-- User Dropdown -->
                        <div class="relative" id="userDropdown">
                            <button onclick="toggleUserDropdown()" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 font-medium focus:outline-none">
                                <span>Hello, <?= htmlspecialchars($currentUser['name']) ?></span>
                                <svg class="w-4 h-4 transition-transform duration-200" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span>Profile</span>
                                    </div>
                                </a>
                                <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span>Logout</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-emerald-700 font-medium">SIGN IN</a>
                    <?php endif; ?>
                    
                    <a href="cart.php" class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Support Content -->
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-100 rounded-full mb-6">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 110 19.5 9.75 9.75 0 010-19.5z"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-800 mb-4">How can we help?</h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    We're here to support you every step of the way. Choose the option that works best for you.
                </p>
            </div>

            <!-- Support Options Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Live Chat -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center hover:shadow-md transition-shadow duration-200">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Live Chat</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Get instant help from our support team. We're available during business hours to answer your questions.
                    </p>
                    <div class="text-sm text-gray-500 mb-6">
                        <p class="font-medium">Available:</p>
                        <p>Mon-Fri: 9:00 AM - 6:00 PM</p>
                        <p>Sat: 10:00 AM - 4:00 PM</p>
                    </div>
                    <button onclick="startLiveChat()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                        Start Chat
                    </button>
                </div>

                <!-- Call Store -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center hover:shadow-md transition-shadow duration-200">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-6">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Call Store</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Speak directly with our team for personalized assistance with orders, products, or any concerns.
                    </p>
                    <div class="text-sm text-gray-500 mb-6">
                        <p class="font-medium">Phone Number:</p>
                        <p class="text-lg font-semibold text-emerald-600">+1 (555) LOCAL-88</p>
                        <p class="mt-2">Same hours as Live Chat</p>
                    </div>
                    <a href="tel:+15555622588" class="inline-block w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                        Call Now
                    </a>
                </div>

                <!-- Visit Us -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center hover:shadow-md transition-shadow duration-200">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-6">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Visit Us</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Come see us in person! Browse our full selection and get hands-on help from our knowledgeable staff.
                    </p>
                    <div class="text-sm text-gray-500 mb-6">
                        <p class="font-medium">Address:</p>
                        <p>123 Market Row</p>
                        <p>Springfield</p>
                        <p class="mt-2">
                            <span class="font-medium">Store Hours:</span><br>
                            Mon-Sat: 8:00 AM - 8:00 PM<br>
                            Sun: 10:00 AM - 6:00 PM
                        </p>
                    </div>
                    <button onclick="openDirections()" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                        Get Directions
                    </button>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-800 text-center mb-8">Frequently Asked Questions</h2>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
                    
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-800 mb-2">What are your delivery hours?</h3>
                        <p class="text-gray-600">We deliver Monday through Saturday from 9:00 AM to 7:00 PM, and Sunday from 11:00 AM to 5:00 PM.</p>
                    </div>
                    
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-800 mb-2">Do you offer same-day delivery?</h3>
                        <p class="text-gray-600">Yes! Orders placed before 2:00 PM can be delivered the same day within our delivery zone.</p>
                    </div>
                    
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-800 mb-2">What's your return policy?</h3>
                        <p class="text-gray-600">We offer a 100% satisfaction guarantee. If you're not happy with any product, contact us within 24 hours for a full refund or replacement.</p>
                    </div>
                    
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-800 mb-2">How do I track my order?</h3>
                        <p class="text-gray-600">You can track your order using our <a href="track-order.php" class="text-emerald-600 hover:text-emerald-700 font-medium">Track Order</a> page with your order number.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function startLiveChat() {
            // In a real implementation, this would open a chat widget
            alert('Live chat feature coming soon! Please call us at +1 (555) LOCAL-88 for immediate assistance.');
        }

        function openDirections() {
            // Open Google Maps with the store address
            const address = encodeURIComponent('123 Market Row, Springfield');
            window.open(`https://www.google.com/maps/search/?api=1&query=${address}`, '_blank');
        }

        // User dropdown functionality
        function toggleUserDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            const arrow = document.getElementById('dropdownArrow');
            
            dropdown.classList.toggle('hidden');
            arrow.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');
            
            if (!dropdown.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
                document.getElementById('dropdownArrow').style.transform = 'rotate(0deg)';
            }
        });
    </script>
</body>
</html>