# StockPulse

This is my final project for a certificate in Software Development.
The project is a Inventory and Sales Management system for small and medium enterprises.

> - `create_database.php`: creates the `sales_inventory` database.
> - `create_table_products.php`, `create_table_sales.php`, `create_table_users.php`: create the `products`, `sales`, and `users` tables.
> - `connection.php`: central database connection used by all pages.
> - `create_admin.php` / `create_cashier.php`: insert initial admin/cashier accounts into `users`.
> - `login.php`: checks credentials and starts a session (`$_SESSION['user_id']`, `$_SESSION['role']`).
> - `logout.php`: destroys the session and redirects to login.
> - `admin.php` / `cashier.php` / `dashboard.php`: role-specific dashboards shown after login.
> - `users.php`: manage users — add users and (only admin with ID 1) delete users.
> - `products.php`: add/edit/list products stored in `products` table.
> - `sales.php`: record sales, update stock, and write to `sales` table.
> - `reports.php`: read `sales`/`products` data and display summaries/reports.
>
> Overall flow: run the create scripts to set up DB/tables, create initial users, then use `login.php` to authenticate; after login pages use `connection.php` + sessions to enforce roles and perform CRUD on products, users, and sales; `logout.php` ends the session.


StockPulse is a simple inventory and sales management system designed for small and medium businesses. It helps manage products, handle sales transactions, track stock levels, and generate basic reports using PHP and MySQL.

This project was developed as a final certificate project in Software Development and focuses on practical CRUD operations, user roles, and a working inventory workflow.

## Project Overview

The system allows different users to log in based on their role:

- Admin: full access to manage users, products, sales, and reports.
- Cashier: can process sales and view available products.

The application uses PHP for the backend, MySQL as the database, and HTML/CSS for the frontend interface.

## Main Features

- User authentication and role-based login
- Secure password hashing using PHP `password_hash()`
- Product management: add, edit, delete, and search products
- Stock tracking and minimum stock threshold monitoring
- Sales recording with automatic stock deduction
- Sales history tracking
- Reports for total sales and revenue
- Low-stock product monitoring
- Session-based access control for protected pages

## Technology Stack

- PHP
- MySQL / MariaDB
- HTML
- CSS
- XAMPP (for local development)

## Project Structure

- `connection.php` - creates the connection to the MySQL server
- `create_database.php` - creates the `sales_inventory` database
- `create_table_users.php` - creates the `users` table
- `create_table_products.php` - creates the `products` table
- `create_table_sales.php` - creates the `sales` table
- `create_admin.php` - creates the default admin account
- `create_cashier.php` - creates a default cashier account
- `login.php` - handles login and redirects users by role
- `logout.php` - ends the session and logs the user out
- `admin.php` - admin dashboard and main admin interface
- `cashier.php` - cashier dashboard and restricted cashier interface
- `products.php` - manage products and stock levels
- `sales.php` - record sales and maintain stock updates
- `users.php` - manage users (admin-level access)
- `reports.php` - view sales and stock summary information
- `assets/stockpulse-logo.svg` - application logo

## Database Design

The project uses a single database called `sales_inventory` with three core tables.

### 1. users
Stores system accounts.

Columns:
- `id` - unique user ID
- `username` - unique username
- `password` - hashed password
- `role` - either `admin` or `cashier`

### 2. products
Stores product records used in inventory tracking.

Columns:
- `id` - product ID
- `name` - product name
- `category` - product category
- `buying_price` - cost price
- `selling_price` - selling price
- `stock` - available quantity
- `min_stock_level` - alert threshold for low-stock warnings

### 3. sales
Stores each recorded transaction.

Columns:
- `id` - sale ID
- `product_id` - product sold
- `quantity` - number of units sold
- `total_price` - total price of the sale
- `sale_date` - date and time of sale
- `user_id` - cashier/admin who processed the sale

## Setup Instructions

### 1. Install XAMPP
Download and install XAMPP, then start the following services:
- Apache
- MySQL

### 2. Place the Project in the Web Root
Copy the project folder into:

`C:/xampp/htdocs/`

Then open it in your browser using:

`http://localhost/Project/`

### 3. Create the Database
Open the following URL in your browser:

`http://localhost/Project/create_database.php`

This creates the `sales_inventory` database.

### 4. Create the Tables
Run these files in order:

- `http://localhost/Project/create_table_users.php`
- `http://localhost/Project/create_table_products.php`
- `http://localhost/Project/create_table_sales.php`

These create the required database tables.

### 5. Create Initial Users
Run the default user creation scripts:

- `http://localhost/Project/create_admin.php`
- `http://localhost/Project/create_cashier.php`

This inserts sample accounts into the database.

### 6. Login to the System
Open:

`http://localhost/Project/login.php`

Default login credentials:

Admin:
- Username: `admin`
- Password: `admin123`

Cashier:
- Username: `cashier1`
- Password: `cashier123`

## User Roles and Permissions

### Admin
The admin account has access to:
- dashboard
- manage products
- manage users
- view sales records
- view reports

### Cashier
The cashier account has access to:
- cashier dashboard
- sales module
- product viewing
- sales processing

Cashiers are restricted from managing users and reports.

## How the System Works

### Product Management
Admins can add new products with details such as:
- product name
- category
- buying price
- selling price
- stock quantity
- minimum stock level

These products are later used in sales transactions.

### Sales Processing
When a sale is recorded:
- the selected product is identified
- quantity is checked against available stock
- total sale price is calculated
- stock is decreased
- a record is saved in the `sales` table
- the transaction is associated with the logged-in user

### Stock Monitoring
Products with stock at or below their minimum threshold are flagged as low-stock items. This helps businesses track inventory problems early.

### Reporting
The reports page summarizes:
- total number of sales
- total revenue collected
- products below minimum stock level

## Security Notes

This project includes basic but effective security features:

- password hashing with `password_hash()`
- session checks before protected pages are accessed
- role validation before allowing access to admin-only functionality

## Common Issues and Troubleshooting

### Database connection error
Ensure:
- Apache and MySQL are running in XAMPP
- the database was created successfully
- the MySQL username/password matches the project setup (`root` with no password)

### Login fails
Check whether:
- the database and users table were created correctly
- the admin or cashier account was created
- the role selected in the login form matches the user role

### Access denied
If you are redirected to the login page, it usually means:
- the session is not set
- the user is not logged in
- a page is restricted to a different role

## Future Improvements

Possible enhancements for later versions include:

- better dashboard analytics and charts
- PDF export for reports
- product image uploads
- customer management
- order history and refunds
- search filters and pagination
- improved UI/UX design
- audit logs for admin actions

## Summary

StockPulse is a practical inventory and sales management project that demonstrates real-world business logic using PHP and MySQL. It covers the essentials of a small business system: user login, product inventory, sales tracking, low-stock alerts, and report generation.

The application is intentionally simple, beginner-friendly, and suitable for learning full-stack web development concepts in a business context.

