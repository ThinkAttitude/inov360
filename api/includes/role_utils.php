<?php
// api/includes/role_utils.php
declare(strict_types=1);

/**
 * Normaliza papéis/roles para um formato padrão
 * Aceita tanto a convenção documental (admin_rh) como a usada no código (adminrh)
 */
function normalize_role(string $role): string {
    return match (strtolower(trim($role))) {
        'admin_rh' => 'adminrh',
        'adminrh' => 'adminrh',
        default => strtolower(trim($role)),
    };
}

/**
 * Verifica se um papel tem permissão para uma ação específica
 */
function has_permission(string $user_role, array $allowed_roles): bool {
    $normalized_user_role = normalize_role($user_role);
    $normalized_allowed = array_map('normalize_role', $allowed_roles);
    return in_array($normalized_user_role, $normalized_allowed, true);
}

/**
 * Envia resposta JSON de erro padronizada
 */
function send_json_error(int $status_code, string $error_code, string $message = ''): void {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => false, 'error' => $error_code];
    if ($message !== '') {
        $response['message'] = $message;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}