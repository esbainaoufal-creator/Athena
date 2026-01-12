<?php
require_once "core/Auth.php";
Auth::logout();
header("Location: login.php");
exit;