<?php
// ─────────────────────────────────────────────
//  php/register.php  —  POST: create new user
// ─────────────────────────────────────────────
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$body = getRequestBody();

$username = trim($body['username'] ?? '');
$email    = trim($body['email']    ?? '');
$password =      $body['password'] ?? '';

// ── Server-side validation ──
if (!$username || !$email || !$password) {
    jsonResponse(['success' => false, 'message' => 'All fields are required.'], 400);
}

if (strlen($username) < 3) {
    jsonResponse(['success' => false, 'message' => 'Username must be at least 3 characters.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address.'], 400);
}

if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters.'], 400);
}

try {
    $pdo = getDB();

    // ── Check for duplicate username or email (prepared statement) ──
    $stmt = $pdo->prepare(
        'SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1'
    );
    $stmt->execute([':username' => $username, ':email' => $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        jsonResponse(['success' => false, 'message' => 'Username or email already in use.'], 409);
    }

    // ── Hash the password (bcrypt) ──
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // ── Insert user (prepared statement) ──
    $insert = $pdo->prepare(
        'INSERT INTO users (username, email, password) VALUES (:username, :email, :password)'
    );
    $insert->execute([
        ':username' => $username,
        ':email'    => $email,
        ':password' => $hashedPassword,
    ]);

    $newUserId = $pdo->lastInsertId();

    // ── Create an empty profile row for the new user ──
    $profileInsert = $pdo->prepare(
        'INSERT INTO profiles (user_id) VALUES (:user_id)'
    );
    $profileInsert->execute([':user_id' => $newUserId]);

    jsonResponse(['success' => true, 'message' => 'Account created successfully.']);

} catch (PDOException $e) {
    // Don't leak DB details to the client
    error_log('register.php DB error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error. Please try again.'], 500);
} catch (Exception $e) {
    error_log('register.php error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Server error. Please try again.'], 500);
}