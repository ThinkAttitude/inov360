<?php
require_once realpath(__DIR__ . '/../../vendor/autoload.php');

use Dotenv\Dotenv;

// Caminhos das pastas privadas
$localPrivate = '/var/www/private';           // Local (Docker)
$devPrivate  = '/home/drawline/inov360_private/env_dev'; // Develop
$prodPrivate = '/home/inovpt/inov360_private/env_prod'; // Production

// Deteção do ambiente
$runningInDocker = getenv('DOCKER_ENV') || file_exists('/.dockerenv');

if ($runningInDocker && is_dir($localPrivate)) {
    // Ambiente LOCAL (Docker)
    $basePath = $localPrivate;
    $envFile  = file_exists("$basePath/.env.local") ? '.env.local' : '.env';
} elseif (is_dir($prodPrivate)) {
    $basePath = $prodPrivate;
    $envFile  = '.env';
} elseif (is_dir($devPrivate)) {
    $basePath = $devPrivate;
    $envFile  = '.env';
} else {
    http_response_code(500);
    exit('Ficheiro .env não encontrado (nenhuma pasta privada detectada).');
}

// Carrega o .env
$dotenv = Dotenv::createImmutable($basePath, $envFile);
$dotenv->load();

function db_connect() {
    $host    = $_ENV['DB_HOST'];
    $dbname  = $_ENV['DB_NAME'];
    $user    = $_ENV['DB_USER'];
    $pass    = $_ENV['DB_PASS'];
    $charset = $_ENV['DB_CHARSET'];

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success'    => false,
            'message'    => 'Erro ao ligar à base de dados.',
            'erro_debug' => $e->getMessage(),
        ]);
        exit;
    }
}
