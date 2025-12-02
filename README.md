# Recipe Book Application

A full-stack recipe management application built as a test assignment. This application allows users to manage their own recipes with support for multiple user accounts, authentication, and role-based access control.

## 🚀 Features

### Core Requirements

- **User Management**
  - User registration with email validation
  - Login/Logout functionality
  - Password reset via email
  - Role-based access: Users (manage own recipes) and Admins (manage all recipes)

- **Recipe Management (CRUD)**
  - Create, read, update, and delete recipes
  - Recipe includes: title, cuisine type, ingredients, steps, and pictures
  - Only authorized users can view/edit/delete their recipes
  - Admin users can manage all recipes

- **Homepage**
  - Overview of recipes ordered by creation date (newest first)
  - Search/filter by recipe title (case-insensitive, supports Unicode)
  - Filter by cuisine type

### Optional Features (Bonus)

- ✅ Docker setup for easy deployment
- ✅ Comprehensive test suite (13 tests including E2E)
- ✅ API documentation (Swagger/OpenAPI)
- ✅ GitHub CI pipeline

## 🛠️ Technology Stack

### Backend
- **Laravel 12** - PHP framework
- **PostgreSQL 16** - Database
- **Orchid Platform** - Admin panel
- **PHP 8.2+** - Runtime

### Frontend
- **Vue.js 3** - Progressive JavaScript framework
- **Vue Router** - Client-side routing
- **Axios** - HTTP client
- **Vite** - Build tool

### Infrastructure
- **Docker & Docker Compose** - Containerization
- **Nginx** - Reverse proxy
- **PHPUnit** - Testing framework

## 📋 Prerequisites

- Docker and Docker Compose installed
- Git
- (Optional) SMTP server for password reset emails

## 🚀 Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd ctest
```

### 2. Environment Configuration

Create a `.env` file in the root directory:

```env
# Application
APP_KEY=base64:your-generated-key-here
APP_DEBUG=true

# Database
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=laravel

# reCAPTCHA (optional, for registration)
RECAPTCHA_SITE_KEY=your-site-key
RECAPTCHA_SECRET_KEY=your-secret-key

