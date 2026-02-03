<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/models/Address.php';

// Require login
if (!isLoggedIn()) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$currentUser = getCurrentUser();
$addresses = [];
$error = '';
$success = '';

// Try to get addresses, handle if table doesn't exist
try {
    $addresses = Address::getByUserId($currentUser['id']);
} catch (Exception $e) {
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $error = 'Address book not set up yet. <a href="create-address-table.php" class="underline text-emerald-600">Click here to set it up</a>.';
    } else {
        $error = 'Error loading addresses: ' . $e->getMessage();
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($name)) {
            $error = 'Name is required';
        } elseif (!empty($newPassword)) {
            // Password change requested
            if (empty($currentPassword)) {
                $error = 'Current password is required to change password';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters long';
            } else {
                $result = User::changePassword($currentUser['id'], $currentPassword, $newPassword);
                if (!$result['success']) {
                    $error = $result['error'];
                } else {
                    // Update profile data
                    User::updateProfile($currentUser['id'], [
                        'name' => $name,
                        'phone' => $phone,
                        'address' => $currentUser['address'] // Keep existing address
                    ]);
                    $success = 'Profile and password updated successfully!';
                    $currentUser = getCurrentUser(); // Refresh data
                }
            }
        } else {
            // Just profile update
            $result = User::updateProfile($currentUser['id'], [
                'name' => $name,
                'phone' => $phone,
                'address' => $currentUser['address'] // Keep existing address
            ]);
            
            if ($result) {
                $success = 'Profile updated successfully!';
                $currentUser = getCurrentUser(); // Refresh data
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
    
    elseif ($_POST['action'] === 'add_address') {
        $addressData = [
            'user_id' => $currentUser['id'],
            'label' => trim($_POST['label'] ?? ''),
            'name' => trim($_POST['address_name'] ?? ''),
            'phone' => trim($_POST['address_phone'] ?? ''),
            'address_line_1' => trim($_POST['address_line_1'] ?? ''),
            'address_line_2' => trim($_POST['address_line_2'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'country' => trim($_POST['country'] ?? 'India'),
            'is_default' => isset($_POST['is_default'])
        ];
        
        if (empty($addressData['label']) || empty($addressData['name']) || empty($addressData['phone']) || 
            empty($addressData['address_line_1']) || empty($addressData['city']) || 
            empty($addressData['state']) || empty($addressData['postal_code'])) {
            $error = 'Please fill in all required address fields';
        } else {
            $result = Address::create($addressData);
            if ($result['success']) {
                $success = 'Address added successfully!';
                $addresses = Address::getByUserId($currentUser['id']); // Refresh addresses
            } else {
                $error = $result['error'];
            }
        }
    }
    
    elseif ($_POST['action'] === 'delete_address') {
        $addressId = $_POST['address_id'] ?? 0;
        if (Address::delete($addressId)) {
            $success = 'Address deleted successfully!';
            $addresses = Address::getByUserId($currentUser['id']); // Refresh addresses
        } else {
            $error = 'Failed to delete address';
        }
    }
    
    elseif ($_POST['action'] === 'set_default') {
        $addressId = $_POST['address_id'] ?? 0;
        if (Address::setDefault($addressId, $currentUser['id'])) {
            $success = 'Default address updated!';
            $addresses = Address::getByUserId($currentUser['id']); // Refresh addresses
        } else {
            $error = 'Failed to update default address';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?= SITE_NAME ?></title>
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
                    <a href="my-orders.php" class="text-gray-500 hover:text-gray-700 font-medium">My Orders</a>
                    <a href="track-order.php" class="text-gray-500 hover:text-gray-700 font-medium">Track Order</a>
                    <a href="support.php" class="text-gray-500 hover:text-gray-700 font-medium">Support</a>
                </nav>

                <!-- Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <span class="text-gray-700 font-medium">Hello, <?= htmlspecialchars($currentUser['name']) ?></span>
                    <a href="logout.php" class="text-gray-500 hover:text-gray-700 font-medium">Logout</a>
                    <a href="cart.php" class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Profile Content -->
    <div class="w-full px-6 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-8">
                <a href="index.php" class="hover:text-gray-700">Shop</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-emerald-600 font-medium">My Profile</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-800 mb-8">My Profile</h1>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Profile Information -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Personal Information -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Personal Information</h2>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                    <input type="text" name="name" required
                                           value="<?= htmlspecialchars($currentUser['name']) ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                                
                                <!-- Email (read-only) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" value="<?= htmlspecialchars($currentUser['email']) ?>" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50" 
                                           readonly>
                                    <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                                </div>
                                
                                <!-- Phone -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                    <input type="tel" name="phone"
                                           value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                           placeholder="+91 98765 43210">
                                </div>
                            </div>
                            
                            <!-- Password Change Section -->
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <h3 class="text-lg font-medium text-gray-800 mb-4">Change Password (Optional)</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                        <input type="password" name="current_password"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                        <input type="password" name="new_password"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                        <input type="password" name="confirm_password"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Address Book -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-800">Address Book</h2>
                            <button onclick="toggleAddressForm()" class="bg-emerald-700 hover:bg-emerald-800 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                Add New Address
                            </button>
                        </div>
                        
                        <!-- Add Address Form (Hidden by default) -->
                        <div id="addAddressForm" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
                            <form method="POST">
                                <input type="hidden" name="action" value="add_address">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Label *</label>
                                        <select name="label" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                            <option value="">Select Label</option>
                                            <option value="Home">Home</option>
                                            <option value="Office">Office</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Name *</label>
                                        <input type="text" name="address_name" required
                                               value="<?= htmlspecialchars($currentUser['name']) ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                                        <input type="tel" name="address_phone" required
                                               value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Address Line 1 *</label>
                                        <input type="text" name="address_line_1" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                               placeholder="Street address">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Address Line 2</label>
                                        <input type="text" name="address_line_2"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                               placeholder="Apartment, suite, etc.">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                        <input type="text" name="city" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">State *</label>
                                        <input type="text" name="state" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
                                        <input type="text" name="postal_code" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                        <input type="text" name="country" value="India"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_default" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-700">Set as default address</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mt-4 flex space-x-3">
                                    <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                        Save Address
                                    </button>
                                    <button type="button" onclick="toggleAddressForm()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Existing Addresses -->
                        <div class="space-y-4">
                            <?php if (empty($addresses)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <p>No addresses saved yet</p>
                                <p class="text-sm">Add your first address to make checkout faster</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($addresses as $address): ?>
                            <div class="border border-gray-200 rounded-lg p-4 <?= $address['is_default'] ? 'ring-2 ring-emerald-500 bg-emerald-50' : '' ?>">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $address['is_default'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= htmlspecialchars($address['label']) ?>
                                            </span>
                                            <?php if ($address['is_default']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Default
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="font-medium text-gray-800"><?= htmlspecialchars($address['name']) ?></p>
                                        <p class="text-gray-600"><?= htmlspecialchars($address['phone']) ?></p>
                                        <p class="text-gray-600 mt-1"><?= htmlspecialchars(Address::getFormattedAddress($address)) ?></p>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2 ml-4">
                                        <?php if (!$address['is_default']): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="set_default">
                                            <input type="hidden" name="address_id" value="<?= $address['id'] ?>">
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                                                Set Default
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this address?')">
                                            <input type="hidden" name="action" value="delete_address">
                                            <input type="hidden" name="address_id" value="<?= $address['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Profile Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Profile Summary</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($currentUser['name']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($currentUser['email']) ?></p>
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Saved Addresses</span>
                                    <span class="text-sm font-medium text-gray-800"><?= count($addresses) ?></span>
                                </div>
                                
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Account Type</span>
                                    <span class="text-sm font-medium text-gray-800 capitalize"><?= htmlspecialchars($currentUser['role']) ?></span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Member Since</span>
                                    <span class="text-sm font-medium text-gray-800"><?= date('M Y', strtotime($currentUser['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 space-y-3">
                            <a href="my-orders.php" class="block w-full bg-emerald-700 hover:bg-emerald-800 text-white font-medium py-2 px-4 rounded-lg text-center transition-colors duration-200">
                                View My Orders
                            </a>
                            <a href="index.php" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg text-center transition-colors duration-200">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleAddressForm() {
            const form = document.getElementById('addAddressForm');
            form.classList.toggle('hidden');
        }
    </script>
</body>
</html>