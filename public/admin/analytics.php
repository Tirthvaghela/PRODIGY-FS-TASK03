<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/models/Order.php';
require_once __DIR__ . '/../../src/models/Product.php';
require_once __DIR__ . '/../../src/models/User.php';

requireAdmin();

// Get date range from filters
$dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$dateTo = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Get analytics data
$totalRevenue = Order::getTotalRevenue($dateFrom, $dateTo);
$totalOrders = Order::getCount(['date_from' => $dateFrom, 'date_to' => $dateTo]);
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// Get top selling products
$topProducts = Order::getTopSellingProducts(10, $dateFrom, $dateTo);

// Get recent customers
$recentCustomers = User::getRecent(10);

// Get order status breakdown
$ordersByStatus = Order::getOrdersByStatus($dateFrom, $dateTo);

// Get daily sales for chart (last 30 days)
$dailySales = Order::getDailySales(30);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 tracking-wide">ADMIN - ANALYTICS</h1>
                </div>

                <!-- Navigation - Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium">Dashboard</a>
                    <a href="orders.php" class="text-gray-500 hover:text-gray-700 font-medium">Orders</a>
                    <a href="inventory.php" class="text-gray-500 hover:text-gray-700 font-medium">Inventory</a>
                    <a href="../index.php" class="text-gray-500 hover:text-gray-700 font-medium">View Store</a>
                    <a href="../logout.php" class="text-gray-500 hover:text-gray-700 font-medium">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="w-full px-6 py-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Page Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Sales Analytics</h1>
                    <p class="text-gray-600 mt-2">Track your store's performance and growth</p>
                </div>
                
                <!-- Date Range Filter -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <form method="GET" class="flex items-center space-x-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"
                                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"
                                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div class="pt-6">
                            <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg">
                                Apply
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">₹<?= number_format($totalRevenue, 2) ?></p>
                            <p class="text-xs text-green-600 mt-1">Selected period</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalOrders ?></p>
                            <p class="text-xs text-blue-600 mt-1">Orders placed</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Avg Order Value</p>
                            <p class="text-2xl font-bold text-gray-900">₹<?= number_format($avgOrderValue, 2) ?></p>
                            <p class="text-xs text-purple-600 mt-1">Per order</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">New Customers</p>
                            <p class="text-2xl font-bold text-gray-900"><?= count($recentCustomers) ?></p>
                            <p class="text-xs text-orange-600 mt-1">Recent signups</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Data -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Sales Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Daily Sales (Last 30 Days)</h2>
                    <div class="h-64">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Order Status Breakdown -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Status Breakdown</h2>
                    <div class="space-y-4">
                        <?php 
                        $statusColors = [
                            'pending' => 'bg-yellow-500',
                            'processing' => 'bg-blue-500',
                            'shipped' => 'bg-purple-500',
                            'delivered' => 'bg-green-500',
                            'cancelled' => 'bg-red-500'
                        ];
                        $totalStatusOrders = array_sum($ordersByStatus);
                        ?>
                        <?php foreach ($ordersByStatus as $status => $count): ?>
                        <?php $percentage = $totalStatusOrders > 0 ? ($count / $totalStatusOrders) * 100 : 0; ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full <?= $statusColors[$status] ?? 'bg-gray-500' ?> mr-3"></div>
                                <span class="text-sm font-medium text-gray-700 capitalize"><?= $status ?></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="<?= $statusColors[$status] ?? 'bg-gray-500' ?> h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900"><?= $count ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Top Products and Recent Customers -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Selling Products -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Top Selling Products</h2>
                    <?php if (empty($topProducts)): ?>
                    <div class="text-center py-8">
                        <div class="text-4xl mb-2">📦</div>
                        <p class="text-gray-500">No sales data available for the selected period.</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($topProducts as $index => $product): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-emerald-600 font-bold text-sm"><?= $index + 1 ?></span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($product['name']) ?></p>
                                    <p class="text-sm text-gray-500">₹<?= number_format($product['price'], 2) ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900"><?= $product['total_sold'] ?> sold</p>
                                <p class="text-sm text-green-600">₹<?= number_format($product['total_revenue'], 2) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Customers -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Customers</h2>
                    <?php if (empty($recentCustomers)): ?>
                    <div class="text-center py-8">
                        <div class="text-4xl mb-2">👥</div>
                        <p class="text-gray-500">No new customers recently.</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentCustomers as $customer): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-medium text-sm">
                                        <?= strtoupper(substr($customer['name'], 0, 2)) ?>
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($customer['name']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($customer['email']) ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600"><?= date('M j', strtotime($customer['created_at'])) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesData = <?= json_encode(array_values($dailySales)) ?>;
        const salesLabels = <?= json_encode(array_keys($dailySales)) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Daily Sales (₹)',
                    data: salesData,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>