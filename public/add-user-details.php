<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/User.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$message = '';

// Add sample data automatically
if (empty($currentUser['phone']) || empty($currentUser['address'])) {
    $result = User::updateProfile($currentUser['id'], [
        'name' => $currentUser['name'],
        'phone' => '+91 98765 43210',
        'address' => '123 Main Street, Ahmedabad, Gujarat 380001, India'
    ]);
    
    if ($result) {
        $message = "✅ Sample phone and address added to your profile!";
    } else {
        $message = "❌ Failed to update profile";
    }
} else {
    $message = "✅ Your profile already has phone and address data";
}

// Redirect to checkout after 2 seconds
header("refresh:2;url=checkout.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adding User Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
        <div class="mb-6">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Updating Profile</h1>
            <p class="text-gray-600"><?= $message ?></p>
        </div>
        
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-emerald-700">
                <strong>Added Details:</strong><br>
                Phone: +91 98765 43210<br>
                Address: 123 Main Street, Ahmedabad, Gujarat 380001, India
            </p>
        </div>
        
        <div class="text-sm text-gray-500 mb-4">
            Redirecting to checkout in 2 seconds...
        </div>
        
        <a href="checkout.php" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
            Go to Checkout Now
        </a>
    </div>
</body>
</html>