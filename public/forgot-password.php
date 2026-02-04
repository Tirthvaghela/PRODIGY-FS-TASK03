<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/email.php';

$message = '';
$error = '';
$email = $_GET['email'] ?? ''; // Get email from URL parameter
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// If email is provided, automatically send reset email
if (!empty($email) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        try {
            // Check if user exists
            $user = User::getByEmail($email);
            
            if ($user) {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token in database
                $pdo = getPDO();
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $stmt->execute([$resetToken, $resetExpiry, $user['id']]);
                
                // Send reset email
                $resetLink = SITE_URL . "/reset-password.php?token=" . $resetToken;
                $emailSent = EmailService::sendPasswordReset($user, $resetLink);
                
                if ($emailSent) {
                    $message = 'Password reset instructions have been sent to ' . htmlspecialchars($email);
                } else {
                    $error = 'Failed to send reset email. Please try again or contact support.';
                }
            } else {
                // Don't reveal if email exists or not for security
                $message = 'If an account with that email exists, password reset instructions have been sent.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
            error_log('Forgot password error: ' . $e->getMessage());
        }
    }
    
    // If this is an AJAX request (from profile page), return simple response
    if ($isAjax || isset($_GET['ajax'])) {
        if ($message) {
            echo $message;
        } else {
            echo $error;
        }
        exit;
    }
}

// Handle manual form submission (if user wants to enter different email)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Check if user exists
            $user = User::getByEmail($email);
            
            if ($user) {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token in database
                $pdo = getPDO();
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $stmt->execute([$resetToken, $resetExpiry, $user['id']]);
                
                // Send reset email
                $resetLink = SITE_URL . "/reset-password.php?token=" . $resetToken;
                $emailSent = EmailService::sendPasswordReset($user, $resetLink);
                
                if ($emailSent) {
                    $message = 'Password reset instructions have been sent to your email address.';
                } else {
                    $error = 'Failed to send reset email. Please try again or contact support.';
                }
            } else {
                // Don't reveal if email exists or not for security
                $message = 'If an account with that email exists, password reset instructions have been sent.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
            error_log('Forgot password POST error: ' . $e->getMessage());
        }
    }
    
    // If this is an AJAX request, return simple response
    if ($isAjax || isset($_POST['ajax'])) {
        if ($message) {
            echo $message;
        } else {
            echo $error;
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?= SITE_NAME ?></title>
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

    <!-- Forgot Password Form -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <!-- Lock Icon -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-6">
                    <?php if ($message): ?>
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <?php else: ?>
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <?php endif; ?>
                </div>
                <?php if ($message): ?>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Email Sent!</h2>
                <p class="text-gray-600">Check your inbox for password reset instructions.</p>
                <?php else: ?>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Forgot Password?</h2>
                <p class="text-gray-600">No worries! Enter your email and we'll send you reset instructions.</p>
                <?php endif; ?>
            </div>
            
            <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
            
            <div class="text-center space-y-4">
                <p class="text-gray-600">Didn't receive the email? Check your spam folder or try again.</p>
                <button onclick="showEmailForm()" class="text-emerald-600 hover:text-emerald-700 font-medium">
                    Send to a different email address
                </button>
            </div>
            
            <!-- Hidden form for different email -->
            <div id="emailForm" class="hidden mt-6">
                <form method="POST" class="space-y-6">
                    <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>
                    
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
                                   class="block w-full pl-10 pr-3 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-400"
                                   placeholder="Enter your email address"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <!-- Send Reset Button -->
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        Send Reset Instructions
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
            </div>
            
            <?php else: ?>
            <form method="POST" class="space-y-6">
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
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
                               class="block w-full pl-10 pr-3 py-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-400"
                               placeholder="Enter your email address"
                               value="<?= htmlspecialchars($email) ?>">
                    </div>
                </div>
                
                <!-- Send Reset Button -->
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    Send Reset Instructions
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
            <?php endif; ?>
            
            <!-- Back to Login -->
            <div class="text-center mt-8">
                <a href="login.php" class="text-emerald-600 hover:text-emerald-500 font-medium flex items-center justify-center">
                    <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Sign In
                </a>
            </div>
        </div>
    </div>
    <script>
        function showEmailForm() {
            document.getElementById('emailForm').classList.remove('hidden');
        }
    </script>
</body>
</html>