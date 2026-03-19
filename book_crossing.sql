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

INSERT INTO genres (name) VALUES 
('Русская классика'), ('Фантастика'), ('Детектив');

INSERT INTO locations (name, address) VALUES
('Библиотека Сургу', 'ул. Энергетиков, 12'),
('Библиотека Пургу', 'ул. Пушкина, 5');

INSERT INTO readers (username, password, full_name, email) VALUES
('ivanov_ii', 'stylesrg!2{', 'Иванов Иван Иванович', 'ivanov@mail.ru'),
('ivanova_mi', 'qwerty', 'Иванова Мария Ивановна', 'ivanova@smail.ru');

INSERT INTO books (title, author, genre_id, condition_book) VALUES
('Преступление и наказание', 'Ф. Достоевский', 1, 'хорошее'),
('Мастер и Маргарита', 'М. Булгаков', 1, 'отличное'),
('Евгений Онегин',  'Александр Пушкин', 2, 'удовлетворительное');

INSERT INTO reviews (book_id, reader_id, rating, comment) VALUES
(1, 1, 5, 'Великолепная книга. Прочитал и всё!'),
(1, 2, 4, 'Тяжело'),
(2, 1, 3, 'Мне понравился только кошак черный');

INSERT INTO history (book_id, reader_id, from_location_id, to_location_id, previous_movement_id, action_type, movement_date) VALUES 
(1, 2, 1, NULL, NULL, 'получил', '2015-05-10 14:30:00'),
(2, 1, 2, NULL, NULL, 'получил', '2014-12-13 11:20:00'),
(1, 2, NULL, 2, 1, 'вернул', '2025-06-10 13:15:00'),
(3, 2, 2, NULL, 3, 'получил', '2015-12-15 16:45:00');

ALTER TABLE books ADD COLUMN cover_image VARCHAR(255) NULL AFTER condition_book;
