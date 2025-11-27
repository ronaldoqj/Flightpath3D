<?php

require_once "controllers/UsersController.php";

// --------------------------------------------------------------------------
// Simple HTTP JSON API to manage users
// Supports actions: list (GET), create (POST), update (POST), delete (POST)
// --------------------------------------------------------------------------

header('Content-Type: application/json; charset=utf-8');

$controller = new UsersController();

// ---------------------------- Response helpers ------------------------------------
function respond($data, int $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function ok($data = null)
{
    $payload = ['ok' => true];
    if ($data !== null) {
        $payload['data'] = $data;
    }

    respond($payload, 200);
}

function error_response(string $message, int $code = 400)
{
    respond(['ok' => false, 'error' => $message], $code);
}


// ---------------------- Request parsing utilities -------------------------
function get_method(): string
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $decoded = json_decode($raw, true);
    
    return is_array($decoded) ? $decoded : [];
}

function build_input(array $json): array
{
    // Merge form POST and JSON body
    return array_merge($_POST, $json);
}

function resolve_action(array $in): ?string
{
    $queryAction = $_GET['action'] ?? null;
    $postAction = $_POST['action'] ?? null;
    $action = $queryAction ?? $postAction;
    if (!$action && isset($in['action'])) {
        $action = $in['action'];
    }

    return $action ? (string)$action : null;
}

function parse_include_deleted(): bool
{
    $param = $_GET['includeDeleted'] ?? $_GET['include_deleted'] ?? null;
    if ($param === null) {
        return false;
    }
    $val = strtolower((string)$param);
    
    return in_array($val, ['1','true','yes','on'], true);
}

function parse_common_params(array $in): array
{
    $id = isset($in['id']) ? (string)$in['id'] : '';
    $name = isset($in['name']) ? trim((string)$in['name']) : '';
    $email = isset($in['email']) ? trim((string)$in['email']) : '';

    return [$id, $name, $email];
}


// ---------------------------- Handlers ------------------------------------
function handle_list(UsersController $controller): void
{
    $includeDeleted = parse_include_deleted();
    $users = array_values($controller->getUsers($includeDeleted));

    ok($users);
}

function handle_create(UsersController $controller, array $in): void
{
    [, $name, $email] = parse_common_params($in);

    if ($name === '' || $email === '') {
        error_response('Required parameters: name, email', 400);
    }

    $ok = $controller->addUser($name, $email);

    if (!$ok) {
        error_response('Email already registered', 409);
    }

    ok();
}

function handle_update(UsersController $controller, array $in): void
{
    [$id, $name, $email] = parse_common_params($in);

    if ($id === '') {
        error_response('Required parameter: id', 400);
    }

    $payload = [];

    if ($name !== '') $payload['name'] = $name;
    if ($email !== '') $payload['email'] = $email;
    if ($payload === []) {
        error_response('Nothing to update', 400);
    }

    $ok = $controller->updateUser($id, $payload);
    
    if (!$ok) {
        error_response('Failed to update (invalid id or email in use)', 400);
    }

    ok();
}

function handle_delete(UsersController $controller, array $in): void
{
    [$id] = parse_common_params($in);

    if ($id === '') {
        error_response('Required parameter: id', 400);
    }

    $ok = $controller->deleteUser($id);

    if (!$ok) {
        error_response('Failed to delete (invalid id)', 400);
    }

    ok();
}

function dispatch_post(UsersController $controller, ?string $action, array $in): void
{
    if (!$action) {
        // By default, POST without action => create
        $action = 'create';
    }

    switch ($action) {
        case 'create':
            handle_create($controller, $in);
            return;
        case 'update':
            handle_update($controller, $in);
            return;
        case 'delete':
            handle_delete($controller, $in);
            return;
        default:
            error_response('Unsupported action', 400);
    }
}


// ---------------------------------------------------------
// ------------------- Request routing ---------------------
// ---------------------------------------------------------

$method = get_method();
$jsonBody = read_json_body();
$in = build_input($jsonBody);
$action = resolve_action($in);

if ($method === 'GET' && (!$action || $action === 'list')) {
    handle_list($controller);
}

if ($method === 'POST') {
    dispatch_post($controller, $action, $in);
}

// Method not allowed
error_response('Method not allowed', 405);
