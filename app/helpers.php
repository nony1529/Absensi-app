<?php

if (!function_exists('formatTime')) {
    function formatTime($time) {
        if (empty($time)) {
            return null;
        }

        // Jika sudah dalam format H:i:s, langsung return
        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        
        // Jika dalam format datetime, ambil bagian waktu saja
        if (preg_match('/^\d{4}-\d{2}-\d{2} (\d{1,2}:\d{2}:\d{2})$/', $time, $matches)) {
            return $matches[1];
        }
        
        // Untuk format lainnya, ambil 8 karakter pertama
        return substr($time, 0, 8);
    }
}