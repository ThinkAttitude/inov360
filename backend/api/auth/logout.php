<?php
session_start();

$_SESSION = [];

session_destroy();

header("Location: ../../../frontend/page/login.html");
exit;
