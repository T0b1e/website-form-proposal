<?php
require_once '../src/controllers/AuthController.php';

$auth = new AuthController();
$auth->logout();
?>
