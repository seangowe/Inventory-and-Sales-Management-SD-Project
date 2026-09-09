<!-- This is my final project for a certificate in Software Development.
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
 -->
