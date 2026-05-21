<?php
declare(strict_types=1);

/**
 * Centralized API response helpers + message catalog.
 *
 * Message catalog lives in `messages.json` (same folder). Edit there to change
 * any user-facing message without touching endpoint code.
 *
 * Usage:
 *   require_once __DIR__ . '/../lib/helper/responses.php';
 *   json_error('UNAUTHENTICATED', 401);
 *   json_error('EMAIL_IN_USE');               // status 200 by default
 *   json_error('DATE_INVALID', 200, ['field' => 'data_inicio']);
 *
 * Response shape (chosen in PR #71 discussion):
 *   { "ok": true,  ...payload }
 *   { "ok": false, "error": "mensagem em PT", ...extra }
 */

/**
 * Load + cache the message catalog. Looks up by exact code across all sections.
 * If the code isn't found, returns the code itself (so it stays visible during
 * development and easy to spot missing entries).
 */
function get_message(string $code, array $params = []): string
{
    static $flat = null;

    if ($flat === null) {
        $path = __DIR__ . '/messages.json';
        $flat = [];
        if (is_file($path)) {
            $raw = file_get_contents($path);
            $catalog = json_decode($raw, true);
            if (is_array($catalog)) {
                foreach ($catalog as $section) {
                    if (is_array($section)) {
                        foreach ($section as $key => $value) {
                            if (is_string($value)) {
                                $flat[$key] = $value;
                            }
                        }
                    }
                }
            }
        }
    }

    $message = $flat[$code] ?? $code;

    if ($params) {
        foreach ($params as $name => $value) {
            $message = str_replace('{' . $name . '}', (string)$value, $message);
        }
    }

    return $message;
}

/**
 * Emit a JSON error response and exit.
 *
 * @param string   $codeOrMessage Either a catalog code (resolved via inov_msg)
 *                                or a literal Portuguese message.
 * @param int|null $status        HTTP status. When null (default) the current
 *                                http_response_code() is preserved, which
 *                                matches the existing pattern of setting the
 *                                status on a previous statement. Pass an int
 *                                to explicitly set the status here.
 * @param array    $extra         Extra fields merged into the JSON body.
 */
function json_error(string $codeOrMessage, ?int $status = null, array $extra = []): void
{
    if (!headers_sent()) {
        if ($status !== null) {
            http_response_code($status);
        }
        header('Content-Type: application/json; charset=utf-8');
    }
    $message = get_message($codeOrMessage);
    echo json_encode(['ok' => false, 'error' => $message] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}
