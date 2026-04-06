<?php
session_start();

$_SESSION = [];

session_destroy();

header('X-Redirect: /frontend/modules/login/view.html');
exit;
