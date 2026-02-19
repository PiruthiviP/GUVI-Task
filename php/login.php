<?php
// ─────────────────────────────────────────────
//  php/login.php  —  POST: login  |  POST: logout
//  Session token stored in Redis (not PHP session)
// ─────────────────────────────────────────────
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$body   = getRequestBody();
$action = $body['action'] ?? 'login';

// ──────────────────────────────────────────────
//  LOGOUT
// ──────────────────────────────────────────────
if ($action === 'logout') {
    $token = trim($body['token'] ?? '');
    if ($token) {
        try {
            $redis = getRedis();
            $redis->del('session:' . $token);
        } catch (Exception $e) {
            error_log('logout Redis error: ' . $e->getMessage());
        }
    }
    jsonResponse(['success' => true, 'message' => 'Logged out.']);
}

// ──────────────────────────────────────────────
//  LOGIN
// ──────────────────────────────────────────────
$email    = trim($body['email']    ?? '');
$password =      $body['password'] ?? '';

if (!$email || !$password) {
    jsonResponse(['success' => false, 'message' => 'Email and password are required.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address.'], 400);
}

try {
    $pdo = getDB();

    // ── Fetch user by email (prepared statement) ──
    $stmt = $pdo->prepare(
        'SELECT id, username, email, password FROM users WHERE email = :email LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        // Generic message — don't reveal which field is wrong
        jsonResponse(['success' => false, 'message' => 'Invalid email or password.'], 401);
    }

    // ── Generate a secure session token ──
    $token = bin2hex(random_bytes(32));   // 64-char hex string

    // ── Store session in Redis with TTL ──
    $redis = getRedis();
    $sessionData = json_encode([
        'user_id'  => $user['id'],
        'username' => $user['username'],
        'email'    => $user['email'],
    ]);
    $redis->setex('session:' . $token, SESSION_TTL, $sessionData);

    jsonResponse([
        'success'  => true,
        'token'    => $token,
        'user_id'  => $user['id'],
        'username' => $user['username'],
        'email'    => $user['email'],
    ]);

} catch (PDOException $e) {
    error_log('login.php DB error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error. Please try again.'], 500);
} catch (Exception $e) {
    error_log('login.php error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Server error. Please try again.'], 500);
}