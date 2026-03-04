<?php
/**
 * DELETE /API/api/book/delete.php
 *
 * Menghapus buku berdasarkan `id` di dalam JSON body.
 * Response Code:
 *   200 OK             → buku berhasil dihapus
 *   400 Bad Request    → id tidak disertakan
 *   404 Not Found      → buku dengan id tidak ditemukan
 *   503 Service Unavail → koneksi database gagal
 */

// ── Header ─────────────────────────────────────────────────────────────────────
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// ── Validasi Method ────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed. Use DELETE."]);
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
if (empty($data->id)) {
    http_response_code(400);
    echo json_encode(["message" => "Bad Request. Field required: id."]);
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

// ── Eksekusi Delete ────────────────────────────────────────────────────────────
$book->id = (int) $data->id;

if ($book->delete()) {
    http_response_code(200);
    echo json_encode(["message" => "Book was deleted."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to delete book."]);
}
