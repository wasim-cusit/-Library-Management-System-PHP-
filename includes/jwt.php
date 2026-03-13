<?php
/**
 * Simple JWT encode/decode for API auth (HMAC SHA-256).
 */
function jwt_encode(array $payload): string {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload['iat'] = time();
    if (!isset($payload['exp'])) {
        $payload['exp'] = time() + (defined('JWT_EXPIRY_SECONDS') ? JWT_EXPIRY_SECONDS : 604800);
    }
    $payload = json_encode($payload);
    $b64h = strtr(base64_encode($header), '+/', '-_');
    $b64p = strtr(base64_encode($payload), '+/', '-_');
    $sig = hash_hmac('sha256', $b64h . '.' . $b64p, JWT_SECRET, true);
    $b64s = strtr(base64_encode($sig), '+/', '-_');
    return $b64h . '.' . $b64p . '.' . rtrim($b64s, '=');
}

function jwt_decode(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    $sig = base64_decode(strtr($parts[2], '-_', '+/') . str_repeat('=', (4 - strlen($parts[2]) % 4) % 4));
    if (hash_hmac('sha256', $parts[0] . '.' . $parts[1], JWT_SECRET, true) !== $sig) return null;
    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    if (!$payload || (isset($payload['exp']) && $payload['exp'] < time())) return null;
    return $payload;
}
