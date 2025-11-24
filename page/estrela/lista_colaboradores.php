<?php
session_start();
if (!isset($_SESSION["is_login"]) || $_SESSION["user"]["role"] !== "*") {
    echo "<p>Acesso negado.</p>";
    exit;
}
?>

<link rel="stylesheet" href="../../css/legacy/fichas_colaboradores.css">

<div class="page-header">
    <h2>Lista de Colaboradores</h2>
    <p>Visualize informações dos colaboradores sob sua supervisão.</p>
</div>

<div class="action-buttons">
    <button id="visualizar_fichas" type="button" class="gestao-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14,2 14,8 20,8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10,9 9,9 8,9"></polyline>
        </svg>
        Visualizar Informação de Colaboradores
    </button>
</div>

<div id="gestao-content">
</div>

<style>
    .action-buttons {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    button {
        padding: 10px 20px;
        cursor: pointer;
    }
</style>
