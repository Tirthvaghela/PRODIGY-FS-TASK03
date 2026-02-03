<?php

class ImageUpload {
    
    private static $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    private static $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    /**
     * Upload and process product image
     */
    public static function uploadProductImage($file, $productId = null) {
        // Validate file
        $validation = self::validateImage($file);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = ($productId ? "product_{$productId}_" : "product_") . uniqid() . '.' . strtolower($extension);
        
        // Ensure upload directory exists
        $uploadDir = UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'error' => 'Failed to create upload directory'];
            }
        }
        
        $uploadPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => false, 'error' => 'Failed to upload image'];
        }
        
        // Try to create thumbnail (optional - won't fail if GD not available)
        self::createThumbnail($uploadPath, $uploadDir . 'thumb_' . $filename);
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $uploadPath,
            'url' => UPLOAD_URL . $filename,
            'thumbnail' => UPLOAD_URL . 'thumb_' . $filename
        ];
    }
    
    /**
     * Validate uploaded image
     */
    private static function validateImage($file) {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload error: ' . $file['error']];
        }
        
        // Check file size
        if ($file['size'] > self::$maxFileSize) {
            return ['success' => false, 'error' => 'File too large. Maximum size is 5MB'];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed'];
        }
        
        // Check if it's actually an image (if getimagesize is available)
        if (function_exists('getimagesize')) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['success' => false, 'error' => 'Invalid image file'];
            }
        }
        
        return ['success' => true];
    }
    
    /**
     * Create thumbnail image (optional - gracefully fails if GD not available)
     */
    private static function createThumbnail($sourcePath, $thumbnailPath, $maxWidth = 300, $maxHeight = 300) {
        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            return ['success' => false, 'error' => 'GD extension not available'];
        }
        
        try {
            $imageInfo = getimagesize($sourcePath);
            if ($imageInfo === false) {
                return ['success' => false, 'error' => 'Invalid image'];
            }
            
            list($originalWidth, $originalHeight, $imageType) = $imageInfo;
            
            // Calculate new dimensions
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = intval($originalWidth * $ratio);
            $newHeight = intval($originalHeight * $ratio);
            
            // Create source image
            $sourceImage = null;
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    if (function_exists('imagecreatefromjpeg')) {
                        $sourceImage = imagecreatefromjpeg($sourcePath);
                    }
                    break;
                case IMAGETYPE_PNG:
                    if (function_exists('imagecreatefrompng')) {
                        $sourceImage = imagecreatefrompng($sourcePath);
                    }
                    break;
                case IMAGETYPE_GIF:
                    if (function_exists('imagecreatefromgif')) {
                        $sourceImage = imagecreatefromgif($sourcePath);
                    }
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $sourceImage = imagecreatefromwebp($sourcePath);
                    }
                    break;
                default:
                    return ['success' => false, 'error' => 'Unsupported image type'];
            }
            
            if (!$sourceImage) {
                return ['success' => false, 'error' => 'Failed to create source image'];
            }
            
            // Create thumbnail
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            if (!$thumbnail) {
                imagedestroy($sourceImage);
                return ['success' => false, 'error' => 'Failed to create thumbnail canvas'];
            }
            
            // Preserve transparency for PNG and GIF
            if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Resize image
            $resizeSuccess = imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            if (!$resizeSuccess) {
                imagedestroy($sourceImage);
                imagedestroy($thumbnail);
                return ['success' => false, 'error' => 'Failed to resize image'];
            }
            
            // Save thumbnail
            $success = false;
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    if (function_exists('imagejpeg')) {
                        $success = imagejpeg($thumbnail, $thumbnailPath, 85);
                    }
                    break;
                case IMAGETYPE_PNG:
                    if (function_exists('imagepng')) {
                        $success = imagepng($thumbnail, $thumbnailPath, 8);
                    }
                    break;
                case IMAGETYPE_GIF:
                    if (function_exists('imagegif')) {
                        $success = imagegif($thumbnail, $thumbnailPath);
                    }
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagewebp')) {
                        $success = imagewebp($thumbnail, $thumbnailPath, 85);
                    }
                    break;
            }
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);
            
            return ['success' => $success];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Delete product image and thumbnail
     */
    public static function deleteProductImage($filename) {
        if (empty($filename)) {
            return ['success' => true]; // Nothing to delete
        }
        
        $imagePath = UPLOAD_DIR . $filename;
        $thumbnailPath = UPLOAD_DIR . 'thumb_' . $filename;
        
        $success = true;
        
        // Delete main image
        if (file_exists($imagePath)) {
            $success = unlink($imagePath) && $success;
        }
        
        // Delete thumbnail
        if (file_exists($thumbnailPath)) {
            $success = unlink($thumbnailPath) && $success;
        }
        
        return ['success' => $success];
    }
    
    /**
     * Get image URL with fallback to placeholder
     */
    public static function getImageUrl($filename, $thumbnail = false) {
        if (empty($filename)) {
            return SITE_URL . '/assets/images/placeholder.jpg';
        }
        
        $prefix = $thumbnail ? 'thumb_' : '';
        $imagePath = UPLOAD_DIR . $prefix . $filename;
        
        if (file_exists($imagePath)) {
            return SITE_URL . UPLOAD_URL . $prefix . $filename;
        }
        
        // If thumbnail doesn't exist, try main image
        if ($thumbnail) {
            $mainImagePath = UPLOAD_DIR . $filename;
            if (file_exists($mainImagePath)) {
                return SITE_URL . UPLOAD_URL . $filename;
            }
        }
        
        return SITE_URL . '/assets/images/placeholder.jpg';
    }
}
?>