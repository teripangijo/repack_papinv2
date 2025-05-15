<?php
// Ganti "PasswordAdminRahasiaAnda" dengan password yang Anda inginkan untuk admin
$passwordAsli = "Admin1234"; 

$hashPassword = password_hash($passwordAsli, PASSWORD_DEFAULT);

echo "Password Asli: " . htmlspecialchars($passwordAsli) . "<br>";
echo "Hash Password: " . htmlspecialchars($hashPassword);

// Untuk keamanan, hapus file ini setelah Anda mendapatkan hash-nya.
?>