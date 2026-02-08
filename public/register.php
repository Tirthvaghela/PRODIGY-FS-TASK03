<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/models/User.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        $result = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone' => $phone,
            'address' => $address
        ]);
        
        if ($result['success']) {
            $success = 'Account created successfully! You can now login.';
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Pantry - <?= SITE_NAME ?></title>
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
                    <a href="track-order.php" class="text-gray-500 hover:text-gray-700 font-medium">Track Order</a>
                    <a href="support.php" class="text-gray-500 hover:text-gray-700 font-medium">Support</a>
                </nav>

                <!-- Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <a href="login.php" class="text-emerald-700 font-medium">SIGN IN</a>
                    <a href="cart.php" class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Registration Form -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <!-- User Icon -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-100 rounded-full mb-6">
                    <svg class="w-10 h-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Join the Pantry</h2>
                <p class="text-gray-600">Start sourcing local artisanal goods today.</p>
            </div>
            
            <form method="POST" class="space-y-6">
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                    <?= htmlspecialchars($success) ?>
                    <div class="mt-2">
                        <a href="login.php" class="font-medium underline">Click here to login</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Full Name Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 uppercase tracking-wide mb-2">FULL NAME</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input type="text" name="name" required
                               class="block w-full pl-10 pr-3 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white text-gray-900 placeholder-gray-400"
                               placeholder="John Doe"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                </div>
                
                <!-- Email Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 uppercase tracking-wide mb-2">EMAIL ADDRESS</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input type="email" name="email" required
                               class="block w-full pl-10 pr-3 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white text-gray-900 placeholder-gray-400"
                               placeholder="you@example.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                
                <!-- Phone Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 uppercase tracking-wide mb-2">PHONE NUMBER</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <input type="tel" name="phone"
                               class="block w-full pl-10 pr-3 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white text-gray-900 placeholder-gray-400"
                               placeholder="+91 98765 43210"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                </div>
                
                <!-- Address Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 uppercase tracking-wide mb-2">ADDRESS</label>
                    <div class="relative">
                        <div class="absolute top-4 left-0 pl-3 flex items-start pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <textarea name="address" rows="3"
                                  class="block w-full pl-10 pr-3 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white text-gray-900 placeholder-gray-400 resize-none"
                                  placeholder="Enter your full address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 uppercase tracking-wide mb-2">PASSWORD</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input type="password" name="password" required
                               class="block w-full pl-10 pr-12 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white text-gray-900 placeholder-gray-400"
                               placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <input type="password" name="confirm_password" required
                           class="block w-full px-4 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white text-gray-900 placeholder-gray-400"
                           placeholder="Confirm Password">
                </div>
                
                <!-- Terms and Conditions -->
                <div class="flex items-start">
                    <input id="terms" name="terms" type="checkbox" required
                           class="mt-1 h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                    <label for="terms" class="ml-3 block text-sm text-gray-700">
                        I agree to the <a href="#" class="text-emerald-600 hover:text-emerald-500 font-medium">Terms of Service</a> 
                        and <a href="#" class="text-emerald-600 hover:text-emerald-500 font-medium">Privacy Policy</a>
                    </label>
                </div>
                
                <!-- Create Account Button -->
                <button type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-4 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    Create Account
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </button>
                
                <!-- Login Link -->
                <div class="text-center mt-8">
                    <span class="text-gray-600">Already have an account? </span>
                    <a href="login.php" class="text-emerald-600 hover:text-emerald-500 font-medium">LOGIN</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>