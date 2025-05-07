<?php

use App\Libraries\FirebaseLib;

if (!function_exists('firebase_instance')) {
    /**
     * Dapatkan instance Firebase
     */
    function firebase_instance()
    {
        // Return singleton instance
        static $firebase = null;
        if ($firebase === null) {
            $firebase = new FirebaseLib();
        }
        return $firebase;
    }
}

if (!function_exists('firebase_get')) {
    /**
     * Ambil data dari Firebase
     */
    function firebase_get($path)
    {
        return firebase_instance()->getData($path);
    }
}

if (!function_exists('firebase_set')) {
    /**
     * Set data ke Firebase
     */
    function firebase_set($path, $data)
    {
        return firebase_instance()->setData($path, $data);
    }
}

if (!function_exists('firebase_update')) {
    /**
     * Update data di Firebase
     */
    function firebase_update($path, $data)
    {
        return firebase_instance()->updateData($path, $data);
    }
}