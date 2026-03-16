<?php
/**
 * Cloudinary Upload Helper
 * 
 * Uploads a file to Cloudinary using the unsigned Upload API.
 * Set these in Render's Environment Variables:
 *   CLOUDINARY_CLOUD_NAME  - your Cloudinary cloud name
 *   CLOUDINARY_UPLOAD_PRESET - your unsigned upload preset name
 *
 * Returns the secure URL of the uploaded image, or false on failure.
 */
function uploadToCloudinary($fileTmpPath, $folder = 'petcloud')
{
    $cloudName    = getenv('CLOUDINARY_CLOUD_NAME');
    $uploadPreset = getenv('CLOUDINARY_UPLOAD_PRESET');

    if (!$cloudName || !$uploadPreset) {
        error_log("Cloudinary env vars not set. Cloud: $cloudName, Preset: $uploadPreset");
        return false;
    }

    $apiUrl = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

    $postData = [
        'file'           => new CURLFile($fileTmpPath),
        'upload_preset'  => $uploadPreset,
        'folder'         => $folder,
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("Cloudinary cURL error: $error");
        return false;
    }

    $result = json_decode($response, true);
    if (isset($result['secure_url'])) {
        return $result['secure_url'];
    }

    error_log("Cloudinary upload failed: " . $response);
    return false;
}
