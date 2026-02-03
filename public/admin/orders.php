<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/models/Order.php';
require_once __DIR__ . '/../../src/models/User.php';
require_once __DIR__ . '/../../src/email.php';

requireAdmin();

$message = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $orderId = intval($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if ($orderId > 0 && in_array($newStatus, $validStatuses)) {
            // Get current order details before update
            $order = Order::getById($orderId);
            $oldStatus = $order['status'];
            
            if (Order::updateStatus($orderId, $newStatus)) {
                // Get customer details
                $customer = User::getById($order['user_id']);
                
                // Send status update email if status actually changed
                if ($oldStatus !== $newStatus && $customer) {
                    $emailSent = EmailService::sendOrderStatusUpdate($order, $customer, $oldStatus, $newStatus);
                    
                    if ($emailSent) {
                        error_log("Order status update email sent for order: " . $order['order_number']);
                    } else {
                        error_log("Failed to send status update email for order: " . $order['order_number']);
                    }
                }
                
                $message = 'Order status updated successfully!' . ($customer ? ' Customer has been notified via email.' : '');
            } else {
                $error = 'Failed to update order status';
            }
        } else {
            $error = 'Invalid order ID or status';
        }
    }
}

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

// Get orders
$orders = Order::getAll($filters);
$orderStats = Order::getOrderStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 tracking-wide">ADMIN - ORDERS</h1>
                </div>

                <!-- Navigation - Right Side -->
                <div class="ml-auto flex items-center space-x-4">
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium">Dashboard</a>
                    <a href="products.php" class="text-gray-500 hover:text-gray-700 font-medium">Products</a>
                    <a href="../index.php" class="text-gray-500 hover:text-gray-700 font-medium">View Store</a>
                    <a href="../logout.php" class="text-gray-500 hover:text-gray-700 font-medium">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="w-full px-6 py-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Order Management</h1>
                <p class="text-gray-600 mt-2">Manage customer orders and track fulfillment</p>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $orderStats['total_orders'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">₹<?= number_format($orderStats['total_revenue'], 2) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending Orders</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $orderStats['orders_by_status']['pending'] ?? 0 ?></p>
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
                            <p class="text-sm font-medium text-gray-600">This Month</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $orderStats['recent_orders'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $filters['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $filters['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $filters['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>"
                               placeholder="Order number, customer name..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    
                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    
                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    
                    <!-- Filter Button -->
                    <div class="md:col-span-4 flex justify-end space-x-4">
                        <a href="orders.php" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Clear Filters
                        </a>
                        <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Order</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Customer</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Date</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Amount</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Payment</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="text-6xl mb-4">📦</div>
                                    <p class="text-lg font-medium">No orders found</p>
                                    <p>Orders will appear here when customers place them.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-800"><?= htmlspecialchars($order['order_number']) ?></p>
                                        <p class="text-sm text-gray-500">ID: <?= $order['id'] ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-800"><?= htmlspecialchars($order['customer_name']) ?></p>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($order['customer_email']) ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-800"><?= date('M j, Y', strtotime($order['created_at'])) ?></p>
                                        <p class="text-sm text-gray-500"><?= date('g:i A', strtotime($order['created_at'])) ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800">₹<?= number_format($order['total_amount'], 2) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $order['payment_method'] === 'COD' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                        <?= $order['payment_method'] === 'COD' ? 'Cash on Delivery' : $order['payment_method'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'shipped' => 'bg-purple-100 text-purple-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusColor = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewOrder(<?= $order['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                            View
                                        </button>
                                        <button onclick="updateStatus(<?= $order['id'] ?>, '<?= $order['status'] ?>')" 
                                                class="text-emerald-600 hover:text-emerald-800 font-medium text-sm">
                                            Update Status
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 id="orderModalTitle" class="text-2xl font-bold text-gray-800">Order Details</h2>
                        <button onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="orderDetails">
                        <!-- Order details will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Order Status</h3>
                    
                    <form id="statusForm" method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" id="statusOrderId" name="order_id">
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                            <select id="newStatus" name="status" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="closeStatusModal()" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg">
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewOrder(orderId) {
            document.getElementById('orderModalTitle').textContent = `Loading Order Details...`;
            document.getElementById('orderDetails').innerHTML = `
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600 mx-auto mb-4"></div>
                    <p class="text-gray-500">Loading order details...</p>
                </div>
            `;
            document.getElementById('orderModal').classList.remove('hidden');
            
            // Fetch order details via AJAX
            fetch(`order-details-ajax.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    displayOrderDetails(data);
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    document.getElementById('orderDetails').innerHTML = `
                        <div class="text-center py-8">
                            <div class="text-red-500 text-4xl mb-4">⚠️</div>
                            <p class="text-red-600">Error loading order details</p>
                            <p class="text-sm text-gray-500 mt-2">${error.message}</p>
                        </div>
                    `;
                });
        }
        
        function displayOrderDetails(data) {
            const order = data.order;
            const items = data.items;
            const shippingAddress = data.shipping_address;
            
            // Helper function to escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            document.getElementById('orderModalTitle').textContent = `Order ${order.order_number}`;
            
            let itemsHtml = '';
            let totalAmount = 0;
            
            items.forEach(item => {
                totalAmount += parseFloat(item.subtotal);
                itemsHtml += `
                    <div class="flex items-center justify-between py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <img src="${item.product_image ? '../uploads/products/' + escapeHtml(item.product_image) : '../assets/images/placeholder.jpg'}" 
                                 alt="${escapeHtml(item.product_name)}" 
                                 class="w-16 h-16 object-cover rounded-lg mr-4"
                                 onerror="this.src='../assets/images/placeholder.jpg'">
                            <div>
                                <h4 class="font-medium text-gray-800">${escapeHtml(item.product_name)}</h4>
                                <p class="text-sm text-gray-500">₹${parseFloat(item.price).toFixed(2)} × ${item.quantity}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">₹${parseFloat(item.subtotal).toFixed(2)}</p>
                        </div>
                    </div>
                `;
            });
            
            const statusColors = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'processing': 'bg-blue-100 text-blue-800',
                'shipped': 'bg-purple-100 text-purple-800',
                'delivered': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            
            const statusColor = statusColors[order.status] || 'bg-gray-100 text-gray-800';
            
            document.getElementById('orderDetails').innerHTML = `
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Order Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Information</h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Number:</span>
                                <span class="font-medium">${escapeHtml(order.order_number)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium">${new Date(order.created_at).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColor}">
                                    ${escapeHtml(order.status.charAt(0).toUpperCase() + order.status.slice(1))}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-medium">${escapeHtml(order.payment_method === 'COD' ? 'Cash on Delivery' : order.payment_method)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Amount:</span>
                                <span class="font-bold text-lg">₹${parseFloat(order.total_amount).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer & Shipping Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Customer & Shipping</h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">Customer Details</h4>
                                <p class="text-gray-600">${escapeHtml(order.customer_name)}</p>
                                <p class="text-gray-600">${escapeHtml(order.customer_email)}</p>
                            </div>
                            
                            ${shippingAddress ? `
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">Shipping Address</h4>
                                <div class="text-gray-600 text-sm">
                                    <p>${escapeHtml(shippingAddress.name || order.customer_name)}</p>
                                    ${shippingAddress.phone ? `<p>${escapeHtml(shippingAddress.phone)}</p>` : ''}
                                    <p>${escapeHtml(shippingAddress.address)}</p>
                                    <p>${escapeHtml(shippingAddress.city)}, ${escapeHtml(shippingAddress.state)} ${escapeHtml(shippingAddress.pincode)}</p>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Items (${items.length} items)</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        ${itemsHtml}
                        
                        <!-- Order Total -->
                        <div class="pt-4 mt-4 border-t border-gray-300">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-800">Total Amount:</span>
                                <span class="text-xl font-bold text-emerald-600">₹${parseFloat(order.total_amount).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end space-x-4">
                    <button onclick="updateStatus(${order.id}, '${order.status}')" 
                            class="px-6 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg">
                        Update Status
                    </button>
                    <button onclick="closeOrderModal()" 
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Close
                    </button>
                </div>
            `;
        }
        
        function updateStatus(orderId, currentStatus) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('newStatus').value = currentStatus;
            document.getElementById('statusModal').classList.remove('hidden');
        }
        
        function closeOrderModal() {
            document.getElementById('orderModal').classList.add('hidden');
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }
        
        // Close modals when clicking outside
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) closeOrderModal();
        });
        
        document.getElementById('statusModal').addEventListener('click', function(e) {
            if (e.target === this) closeStatusModal();
        });
    </script>
</body>
</html>