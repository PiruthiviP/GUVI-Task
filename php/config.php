<?php
// ─────────────────────────────────────────────
//  config.php  —  MySQL + MongoDB + Redis helpers
// ─────────────────────────────────────────────

// ── MySQL (users: auth only) ──
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'user_app');
define('DB_USER', 'root');
define('DB_PASS', '');           // ← set your MySQL root password here

define('MONGO_URI',  getenv('MONGO_URI'));
define('MONGO_DB',   getenv('MONGO_DB'));
define('MONGO_COLL', getenv('MONGO_COLL'));

// ── Redis (session store) ──
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('SESSION_TTL', 86400);    // 24 hours

// ── Composer autoloader (MongoDB library) ──
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    // Fallback error if composer install hasn't been run
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Run: composer install in the project root.']);
    exit;
}

// ── CORS / JSON headers (sent once here) ──
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ──────────────────────────────────────────────
//  MySQL
// ──────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
             . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ──────────────────────────────────────────────
//  MongoDB  (via official mongodb/mongodb library)
// ──────────────────────────────────────────────
function getMongoDB(): MongoDB\Database {
    static $db = null;
    if ($db === null) {
        $client = new MongoDB\Client(MONGO_URI);
        $db     = $client->selectDatabase(MONGO_DB);
    }
    return $db;
}

function getProfilesCollection(): MongoDB\Collection {
    return getMongoDB()->selectCollection(MONGO_COLL);
}

// ──────────────────────────────────────────────
//  Redis
// ──────────────────────────────────────────────
function getRedis(): Redis {
    static $redis = null;
    if ($redis === null) {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
    }
    return $redis;
}

// ──────────────────────────────────────────────
//  Shared helpers
// ──────────────────────────────────────────────
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getRequestBody(): array {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function validateDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}