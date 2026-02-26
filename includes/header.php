<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bs-primary: #FF88BA;
            --bs-primary-rgb: 255, 136, 186;
            --bs-success: #FF88BA;
            --bs-success-rgb: 255, 136, 186;
            --bs-info: #FF88BA;
            --bs-info-rgb: 255, 136, 186;
        }
        body { 
            background-color: #FFF2D0; 
            color: #444;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card { 
            box-shadow: 0 8px 16px rgba(255, 136, 186, 0.15); 
            border: none; 
            border-radius: 12px;
        }
        .card-header {
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
            background-color: #FF88BA !important;
            color: white !important;
            border-bottom: none;
        }
        .navbar { 
            box-shadow: 0 4px 12px rgba(255, 136, 186, 0.3); 
            background-color: #FF88BA !important;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .btn-primary, .btn-success, .btn-info {
            background-color: #FF88BA;
            border-color: #FF88BA;
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(255, 136, 186, 0.2);
            transition: all 0.3s ease;
        }
        .btn-primary:hover, .btn-success:hover, .btn-info:hover {
            background-color: #ff6ea8;
            border-color: #ff6ea8;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(255, 136, 186, 0.3);
        }
        .btn-secondary {
            background-color: white;
            color: #FF88BA;
            border: 2px solid #FF88BA;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #FF88BA;
            color: white;
            border-color: #FF88BA;
        }
        .table-light {
            background-color: rgba(255, 136, 186, 0.15) !important;
            color: #d8457e;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.6);
        }
        .badge.bg-info {
            background-color: #FF88BA !important;
            color: white;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">📚 Library Kenanga</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="create.php">Add Book</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
