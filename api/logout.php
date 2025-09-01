<?php
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Destroi a sessão
session_destroy();

// Redireciona para a página de login
header("Location: ../page/page_login.php"); // ou ajuste se o login estiver noutro local
exit;
