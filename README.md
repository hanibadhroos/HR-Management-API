# HR Management API

A robust and scalable HR Management RESTful API built with Laravel 11, following clean architecture principles, UUID support, service layer pattern, logging system, and advanced Artisan commands.

## 🚀 Features
### Authentication
- Laravel Sanctum authentication
- Secure API access
- Rate limiting
- API versioning (/api/v1)

### Employees Module
- Full CRUD operations
- UUID primary keys
- Founder role logic
- Salary change tracking
- Salary change timestamp
- Employees without salary change filter
- CSV import
- CSV export
- JSON export
- Employee hierarchy (manager relationship)

### Positions Module
- Full CRUD
- UUID primary keys
- Prevent deletion if position has employees
- Pagination support
- Clean API Resources

### Logging System
- Database logging (employee_logs table)
- File logging (employee.log)
- Logs API endpoint
- Artisan command to delete old logs
- Artisan command to clear log files

### Artisan Commands
- employee-logs:clean {days} ---> Delete logs older than X days
- logs:clear --->	Remove all log files
- employees:insert {count} --->	Insert multiple employees
- db:export ---> Export database to SQL
- employees:export-json --->Export employees to JSON

### Architecture
- The project follows:
- Service Layer Pattern
- Form Request Validation
- API Resources
- UUID-based Models
- Database Transactions
- Clean Separation of Concerns
- Event-driven salary updates
- Pagination & filtering
- Versioned API routes

### Tech Stack
- Laravel 11
- Sanctum
- MySQL
- PHPUnit
- Carbon
- Faker
- UUID

## 🛠 Installation
- `git clone https://github.com/hanibadhroos/HR-Management-API.git`
- `cd hr-management-api`
- `composer install`
### Create environment file:
   `cp .env.example .env`
### Generate key:
- `php artisan key:generate`
### Run migrations:
- `php artisan migrate`
### Install Sanctum:
- `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
- `php artisan migrate`
### Run server:
- `php artisan serve`
### Running Tests
- `php artisan test`

## API Versioning
All endpoints are prefixed with: `/api/v1`
Example: <br/>
POST `/api/v1/employees`

## Salary Change Logic
- Founder salary cannot be changed
- When salary changes:
    - salary_changed_at updates
    - Log record is created
    - Notification event triggered
 
## Database Backup
To export database:
`php artisan db:export` <br/>
File will be saved inside:
`storage/app/backups`

## Export Employees
`php artisan employees:export-json` <br/>

Saved inside: <br/>
`storage/app/exports`

## Logs API
GET `/api/v1/employee-logs` <br/>
GET `/api/v1/employee-logs?employee_id=UUID`

## Author
Hani Ahmed