# Mail Configuration (optional, for password reset)
MAIL_HOST=host.docker.internal
MAIL_PORT=25
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=noreply@localhost
MAIL_FROM_NAME=Laravel
```

**Note:** Generate `APP_KEY` using:
```bash
docker compose exec backend php artisan key:generate
```

### 3. Start the Application

```bash
docker compose up -d
```

This will start:
- PostgreSQL database (port 5433)
- Laravel backend (port 8000)
- Vue.js frontend (port 5173)
- Nginx reverse proxy (port 80)

### 4. Access the Application

- **Frontend:** http://localhost
- **Admin Panel:** http://localhost/admin
- **API:** http://localhost/api

### 5. Create Admin User

To create an admin user, first register a user through the frontend, then run:

```bash
docker compose exec backend php artisan user:make-admin <email>
```

## 🧪 Running Tests

All tests can be run with a single command:

```bash
docker compose exec backend php artisan test
```

Run specific test file:

```bash
docker compose exec backend php artisan test --filter CuisineTest
```

### CI/CD Pipeline

The project includes a GitHub Actions CI pipeline (`.github/workflows/ci.yml`) that:

- Runs automatically on push and pull requests to `main`, `master`, and `develop` branches
- Sets up PHP 8.2 with required extensions
- Configures PostgreSQL 16 database
- Installs dependencies via Composer
- Runs database migrations
- Runs database seeders
- Executes all PHPUnit tests (13 tests including E2E)
- Generates Swagger documentation

The pipeline ensures that all tests pass before code can be merged.

### Test Coverage

- **Cuisine Tests** (4 tests)
  - Create Italian cuisine
  - Unauthenticated user cannot create cuisine
  - Cuisine name is required
  - Cuisine name must be unique

- **Ingredient Tests** (4 tests)
  - Create carrot ingredient
  - Unauthenticated user cannot create ingredient
  - Ingredient name is required
  - Ingredient name must be unique

- **Recipe Tests** (3 tests)
  - Create recipe with steps and images
  - Unauthenticated user cannot create recipe
  - Recipe title is required

- **E2E Tests** (2 tests)
  - Complete recipe creation flow (registration → login → create cuisine → create ingredients → upload images → create recipe → view recipes)
  - User can filter recipes

**Total: 13 tests, 168 assertions**

## 📁 Project Structure

```
ctest/
├── app/
│   ├── Console/Commands/      # Artisan commands
│   ├── Http/
│   │   ├── Controllers/       # API controllers
│   │   ├── Middleware/        # Custom middleware
│   │   └── Requests/          # Form requests
│   ├── Models/                # Eloquent models
│   ├── Notifications/         # Email notifications
│   ├── Orchid/                # Admin panel screens
│   ├── Policies/              # Authorization policies
│   └── Services/              # Business logic services
├── bootstrap/                 # Application bootstrap
├── config/                    # Configuration files
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── docker/                    # Docker configuration
│   ├── backend/               # Backend Dockerfile
│   ├── nginx/                 # Nginx configuration
│   └── postgres/              # PostgreSQL configuration
├── frontend/                  # Vue.js frontend
│   ├── src/
│   │   ├── components/        # Reusable components
│   │   ├── views/             # Page components
│   │   ├── App.vue            # Root component
│   │   └── main.js            # Entry point
│   └── package.json
├── public/                    # Public assets
├── routes/
│   ├── api.php                # API routes
│   ├── web.php                # Web routes
│   └── platform.php           # Admin panel routes
├── storage/                   # File storage
├── tests/                     # Test suite
│   ├── Feature/               # Feature tests
│   └── Unit/                  # Unit tests
├── docker-compose.yml         # Docker services
└── README.md                  # This file
```

## 🔧 Technical Decisions

### Architecture

1. **Separation of Concerns**
   - Controllers handle HTTP requests/responses
   - Policies handle authorization logic
   - Models contain business logic and relationships
   - Services encapsulate complex business operations

2. **SOLID Principles**
   - Single Responsibility: Each class has one reason to change
   - Open/Closed: Policies allow extension without modification
   - Dependency Inversion: Controllers depend on abstractions (Policies)

3. **API Design**
   - RESTful API endpoints
   - JSON responses
   - Session-based authentication (web middleware)
   - CSRF protection for state-changing operations

### Database Design

- **Users** - Authentication and authorization
- **Recipes** - Main recipe entity
- **Cuisines** - Recipe categories (shared across users)
- **Ingredients** - Recipe ingredients (shared across users)
- **Recipe Steps** - Step-by-step instructions
- **Recipe Step Ingredients** - Many-to-many relationship between steps and ingredients
- **Attachments** - Image storage using Orchid's attachment system

### Authorization

- **Laravel Policies** - Centralized authorization logic
- **User Scope** - Recipes are scoped to their creators
- **Admin Override** - Admins can access all resources
- **Shared Resources** - Cuisines and Ingredients are accessible to all authenticated users

### Frontend Architecture

- **Component-Based** - Reusable Vue components
- **Router-Based Navigation** - Vue Router for SPA navigation
- **Axios Interceptors** - Centralized API error handling
- **Reactive State** - Vue 3 Composition API

### Security

- **Password Hashing** - Laravel's bcrypt
- **CSRF Protection** - Laravel's built-in CSRF tokens
- **reCAPTCHA** - Optional protection for registration
- **Session Management** - Secure session handling
- **Input Validation** - Laravel form requests

## 📚 API Documentation

The API documentation is available via Swagger UI at:

**http://localhost/api/documentation**

The documentation includes:
- All API endpoints with request/response examples
- Authentication requirements
- Request/response schemas
- Validation rules
- Error responses

To regenerate the documentation after making changes:

```bash
docker compose exec backend php artisan l5-swagger:generate
```

## 📡 API Endpoints

### Authentication

- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user
- `GET /api/user` - Get authenticated user
- `POST /api/password/email` - Request password reset
- `POST /api/password/reset` - Reset password

### Recipes

- `GET /api/recipes` - List recipes (with filters: `?title=...&cuisine_id=...`)
- `POST /api/recipes` - Create recipe
- `GET /api/recipes/{id}` - Get recipe
- `PUT /api/recipes/{id}` - Update recipe
- `DELETE /api/recipes/{id}` - Delete recipe

### Cuisines

- `GET /api/cuisines` - List cuisines (all users)
- `POST /api/cuisines` - Create cuisine
- `GET /api/cuisines/{id}` - Get cuisine
- `PUT /api/cuisines/{id}` - Update cuisine
- `DELETE /api/cuisines/{id}` - Delete cuisine

### Ingredients

- `GET /api/ingredients` - List ingredients (all users)
- `POST /api/ingredients` - Create ingredient
- `GET /api/ingredients/{id}` - Get ingredient
- `PUT /api/ingredients/{id}` - Update ingredient
- `DELETE /api/ingredients/{id}` - Delete ingredient

### Attachments

- `POST /api/attachments` - Upload image
- `GET /api/attachments/{id}` - Get attachment

## 🎯 Assumptions

1. **Cuisines and Ingredients are Shared**
   - All authenticated users can view all cuisines and ingredients
   - Users can create their own cuisines and ingredients
   - This allows recipe sharing and collaboration

2. **Recipes are Private**
   - Users can only view/edit/delete their own recipes
   - Admins can manage all recipes
   - This ensures user privacy

3. **Image Storage**
   - Images are stored using Orchid's attachment system
   - Multiple images per recipe are supported
   - Images are stored in `storage/app/public`

4. **Password Reset**
   - Requires SMTP configuration
   - Uses Laravel's built-in password reset functionality
   - Frontend URL is constructed in the notification

5. **Admin Panel**
   - Uses Orchid Platform for admin interface
   - Accessible at `/admin`
   - Requires admin permissions (set via `user:make-admin` command)

6. **Database**
   - PostgreSQL is used as the primary database
   - Migrations run automatically on container startup
   - Database persists in Docker volume

## 🔐 Admin Panel

The admin panel is built with Orchid Platform and provides:

- User management
- Role management
- Recipe management (for admins)
- System configuration

**Access:** http://localhost/admin

**Creating Admin User:**
```bash
docker compose exec backend php artisan user:make-admin <email>
```

## 🐛 Troubleshooting

### Database Connection Issues

If you encounter database connection errors:

```bash
docker compose restart postgres
docker compose exec backend php artisan migrate:fresh
```

### Permission Issues

If you have file permission issues:

```bash
docker compose exec backend chmod -R 775 storage bootstrap/cache
```

### Clear Cache

```bash
docker compose exec backend php artisan config:clear
docker compose exec backend php artisan cache:clear
```

### View Logs

```bash
docker compose logs backend
docker compose logs frontend
docker compose logs nginx
```

## 📝 Development

### Running in Development Mode

The application is already configured for development with hot-reload:

- Frontend: Vite dev server with HMR
- Backend: Laravel development server

### Code Style

Laravel Pint is configured for code formatting:

```bash
docker compose exec backend ./vendor/bin/pint
```

## 📄 License

This project is a test assignment and is not licensed for production use.

## 👤 Author

Built as a test assignment for fointerpro (formerly Survey Anyplace).

---

**Note:** This application is built according to the requirements specified in the assignment. All optional features have been implemented:
- API documentation (Swagger) is available at http://localhost/api/documentation
- GitHub CI pipeline runs tests automatically on push and pull requests

