# Livraria Bookstore

This project is a simple online bookstore built with Laravel. It allows you to manage books and categories, mark favorites and place orders through a shopping cart.

## Features

- CRUD management for books with search, category filters and stock control.
- Category management.
- Favorite books for authenticated users.
- Shopping cart with checkout and order creation.
- Basic user authentication and dashboard.

## Getting Started

1. Copy the example environment file and adjust your configuration:
   ```bash
   cp .env.example .env
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install Node dependencies and build assets:
   ```bash
   npm install
   npm run build # or `npm run dev` during development
   ```
4. Generate the application key:
   ```bash
   php artisan key:generate
   ```
5. Run the database migrations:
   ```bash
   php artisan migrate
   ```
6. Start the development server:
   ```bash
   php artisan serve
   ```

## Running Tests

Execute the test suite with:

```bash
php artisan test
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as routing, dependency injection, database ORM and more. The framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## License

This project is licensed under the [MIT License](../LICENSE).
