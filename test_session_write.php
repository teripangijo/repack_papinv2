<?php
define('APPPATH', __DIR__ . '/application/'); // Sesuaikan jika struktur Anda berbeda
$session_path = APPPATH . 'session/';

echo "Session path: " . $session_path . "<br>";

if (!is_dir($session_path)) {
    echo "Directory DOES NOT EXIST: " . $session_path . "<br>";
    echo "Please create it manually.<br>";
} else {
    echo "Directory EXISTS: " . $session_path . "<br>";
    if (is_writable($session_path)) {
        echo "Directory IS WRITABLE.<br>";
        $test_file = $session_path . 'test_write.txt';
        if (@file_put_contents($test_file, 'test content')) {
            echo "Successfully WROTE to test_write.txt in session path.<br>";
            @unlink($test_file); // Hapus file tes
        } else {
            echo "FAILED to write to session path. Check permissions carefully or PHP error logs for 'permission denied'.<br>";
        }
    } else {
        echo "Directory IS NOT WRITABLE. Please check permissions.<br>";
    }
}
?>