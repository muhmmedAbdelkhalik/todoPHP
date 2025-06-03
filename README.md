# Todo List API

A RESTful API built with Laravel that allows users to manage their to-do list. This project implements user authentication, CRUD operations, and various security features.

## Features

- User authentication with Sanctum tokens
- CRUD operations for todo items
- User authorization (users can only manage their own todos)
- Data validation
- Pagination and filtering
- Error handling
- Security measures
- Unit and feature tests
- Refresh token mechanism

## API Endpoints

### Authentication

#### Register a new user
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@doe.com",
  "password": "password"
}
```

Response:
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@doe.com",
  "password": "password"
}
```

Response:
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9"
}
```

### Todo Operations

#### Create Todo
```http
POST /api/todos
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Buy groceries",
  "description": "Buy milk, eggs, and bread"
}
```

Response:
```json
{
  "id": 1,
  "title": "Buy groceries",
  "description": "Buy milk, eggs, and bread"
}
```

#### Update Todo
```http
PUT /api/todos/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Buy groceries",
  "description": "Buy milk, eggs, bread, and cheese"
}
```

Response:
```json
{
  "id": 1,
  "title": "Buy groceries",
  "description": "Buy milk, eggs, bread, and cheese"
}
```

#### Delete Todo
```http
DELETE /api/todos/{id}
Authorization: Bearer {token}
```

Response: 204 No Content

#### List Todos
```http
GET /api/todos?page=1&limit=10
Authorization: Bearer {token}
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "title": "Buy groceries",
      "description": "Buy milk, eggs, bread"
    },
    {
      "id": 2,
      "title": "Pay bills",
      "description": "Pay electricity and water bills"
    }
  ],
  "page": 1,
  "limit": 10,
  "total": 2
}
```

## Installation

1. Clone the repository
```bash
git clone https://github.com/muhmmedAbdelkhalik/todoPHP.git
cd todoPHP
```

2. Install dependencies
```bash
composer install
```

3. Copy environment file
```bash
cp .env.example .env
```

4. Generate application key
```bash
php artisan key:generate
```

5. Configure your database in `.env` file

6. Run migrations
```bash
php artisan migrate
```

7. Start the development server
```bash
php artisan serve
```

## Testing

Run the test suite:
```bash
php artisan test
```

## Security

- Passwords are hashed using Laravel's built-in hashing
- API endpoints are protected with Sanctum authentication
- CSRF protection is enabled
- Input validation is implemented
- Rate limiting is configured

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
