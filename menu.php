<?php
// menu.php
?>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?page=books">BookCrossing</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="ms-auto d-flex">
                <span class="navbar-text me-3">
                    <?= htmlspecialchars($_SESSION['firstname']) ?>
                </span>
                <a class="btn btn-outline-light btn-sm" href="auth.php?logout=1">Выйти</a>
            </div>
        </div>
    </div>
</nav>