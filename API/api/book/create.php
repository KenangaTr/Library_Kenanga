<?php
/**
 * POST /API/api/book/create.php
 *
 * Membuat buku baru dari JSON body.
 * Response Code:
 *   201 Created        → buku berhasil dibuat
 *   400 Bad Request    → data tidak lengkap atau input tidak valid
 *   503 Service Unavail → koneksi database gagal
 */

// ── Header ─────────────────────────────────────────────────────────────────────
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// ── Validasi Method ────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed. Use POST."]);
    exit;
}

// ── Autoload ───────────────────────────────────────────────────────────────────
require_once "../../config/Database.php";
require_once "../../models/Book.php";

// ── Koneksi Database ───────────────────────────────────────────────────────────
$database = new Database();
$db       = $database->getConnection();

if ($db === null) {
    http_response_code(503);
    echo json_encode(["message" => "Service Unavailable. Database connection failed."]);
    exit;
}

// ── Baca Body JSON ─────────────────────────────────────────────────────────────
$data = json_decode(file_get_contents("php://input"));

// ── Validasi Field Wajib ───────────────────────────────────────────────────────
if (
    empty($data->title) ||
    empty($data->author) ||
    empty($data->published_year) ||
    empty($data->isbn)
) {
    http_response_code(400);
    echo json_encode([
        "message" => "Bad Request. Fields required: title, author, published_year, isbn."
    ]);
    exit;
}

// ── Set Properti Model ─────────────────────────────────────────────────────────
$book                 = new Book($db);
$book->title          = $data->title;
$book->author         = $data->author;
$book->published_year = (int) $data->published_year;
$book->isbn           = $data->isbn;
$book->stock          = isset($data->stock) ? (int) $data->stock : 0;

// ── Eksekusi Create ────────────────────────────────────────────────────────────
if ($book->create()) {
    http_response_code(201);
    echo json_encode([
        "message" => "Book was created.",
        "id"      => $book->id,
    ]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to create book. ISBN may already exist."]);
}
