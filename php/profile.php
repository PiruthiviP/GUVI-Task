<?php
// ─────────────────────────────────────────────
//  php/profile.php  —  GET: load  |  POST: update
//  Profiles stored in MongoDB. Token validated via Redis.
// ─────────────────────────────────────────────
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ──────────────────────────────────────────────
//  GET — return profile data from MongoDB
// ──────────────────────────────────────────────
if ($method === 'GET') {
    $token  = trim($_GET['token']   ?? '');
    $userId = trim($_GET['user_id'] ?? '');

    $session = validateToken($token, $userId);
    if (!$session) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    try {
        $col = getProfilesCollection();
        $doc = $col->findOne(['user_id' => $userId]);

        $profile = null;
        if ($doc) {
            $profile = [
                'full_name' => $doc['full_name'] ?? '',
                'age'       => $doc['age']       ?? '',
                'dob'       => $doc['dob']       ?? '',
                'contact'   => $doc['contact']   ?? '',
                'address'   => $doc['address']   ?? '',
            ];
        }

        jsonResponse(['success' => true, 'profile' => $profile ?? (object)[]]);

    } catch (Exception $e) {
        error_log('profile.php GET MongoDB error: ' . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error.'], 500);
    }
}

// ──────────────────────────────────────────────
//  POST — upsert profile in MongoDB
// ──────────────────────────────────────────────
if ($method === 'POST') {
    $body   = getRequestBody();
    $token  = trim($body['token']   ?? '');
    $userId = trim($body['user_id'] ?? '');

    $session = validateToken($token, $userId);
    if (!$session) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $fullName = trim($body['full_name'] ?? '');
    $age      = isset($body['age']) && $body['age'] !== '' ? (int)$body['age'] : null;
    $dob      = trim($body['dob']     ?? '') ?: null;
    $contact  = trim($body['contact'] ?? '');
    $address  = trim($body['address'] ?? '');

    if ($age !== null && ($age < 1 || $age > 120)) {
        jsonResponse(['success' => false, 'message' => 'Age must be between 1 and 120.'], 400);
    }
    if ($dob && !validateDate($dob)) {
        jsonResponse(['success' => false, 'message' => 'Invalid date of birth format.'], 400);
    }

    try {
        $col = getProfilesCollection();
        $col->updateOne(
            ['user_id' => $userId],
            ['$set' => [
                'user_id'    => $userId,
                'username'   => $session['username'],
                'email'      => $session['email'],
                'full_name'  => $fullName ?: null,
                'age'        => $age,
                'dob'        => $dob,
                'contact'    => $contact ?: null,
                'address'    => $address ?: null,
                'updated_at' => date('c'),
            ]],
            ['upsert' => true]
        );

        jsonResponse(['success' => true, 'message' => 'Profile updated successfully.']);

    } catch (Exception $e) {
        error_log('profile.php POST MongoDB error: ' . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error.'], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);

function validateToken(string $token, string $userId): array|false {
    if (!$token) return false;
    try {
        $redis = getRedis();
        $raw   = $redis->get('session:' . $token);
        if (!$raw) return false;

        $session = json_decode($raw, true);
        if (!is_array($session)) return false;

        if ($userId && (string)$session['user_id'] !== (string)$userId) {
            return false;
        }

        $redis->expire('session:' . $token, SESSION_TTL);
        return $session;

    } catch (Exception $e) {
        error_log('validateToken Redis error: ' . $e->getMessage());
        return false;
    }
}