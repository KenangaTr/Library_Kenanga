<?php
/**
 * PUT /API/api/admin/update.php
 *
 * Memperbarui data admin berdasarkan `id` di dalam JSON body.
 * Jika field `password` tidak disertakan / kosong, password lama tidak berubah.
 * Response Code:
 *   200 OK             → admin berhasil diperbarui
 *   400 Bad Request    → data tidak lengkap
 *   404 Not Found      → admin dengan id tidak ditemukan
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
require_once "../../models/Admin.php";

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
    empty($data->nama_lengkap) ||
    empty($data->username)
) {
    http_response_code(400);
    echo json_encode([
        "message" => "Bad Request. Fields required: id, nama_lengkap, username."
    ]);
    exit;
}

// ── Cek apakah admin dengan id tersebut ada ───────────────────────────────────
$admin = new Admin($db);
$stmt  = $admin->read((int) $data->id);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Admin with id {$data->id} not found."]);
    exit;
}

// ── Set Properti Model ─────────────────────────────────────────────────────────
$admin->id           = (int) $data->id;
$admin->nama_lengkap = $data->nama_lengkap;
$admin->username     = $data->username;
$admin->password     = $data->password ?? "";   // kosong = tidak update password

// ── Eksekusi Update ────────────────────────────────────────────────────────────
if ($admin->update()) {
    http_response_code(200);
    echo json_encode(["message" => "Admin was updated."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to update admin."]);
}
