<?php
/**
 * GET /API/api/book/read.php
 * GET /API/api/book/read.php?id={id}
 *
 * Mengambil semua buku atau satu buku berdasarkan query param `id`.
 * Response Code:
 *   200 OK             → data ditemukan
 *   404 Not Found      → buku dengan id tidak ditemukan (single read)
 *   503 Service Unavail → koneksi database gagal
 */

// ── Header ─────────────────────────────────────────────────────────────────────
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// ── Validasi Method ────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed. Use GET."]);
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

// ── Proses Request ─────────────────────────────────────────────────────────────
$book = new Book($db);

// Jika ada query param `id`, ambil satu buku
if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $id   = (int) $_GET["id"];
    $stmt = $book->read($id);
    $row  = $stmt->fetch();

    if ($row) {
        http_response_code(200);
        echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Book with id {$id} not found."]);
    }
    exit;
}

// Ambil semua buku
$stmt = $book->read();
$books_arr = ["records" => []];

while ($row = $stmt->fetch()) {
    $books_arr["records"][] = $row;
}

http_response_code(200);
echo json_encode($books_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
