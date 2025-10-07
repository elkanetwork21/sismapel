<?php
// Pastikan tidak dieksekusi langsung
// if (!defined('APP_RUNNING')) {
//     exit('No direct script access allowed');
// }

// Gunakan define sekali saja
if (!defined('SECURE_KEY')) {
    define('SECURE_KEY', 'rahasia_gantidenganstringrandom123');
}

/**
 * Encode ID agar tidak langsung terlihat angka
 */
if (!function_exists('secure_id')) {
    function secure_id($id) {
        $id = (int)$id;
        $hash = hash_hmac('sha256', $id, SECURE_KEY);
        return base64_encode($id . ':' . $hash);
    }
}

/**
 * Decode & validasi ID dari URL
 */
if (!function_exists('validate_secure_id')) {
    function validate_secure_id($token) {
        $decoded = base64_decode($token, true);
        if ($decoded === false) return false;

        $parts = explode(':', $decoded);
        if (count($parts) !== 2) return false;

        [$id, $hash] = $parts;
        $expected = hash_hmac('sha256', $id, SECURE_KEY);

        return hash_equals($expected, $hash) ? (int)$id : false;
    }
}
