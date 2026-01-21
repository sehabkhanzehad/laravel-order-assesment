# Laravel Order Management Assessment

## Setup Instructions

Requirements: PHP 8.1+, Composer, MySQL/PostgreSQL.

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
```

## API Endpoints

Authenticated routes (use Sanctum tokens):

- `GET /api/orders` - List orders (paginated, role-based visibility)
- `POST /api/orders` - Create order
- `GET /api/orders/{order}` - View order
- `PATCH /api/orders/{order}/status` - Update status (Admin/Manager only)
- `DELETE /api/orders/{order}` - Delete order (Admin only)

## Key Features

- **Roles & Authorization**: Admin, Manager, Customer via Policies (no controller role checks).
- **Order Status**: Enum-based with transition rules (pending → processing → completed; cancelled terminal).
- **Business Logic**: Service class handles creation, updates, deletions with transactions.
- **Validation**: Form Requests for input validation.
- **Relationships**: Order belongs to User, has many OrderItems; total_amount derived.
- **Scopes**: Custom Eloquent scope for visibility filtering.
- **Error Handling**: Custom domain exceptions for invalid transitions.

## Architecture

- **Models**: Order, OrderItem, User with casts, relationships, helpers.
- **Enums**: OrderStatus, UserRole.
- **Policies**: OrderPolicy for authorization.
- **Services**: OrderService for business logic.
- **Controllers**: Thin, delegate to services.
- **Resources**: JSON API responses.
- **Migrations**: Proper foreign keys, indexes.
- **Seeders**: Sample users and orders.
