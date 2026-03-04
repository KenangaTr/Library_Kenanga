<?php

/**
 * REST API Router
 *
 * Entry point for all API requests.
 * Routes are matched by the URL path and the HTTP method.
 *
 * Supported endpoints:
 *
 *   Books:
 *     GET    /API/books           → list all books  (supports ?limit=&offset=)
 *     GET    /API/books/{id}      → get single book
 *     POST   /API/books           → create a book
 *     PUT    /API/books/{id}      → update a book
 *     DELETE /API/books/{id}      → delete a book
 *
 *   Admins:
 *     GET    /API/admins          → list all admins
 *     GET    /API/admins/{id}     → get single admin
 *     POST   /API/admins          → create an admin
 *     PUT    /API/admins/{id}     → update an admin
 *     DELETE /API/admins/{id}     → delete an admin
 */

// ─── Global API headers ────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─── Autoload dependencies ─────────────────────────────────────────────────────
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/helpers/Response.php';
require_once __DIR__ . '/Book.php';
require_once __DIR__ . '/Admin.php';

// ─── Route parsing ─────────────────────────────────────────────────────────────

/**
 * Resolve the URI path to a [resource, id] pair.
 * Strips the /API prefix so routing works whether the folder is accessed
 * as /API or as a subfolder (e.g. /Library_Kenanga/API).
 *
 * Example: /API/books/3  →  ['books', 3]
 *          /API/admins   →  ['admins', null]
 */
$requestUri    = $_SERVER['REQUEST_URI'];
$parsedPath    = parse_url($requestUri, PHP_URL_PATH);    // strip query string
$parsedPath    = rtrim($parsedPath, '/');                 // normalise trailing slash

// Remove the application prefix up to and including /API
$parsedPath    = preg_replace('#^.*/API#i', '', $parsedPath);  // → /books/3
$segments      = array_values(array_filter(explode('/', $parsedPath)));

$resource = strtolower($segments[0] ?? '');               // 'books' | 'admins'
$id       = isset($segments[1]) ? (int) $segments[1] : null;
$method   = $_SERVER['REQUEST_METHOD'];

// ─── Dispatch ─────────────────────────────────────────────────────────────────
match (true) {
    $resource === 'books'  => handleBooks($method, $id),
    $resource === 'admins' => handleAdmins($method, $id),
    default                => Response::error('Endpoint not found.', 404),
};


// ══════════════════════════════════════════════════════════════════════════════
// Book handlers
// ══════════════════════════════════════════════════════════════════════════════

