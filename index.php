<?php
session_start();
require "dbconnect.php";

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

require "menu.php";

echo '<main class="container" style="margin-top: 100px">';

echo '
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link ' . (!isset($_GET['page']) || $_GET['page'] == 'books' ? 'active' : '') . '" href="index.php?page=books">Книги</a>
    </li>
    <li class="nav-item">
        <a class="nav-link ' . (isset($_GET['page']) && $_GET['page'] == 'mybooks' ? 'active' : '') . '" href="index.php?page=mybooks">Мои книги</a>
    </li>
    <li class="nav-item">
        <a class="nav-link ' . (isset($_GET['page']) && $_GET['page'] == 'users' ? 'active' : '') . '" href="index.php?page=users">Пользователи</a>
    </li>
    <li class="nav-item">
        <a class="nav-link ' . (isset($_GET['page']) && $_GET['page'] == 'reviews' ? 'active' : '') . '" href="index.php?page=reviews">Отзывы</a>
    </li>
</ul>';

if (isset($_SESSION['msg']) && $_SESSION['msg'] != '') {
    echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['msg']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    $_SESSION['msg'] = '';
}

if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'books':
            require "books.php";
            break;
        case 'mybooks':
            require "mybooks.php";
            break;
        case 'users':
            require "users.php";
            break;
        case 'reviews':
            require "reviews.php";
            break;
        case 'history':
            if (isset($_GET['book_id'])) {
                require "gethistory.php";
            } else {
                echo '<div class="alert alert-warning">Выберите книгу для просмотра истории</div>';
            }
            break;
        default:
            require "books.php";
            break;
    }
} else {
    require "books.php";
}

echo '</main>';
require "footer.php";
?>