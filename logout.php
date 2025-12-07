<?php
require_once __DIR__ . '/auth_helpers.php';
logout_current_user();
header('Location: login.php');
exit;
