<?php
// /api/helpers.php
declare(strict_types=1);

function api_server_secret(): string {
    return getenv('SERVER_SECRET') ?: '';
}

function api_block4_from_balance(int $balanceCents): string {
    $secret = api_server_secret();
    $input = $secret === '' ? (string)$balanceCents : ($secret . '|' . $balanceCents);
    $bin = hash('sha256', $input, true);
    return strtoupper(bin2hex(substr($bin, 0, 16))); // HEX32
}

function api_is_hex32(string $s): bool {
    return (bool)preg_match('/^[0-9A-Fa-f]{32}$/', $s);
}

// admin auth for API: either session-based admin (web) or X-API-KEY env
function api_require_admin(): void {
    // try session
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') return;

    // try X-API-KEY header
    $headers = getallheaders();
    $key = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? '';
    $expected = getenv('ADMIN_API_KEY') ?: '';
    if ($expected !== '' && hash_equals($expected, (string)$key)) return;

    api_json(['ok'=>false,'msg'=>'admin auth required'], 403);
}
