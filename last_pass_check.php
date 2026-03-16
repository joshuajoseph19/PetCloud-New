<?php
$hash = '$2y$10$QjjpVXblfoK3ILZ2RO1YiunR2vJt/V9sv1fJWNPFeUbczfi6Yynoy';
if (password_verify('admin', $hash)) {
    echo "Password IS 'admin'\n";
} else {
    echo "Password NOT 'admin'\n";
}
?>