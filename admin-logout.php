<?php
session_start();
session_destroy();
header('Location: admin-login.html?msg=Logged out successfully');
exit;
