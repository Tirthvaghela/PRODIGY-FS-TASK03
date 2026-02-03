<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/models/User.php';
require_once __DIR__ . '/../../src/models/Order.php';

requireAdmin();

// Get all customers (non-admin users)
$customers = User::getAll(['role' => 'customer']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">

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
<body class="bg-gray-50">
    <?php include __DIR__ . '/../../src/includes/admin-header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Customers</h1>
            <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">← Back to Dashboard</a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <?php if (empty($customers)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No customers yet</h3>
                <p class="mt-1 text-sm text-gray-500">Customers will appear here when they register on your store.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Customer</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Contact</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Registered</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Orders</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($customers as $customer): ?>
                        <?php
                        $orderCount = Order::getCountByUser($customer['id']);
                        $registeredDate = date('M j, Y', strtotime($customer['created_at']));
                        ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                        <span class="text-emerald-600 font-medium text-sm">
                                            <?= strtoupper(substr($customer['name'], 0, 2)) ?>
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($customer['name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($customer['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($customer['phone'] ?? 'Not provided') ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($customer['address'] ?? 'No address') ?></div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="text-sm text-gray-900"><?= $registeredDate ?></div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    <?= $orderCount ?> orders
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Active
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Summary Stats -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-blue-600 text-sm font-medium">Total Customers</div>
                    <div class="text-2xl font-bold text-blue-900"><?= count($customers) ?></div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-green-600 text-sm font-medium">Active Customers</div>
                    <div class="text-2xl font-bold text-green-900"><?= count($customers) ?></div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="text-purple-600 text-sm font-medium">New This Month</div>
                    <div class="text-2xl font-bold text-purple-900">
                        <?php
                        $thisMonth = date('Y-m');
                        $newThisMonth = array_filter($customers, function($customer) use ($thisMonth) {
                            return strpos($customer['created_at'], $thisMonth) === 0;
                        });
                        echo count($newThisMonth);
                        ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>