function handleBooks(string $method, ?int $id): void
{
    $book = new Book();

    switch ($method) {

        // ── GET /books  or  GET /books/{id} ───────────────────────────────────
        case 'GET':
            if ($id !== null) {
                // Single book
                $data = $book->getById($id);
                if ($data === false) {
                    Response::error("Book with ID {$id} not found.", 404);
                }
                Response::success($data);
            }

            // All books — optional pagination: ?limit=10&offset=0
            $limit  = isset($_GET['limit'])  ? (int) $_GET['limit']  : 0;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
            $books  = $book->getAll($limit, $offset);
            $total  = $book->count();

            Response::success([
                'total'  => $total,
                'limit'  => $limit,
                'offset' => $offset,
                'books'  => $books,
            ]);

        // ── POST /books ────────────────────────────────────────────────────────
        case 'POST':
            $body = getJsonBody();

            $errors = validateBook($body);
            if (!empty($errors)) {
                Response::error('Validation failed.', 422, $errors);
            }

            // ISBN uniqueness check
            if ($book->isbnExists($body['isbn'])) {
                Response::error("ISBN '{$body['isbn']}' is already in use.", 409);
            }

            $book->title          = trim($body['title']);
            $book->author         = trim($body['author']);
            $book->published_year = (int) $body['published_year'];
            $book->isbn           = trim($body['isbn']);
            $book->stock          = (int) ($body['stock'] ?? 0);

            if (!$book->create()) {
                Response::error('Failed to create book.', 500);
            }

            Response::success($book->getById($book->id), 201, 'Book created successfully.');

        // ── PUT /books/{id} ────────────────────────────────────────────────────
        case 'PUT':
            if ($id === null) {
                Response::error('Book ID is required for update.', 400);
            }

            $existing = $book->getById($id);
            if ($existing === false) {
                Response::error("Book with ID {$id} not found.", 404);
            }

            $body = getJsonBody();

            $errors = validateBook($body);
            if (!empty($errors)) {
                Response::error('Validation failed.', 422, $errors);
            }

            // ISBN uniqueness check (excluding current record)
            if ($book->isbnExists($body['isbn'], $id)) {
                Response::error("ISBN '{$body['isbn']}' is already in use by another book.", 409);
            }

            $book->id             = $id;
            $book->title          = trim($body['title']);
            $book->author         = trim($body['author']);
            $book->published_year = (int) $body['published_year'];
            $book->isbn           = trim($body['isbn']);
            $book->stock          = (int) ($body['stock'] ?? $existing['stock']);

            if (!$book->update()) {
                Response::error('Failed to update book.', 500);
            }

            Response::success($book->getById($id), 200, 'Book updated successfully.');

        // ── DELETE /books/{id} ─────────────────────────────────────────────────
        case 'DELETE':
            if ($id === null) {
                Response::error('Book ID is required for deletion.', 400);
            }

            if ($book->getById($id) === false) {
                Response::error("Book with ID {$id} not found.", 404);
            }

            if (!$book->delete($id)) {
                Response::error('Failed to delete book.', 500);
            }

            Response::success(null, 200, "Book with ID {$id} deleted successfully.");

        default:
            Response::error("Method {$method} not allowed.", 405);
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// Admin handlers
// ══════════════════════════════════════════════════════════════════════════════

function handleAdmins(string $method, ?int $id): void
{
    $admin = new Admin();

    switch ($method) {

        // ── GET /admins  or  GET /admins/{id} ─────────────────────────────────
        case 'GET':
            if ($id !== null) {
                $data = $admin->getById($id);
                if ($data === false) {
                    Response::error("Admin with ID {$id} not found.", 404);
                }
                Response::success($data);
            }

            $limit  = isset($_GET['limit'])  ? (int) $_GET['limit']  : 0;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
            $admins = $admin->getAll($limit, $offset);
            $total  = $admin->count();

            Response::success([
                'total'  => $total,
                'limit'  => $limit,
                'offset' => $offset,
                'admins' => $admins,
            ]);

        // ── POST /admins ───────────────────────────────────────────────────────
        case 'POST':
            $body = getJsonBody();

            $errors = validateAdmin($body, isCreate: true);
            if (!empty($errors)) {
                Response::error('Validation failed.', 422, $errors);
            }

            if ($admin->usernameExists($body['username'])) {
                Response::error("Username '{$body['username']}' is already taken.", 409);
            }

            $admin->nama_lengkap = trim($body['nama_lengkap']);
            $admin->username     = trim($body['username']);
            $admin->password     = $body['password'];   // will be hashed in create()

            if (!$admin->create()) {
                Response::error('Failed to create admin.', 500);
            }

            Response::success($admin->getById($admin->id), 201, 'Admin created successfully.');

        // ── PUT /admins/{id} ───────────────────────────────────────────────────
        case 'PUT':
            if ($id === null) {
                Response::error('Admin ID is required for update.', 400);
            }

            if ($admin->getById($id) === false) {
                Response::error("Admin with ID {$id} not found.", 404);
            }

            $body = getJsonBody();

            $errors = validateAdmin($body, isCreate: false);
            if (!empty($errors)) {
                Response::error('Validation failed.', 422, $errors);
            }

            if ($admin->usernameExists($body['username'], $id)) {
                Response::error("Username '{$body['username']}' is already taken by another admin.", 409);
            }

            $admin->id           = $id;
            $admin->nama_lengkap = trim($body['nama_lengkap']);
            $admin->username     = trim($body['username']);
            $admin->password     = $body['password'] ?? '';  // empty = keep old password

            if (!$admin->update()) {
                Response::error('Failed to update admin.', 500);
            }

            Response::success($admin->getById($id), 200, 'Admin updated successfully.');

        // ── DELETE /admins/{id} ────────────────────────────────────────────────
        case 'DELETE':
            if ($id === null) {
                Response::error('Admin ID is required for deletion.', 400);
            }

            if ($admin->getById($id) === false) {
                Response::error("Admin with ID {$id} not found.", 404);
            }

            if (!$admin->delete($id)) {
                Response::error('Failed to delete admin.', 500);
            }

            Response::success(null, 200, "Admin with ID {$id} deleted successfully.");

        default:
            Response::error("Method {$method} not allowed.", 405);
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// Shared utility functions
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Parse the JSON request body.
 * Returns an associative array; responds with 400 if the body is invalid JSON.
 *
 * @return array<string, mixed>
 */
function getJsonBody(): array
{
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        Response::error('Invalid JSON body: ' . json_last_error_msg(), 400);
    }

    return $data ?? [];
}

/**
 * Validate book input fields.
 *
 * @param array<string, mixed> $data
 * @return array<string, string> Associative array of field → error message.
 */
function validateBook(array $data): array
{
    $errors = [];

    if (empty($data['title']) || !is_string($data['title'])) {
        $errors['title'] = 'Title is required and must be a string.';
    } elseif (strlen($data['title']) > 255) {
        $errors['title'] = 'Title must not exceed 255 characters.';
    }

    if (empty($data['author']) || !is_string($data['author'])) {
        $errors['author'] = 'Author is required and must be a string.';
    } elseif (strlen($data['author']) > 255) {
        $errors['author'] = 'Author must not exceed 255 characters.';
    }

    if (!isset($data['published_year']) || !is_numeric($data['published_year'])) {
        $errors['published_year'] = 'Published year is required and must be a number.';
    } elseif ((int) $data['published_year'] < 1000 || (int) $data['published_year'] > (int) date('Y')) {
        $errors['published_year'] = 'Published year must be a valid 4-digit year.';
    }

    if (empty($data['isbn']) || !is_string($data['isbn'])) {
        $errors['isbn'] = 'ISBN is required and must be a string.';
    } elseif (strlen($data['isbn']) > 20) {
        $errors['isbn'] = 'ISBN must not exceed 20 characters.';
    }

    if (isset($data['stock']) && (!is_numeric($data['stock']) || (int) $data['stock'] < 0)) {
        $errors['stock'] = 'Stock must be a non-negative integer.';
    }

    return $errors;
}

/**
 * Validate admin input fields.
 *
 * @param array<string, mixed> $data
 * @param bool                 $isCreate True when creating (password mandatory).
 * @return array<string, string>
 */
function validateAdmin(array $data, bool $isCreate = true): array
{
    $errors = [];

    if (empty($data['nama_lengkap']) || !is_string($data['nama_lengkap'])) {
        $errors['nama_lengkap'] = 'Full name (nama_lengkap) is required.';
    } elseif (strlen($data['nama_lengkap']) > 100) {
        $errors['nama_lengkap'] = 'Full name must not exceed 100 characters.';
    }

    if (empty($data['username']) || !is_string($data['username'])) {
        $errors['username'] = 'Username is required.';
    } elseif (strlen($data['username']) > 50) {
        $errors['username'] = 'Username must not exceed 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $data['username'])) {
        $errors['username'] = 'Username may only contain letters, numbers, dots, underscores, and dashes.';
    }

    // Password is mandatory on create; optional on update (keep old if omitted)
    if ($isCreate) {
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
    } elseif (!empty($data['password']) && strlen($data['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }

    return $errors;
}
