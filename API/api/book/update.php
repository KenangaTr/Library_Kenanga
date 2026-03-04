<?php
/**
 * PUT /API/api/book/update.php
 *
 * Memperbarui buku yang ada berdasarkan `id` di dalam JSON body.
 * Response Code:
 *   200 OK             → buku berhasil diperbarui
 *   400 Bad Request    → data tidak lengkap
 *   404 Not Found      → buku dengan id tidak ditemukan
 *   503 Service Unavail → koneksi database gagal
 */

// ── Header ─────────────────────────────────────────────────────────────────────
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// ── Validasi Method ────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed. Use PUT."]);
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
    empty($data->id) ||
    empty($data->title) ||
    empty($data->author) ||
    empty($data->published_year) ||
    empty($data->isbn)
) {
    http_response_code(400);
    echo json_encode([
        "message" => "Bad Request. Fields required: id, title, author, published_year, isbn."
    ]);
    exit;
}

// ── Cek apakah buku dengan id tersebut ada ────────────────────────────────────
$book = new Book($db);
$stmt = $book->read((int) $data->id);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Book with id {$data->id} not found."]);
    exit;
}

// ── Set Properti Model ─────────────────────────────────────────────────────────
$book->id             = (int) $data->id;
$book->title          = $data->title;
$book->author         = $data->author;
$book->published_year = (int) $data->published_year;
$book->isbn           = $data->isbn;
$book->stock          = isset($data->stock) ? (int) $data->stock : 0;

// ── Eksekusi Update ────────────────────────────────────────────────────────────
if ($book->update()) {
    http_response_code(200);
    echo json_encode(["message" => "Book was updated."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to update book."]);
}
