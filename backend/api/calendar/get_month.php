<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['is_login']) || empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'UNAUTHENTICATED']);
    exit;
}

function fail(int $status, string $error): void
{
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $error]);
    exit;
}

function valid_date(?string $value): bool
{
    return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
}

function normalize_leave_title(?string $title): string
{
    return match ($title) {
        'licenca_paternidade' => 'Licença de Paternidade',
        'licenca_maternidade' => 'Licença de Maternidade',
        'baixa_medica' => 'Baixa Médica',
        'baixa_seguro' => 'Baixa por Seguro',
        'casamento' => 'Casamento',
        'consulta_medica' => 'Consulta Médica',
        'ferias' => 'Férias',
        'pessoal' => 'Pessoal',
        default => $title ? ucwords(str_replace('_', ' ', $title)) : 'Ausência',
    };
}

function merge_status(?string $current, ?string $next): string
{
    if ($current === 'approved' || $next === 'approved') return 'approved';
    if ($current === 'submitted' || $next === 'submitted') return 'submitted';
    if ($current === 'locked' || $next === 'locked') return 'locked';
    if ($current === 'rejected' || $next === 'rejected') return 'rejected';

    return $next ?: $current ?: 'draft';
}

function day_key(DateTimeInterface $date): string
{
    return $date->format('Y-m-d');
}

function base_day(string $date): array
{
    return [
        'date' => $date,
        'workMin' => 0,
        'oncallMin' => 0,
        'km' => 0,
        'status' => 'draft',
        'leaves' => [],
    ];
}

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

if (!valid_date($from) || !valid_date($to)) {
    fail(400, 'BAD_DATE_RANGE');
}

if ($from > $to) {
    fail(400, 'BAD_DATE_RANGE');
}

$userId = (int)$_SESSION['user']['id'];

try {
    $start = new DateTimeImmutable($from);
    $end = new DateTimeImmutable($to);
    $endExclusive = $end->modify('+1 day');

    $days = [];

    for ($d = $start; $d < $endExclusive; $d = $d->modify('+1 day')) {
        $key = day_key($d);
        $days[$key] = base_day($key);
    }

    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT
            id,
            titulo,
            tipo,
            inicio,
            fim,
            minutos,
            km,
            status,
            leave_request_id
        FROM eventos
        WHERE user_id = :user_id
          AND inicio < :end_exclusive
          AND fim >= :start
        ORDER BY inicio ASC, id ASC
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':start' => $start->format('Y-m-d 00:00:00'),
        ':end_exclusive' => $endExclusive->format('Y-m-d 00:00:00'),
    ]);

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $event) {
        $eventStart = new DateTimeImmutable((string)$event['inicio']);
        $eventEnd = new DateTimeImmutable((string)$event['fim']);

        $eventStartDay = $eventStart < $start ? $start : new DateTimeImmutable($eventStart->format('Y-m-d'));
        $eventEndDay = $eventEnd > $end ? $end : new DateTimeImmutable($eventEnd->format('Y-m-d'));

        for ($d = $eventStartDay; $d <= $eventEndDay; $d = $d->modify('+1 day')) {
            $date = day_key($d);

            if (!isset($days[$date])) {
                continue;
            }

            $days[$date]['status'] = merge_status($days[$date]['status'] ?? null, $event['status'] ?? null);

            if ($event['tipo'] === 'WORK') {
                $days[$date]['workMin'] += (int)($event['minutos'] ?? 0);
            }

            if ($event['tipo'] === 'ONCALL') {
                $days[$date]['oncallMin'] += (int)($event['minutos'] ?? 0);
            }

            if ($event['km'] !== null) {
                $days[$date]['km'] += (float)$event['km'];
            }

            if ($event['tipo'] === 'LEAVE') {
                $days[$date]['leaves'][] = [
                    'id' => (int)$event['id'],
                    'requestId' => $event['leave_request_id'] !== null ? (int)$event['leave_request_id'] : null,
                    'title' => normalize_leave_title($event['titulo'] ?? null),
                    'kind' => 'LEAVE',
                ];
            }
        }
    }

    echo json_encode([
        'success' => true,
        'days' => array_values($days),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'SERVER_ERROR']);
}