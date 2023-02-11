<?php
session_start([
        'use_only_cookies' => 1,
        'cookie_lifetime' => 0,
        'cookie_secure' => 1,
        'cookie_httponly' => 1
    ]);
session_unset();
session_destroy();
header('Location: ./index.php');
?>