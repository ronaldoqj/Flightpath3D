# User Management System

A simple PHP-based user management system with a RESTful JSON API and a web interface for CRUD operations.

## Features

- List all registered users
- Create new users
- Update existing users
- Delete users (soft delete)
- Clean JSON API
- Responsive Bootstrap UI

## Project Structure

```text
├── index.php                 # Web interface (UI) - main entry point
├── Main.php                  # REST API endpoint
├── swagger.yaml              # OpenAPI 3.0 specification
├── controllers/
│   └── UsersController.php   # Business logic controller
├── models/
│   └── Users.php             # Data model and persistence
└── data/
    └── users.json            # JSON data storage
```

## Requirements

- PHP 7.4 or higher
- No additional dependencies required (uses built-in PHP server)

## How to Run

### 1. Clone or navigate to the project directory

```bash
cd /path/to/Flightpath3D
```

### 2. Start the PHP built-in server

```bash
php -S localhost:8000
```

### 3. Access the application

Open your browser and navigate to:

```text
http://localhost:8000/index.php
```

## Using the Web Interface (index.php)

The `index.php` file provides a complete web interface with the following functionalities:

### Add a New User

1. Fill in the **Name** and **Email** fields in the form at the top
2. Click the **Add** button
3. The new user will appear in the table below

### View All Users

- All registered users are automatically displayed in a table
- Each row shows: ID, Name, Email, and action buttons
- Click **Reload** to refresh the list manually

### Update a User

1. Click the **Update** button next to any user
2. Enter the new name and/or email in the prompts
3. The user information will be updated immediately

### Delete a User

1. Click the **Delete** button next to any user
2. Confirm the deletion in the dialog
3. The user will be removed from the list (soft delete)

## API Documentation

The complete API specification is available in **OpenAPI 3.0** format in the `swagger.yaml` file. You can:

- View it in [Swagger Editor](https://editor.swagger.io/) by pasting the contents
- Use it with Swagger UI, Postman, or any OpenAPI-compatible tool
- Generate client SDKs in multiple languages

### API Endpoints

If you want to interact with the API directly (via curl, Postman, etc.):

### List Users

```bash
curl http://localhost:8000/Main.php?action=list
```

### Create User

```bash
curl -X POST http://localhost:8000/Main.php \
  -H "Content-Type: application/json" \
  -d '{"action":"create","name":"John Doe","email":"john@example.com"}'
```

### Update User

```bash
curl -X POST http://localhost:8000/Main.php \
  -H "Content-Type: application/json" \
  -d '{"action":"update","id":"USER_ID","name":"Jane Doe","email":"jane@example.com"}'
```

### Delete User

```bash
curl -X POST http://localhost:8000/Main.php \
  -H "Content-Type: application/json" \
  -d '{"action":"delete","id":"USER_ID"}'
```

### List Including Deleted Users

```bash
curl http://localhost:8000/Main.php?action=list&includeDeleted=true
```

## Data Storage

User data is stored in `data/users.json` with the following structure:

```json
[
  {
    "name": "John Doe",
    "email": "john@example.com",
    "id": "unique_id",
    "deleted": false
  }
]
```

- Users are soft-deleted (marked as `deleted: true`) rather than removed from the file
- Each user has a unique ID generated using `uniqid()`
- Email addresses must be unique

## Technologies Used

- **Backend**: PHP (procedural and OOP)
- **Frontend**: HTML5, Bootstrap 5, Vanilla JavaScript (ES6+)
- **Data Format**: JSON
- **HTTP**: RESTful API design

## Notes

- The system uses soft deletes, so deleted users remain in `users.json` with `deleted: true`
- Email validation prevents duplicate registrations
- No external database required - uses file-based JSON storage
- The UI automatically refreshes after create, update, and delete operations
