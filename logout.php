<?php
require 'config.php';
$_SESSION = [];
session_destroy();
session_start();
flash('ออกจากระบบแล้ว');
redirect('index.php');
