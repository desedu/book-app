CREATE DATABASE book_crossing;

USE book_crossing;

-- Локации для обмена книгами
CREATE TABLE locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL
);

-- Читатели системы
CREATE TABLE readers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Жанры книг
CREATE TABLE genres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Книги с информацией о доступности и прочим (book_take) еперь тут
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    genre_id INT,  -- жанры
    condition_book ENUM('отличное', 'хорошее', 'удовлетворительное', 'плохое') DEFAULT 'хорошее', 
    FOREIGN KEY (genre_id) REFERENCES genres(id) 
);

-- Отзывы о книгах
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    reader_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    review_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (reader_id) REFERENCES readers(id)
);

-- История перемещений книг
CREATE TABLE history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL, 
    reader_id INT NOT NULL, 
    from_location_id INT NULL, 
    to_location_id INT NULL, 
    previous_movement_id INT NULL, 
    movement_date DATETIME DEFAULT CURRENT_TIMESTAMP, 
    action_type ENUM('получил', 'вернул') NOT NULL, 
    FOREIGN KEY (book_id) REFERENCES books(id), 
    FOREIGN KEY (reader_id) REFERENCES readers(id),
    FOREIGN KEY (from_location_id) REFERENCES locations(id),
    FOREIGN KEY (to_location_id) REFERENCES locations(id),
    FOREIGN KEY (previous_movement_id) REFERENCES history(id)
);



ALTER TABLE books ADD COLUMN cover_image VARCHAR(255) NULL AFTER condition_book;
ALTER TABLE readers ADD COLUMN favorite_genres VARCHAR(255) NULL AFTER email;

