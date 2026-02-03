<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/auth.php';

requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?= SITE_NAME ?></title>
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
            <h1 class="text-3xl font-bold text-gray-800">Settings</h1>
            <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">← Back to Dashboard</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Store Settings -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">Store Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                        <div class="text-gray-900"><?= SITE_NAME ?></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store URL</label>
                        <div class="text-gray-900"><?= SITE_URL ?></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                        <div class="text-gray-900"><?= ADMIN_EMAIL ?></div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">Email Configuration</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Debug Mode</label>
                        <div class="text-gray-900">
                            <?= EMAIL_DEBUG ? 'ON (emails logged to file)' : 'OFF (emails sent)' ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Use MailHog</label>
                        <div class="text-gray-900">
                            <?= (defined('USE_MAILHOG') && USE_MAILHOG) ? 'ON' : 'OFF' ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Use SMTP</label>
                        <div class="text-gray-900">
                            <?= (defined('USE_SMTP') && USE_SMTP) ? 'ON' : 'OFF' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Settings -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">Database Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database Host</label>
                        <div class="text-gray-900"><?= DB_HOST ?></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
                        <div class="text-gray-900"><?= DB_NAME ?></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database User</label>
                        <div class="text-gray-900"><?= DB_USER ?></div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">System Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PHP Version</label>
                        <div class="text-gray-900"><?= phpversion() ?></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Server Software</label>
                        <div class="text-gray-900"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload Max Size</label>
                        <div class="text-gray-900"><?= ini_get('upload_max_filesize') ?></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Memory Limit</label>
                        <div class="text-gray-900"><?= ini_get('memory_limit') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>