<?php
/**
 * POST /API/api/admin/create.php
 *
 * Membuat akun admin baru dari JSON body.
 * Password akan di-hash dengan bcrypt di dalam model.
 * Response Code:
 *   201 Created        → admin berhasil dibuat
 *   400 Bad Request    → data tidak lengkap atau username sudah digunakan
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
    empty($data->nama_lengkap) ||
    empty($data->username) ||
    empty($data->password)
) {
    http_response_code(400);
    echo json_encode([
        "message" => "Bad Request. Fields required: nama_lengkap, username, password."
    ]);
    exit;
}

// ── Validasi Panjang Password ──────────────────────────────────────────────────
if (strlen($data->password) < 8) {
    http_response_code(400);
    echo json_encode(["message" => "Bad Request. Password must be at least 8 characters."]);
    exit;
}

// ── Set Properti Model ─────────────────────────────────────────────────────────
$admin               = new Admin($db);
$admin->nama_lengkap = $data->nama_lengkap;
$admin->username     = $data->username;
$admin->password     = $data->password;   // di-hash di dalam model->create()

// ── Eksekusi Create ────────────────────────────────────────────────────────────
if ($admin->create()) {
    http_response_code(201);
    echo json_encode([
        "message" => "Admin was created.",
        "id"      => $admin->id,
    ]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to create admin. Username may already exist."]);
}
