CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    published_year INT NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    stock INT DEFAULT 0
);

-- Seeding the database with dummy data
INSERT INTO books (title, author, published_year, isbn, stock) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 1925, '9780743273565', 5),
('To Kill a Mockingbird', 'Harper Lee', 1960, '9780061120084', 3),
('1984', 'George Orwell', 1949, '9780451524935', 8),
('Pride and Prejudice', 'Jane Austen', 1813, '9780141439518', 4),
('The Catcher in the Rye', 'J.D. Salinger', 1951, '9780316769480', 6);
