<?php
require_once '../Classes/Admin.php';
require_once '../Classes/User.php';
use DELIVERY\Admin\Admin;
use DELIVERY\Database;

$admin = new Admin("Johann @Thant Zin Aung Hla", "21", "YoPae", "johannhla7777@gmail.com", "lusoelay");
$admin->CreateUser("LuSoe Lay", "21", "YoPae", "tzah2003@gmail.com", "koyolay");