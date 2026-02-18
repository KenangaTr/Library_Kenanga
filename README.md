# Library Management System

A simple CRUD application for managing a library's book inventory using Native PHP, MySQL, and Bootstrap 5.

## Project Structure

```
/config
    - database.php      # Database connection settings
/includes
    - header.php        # Header template (Navbar, CSS)
    - footer.php        # Footer template (Scripts, Closing tags)
/sql
    - schema.sql        # Database creation script
/
    - index.php         # Main page (Read books)
    - create.php        # Add new book page
    - edit.php          # Edit book page
    - delete.php        # Delete action
```

## Setup Instructions

1.  **Software Requirements**:
    - XAMPP (or any PHP/MySQL environment)
    - Web Browser

2.  **Database Setup**:
    - Open phpMyAdmin (http://localhost/phpmyadmin)
    - Create a new database named `library_db` (Optional, script handles this if run)
    - Import `sql/database.sql` to create the table and populate it with sample data.

3.  **Configuration**:
    - Open `config/database.php`
    - Check the MySQL credentials (`root`, empty password). Adjust if your local setup is different.

4.  **Running the App**:
    - Place the project folder in `htdocs` (if using XAMPP).
    - Open your browser and navigate to `http://localhost/Praktikum2/`.

## Features
- **View Books**: See all books in a responsive table.
- **Search**: Filter books by title, author, or ISBN.
- **Add Book**: Form to add a new entry.
- **Edit Book**: Update details of an existing book.
- **Delete Book**: Remove a book with a confirmation prompt.
