# Notification API Documentation

## Overview

This API provides endpoints for managing users and notifications with proper status management using enum-based status system.

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication
Currently no authentication is required. In production, implement proper authentication (JWT, API keys, etc.).

## Response Format

All responses are in JSON format with the following structure:

### Success Response
```json
{
  "data": "...",
  "message": "Success message (optional)"
}
```

### Error Response
```json
{
  "error": "Error message",
  "code": 400,
  "details": ["Additional error details (optional)"]
}
```

## Status Codes

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `404` - Not Found
- `409` - Conflict
- `500` - Internal Server Error

---

## User Endpoints

### Create User
**POST** `/api/users`

Creates a new user with required email, first name, and last name.

#### Request Body
```json
{
  "email": "john.doe@example.com",
  "first_name": "John",
  "last_name": "Doe"
}
```

#### Response (201 Created)
```json
{
  "id": 1,
  "email": "john.doe@example.com",
  "first_name": "John",
  "last_name": "Doe",
  "created_at": "2024-01-01 12:00:00",
  "updated_at": "2024-01-01 12:00:00",
  "notifications_count": 0
}
```

#### Validation Rules
- `email`: Required, valid email format, unique, max 255 characters
- `first_name`: Required, max 255 characters
- `last_name`: Required, max 255 characters

### List Users
**GET** `/api/users`

Returns all users in the system.

#### Response (200 OK)
```json
[
  {
    "id": 1,
    "email": "john.doe@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "created_at": "2024-01-01 12:00:00",
    "updated_at": "2024-01-01 12:00:00",
    "notifications_count": 2
  }
]
```

### Get User
**GET** `/api/users/{id}`

Returns a specific user by ID.

#### Response (200 OK)
```json
{
  "id": 1,
  "email": "john.doe@example.com",
  "first_name": "John",
  "last_name": "Doe",
  "created_at": "2024-01-01 12:00:00",
  "updated_at": "2024-01-01 12:00:00",
  "notifications_count": 2
}
```

---

## Notification Endpoints

### Create Notification
**POST** `/api/notifications`

Creates a new notification. Status is automatically set to 'pending'.

#### Request Body
```json
{
  "subject": "Welcome to Our Service",
  "body": "Thank you for joining our service!",
  "user_id": 1,
  "recipient_email": "external@example.com",
  "email_template_id": 1
}
```

**Note**: Either `user_id` or `recipient_email` must be provided.

#### Response (201 Created)
```json
{
  "id": 1,
  "user_id": 1,
  "user_name": "John Doe",
  "user_email": "john.doe@example.com",
  "recipient_email": null,
  "subject": "Welcome to Our Service",
  "body": "Thank you for joining our service!",
  "status": "pending",
  "status_label": "Pending",
  "created_at": "2024-01-01 12:00:00",
  "sent_at": null,
  "attachments_count": 0,
  "email_template_id": 1
}
```

#### Validation Rules
- `subject`: Required, max 255 characters
- `body`: Required
- `user_id`: Optional, must be valid user ID
- `recipient_email`: Optional, valid email format
- `email_template_id`: Optional, must be valid template ID

### List All Notifications
**GET** `/api/notifications`

Returns all notifications in the system.

#### Response (200 OK)
```json
[
  {
    "id": 1,
    "user_id": 1,
    "user_name": "John Doe",
    "user_email": "john.doe@example.com",
    "recipient_email": null,
    "subject": "Welcome to Our Service",
    "body": "Thank you for joining our service!",
    "status": "pending",
    "status_label": "Pending",
    "created_at": "2024-01-01 12:00:00",
    "sent_at": null,
    "attachments_count": 0,
    "email_template_id": 1
  }
]
```

### List Pending Notifications
**GET** `/api/notifications/pending`

Returns only notifications with 'pending' status.

#### Response (200 OK)
Same format as list all notifications, but filtered to pending only.

### Get Notification
**GET** `/api/notifications/{id}`

Returns a specific notification by ID.

#### Response (200 OK)
```json
{
  "id": 1,
  "user_id": 1,
  "user_name": "John Doe",
  "user_email": "john.doe@example.com",
  "recipient_email": null,
  "subject": "Welcome to Our Service",
  "body": "Thank you for joining our service!",
  "status": "pending",
  "status_label": "Pending",
  "created_at": "2024-01-01 12:00:00",
  "sent_at": null,
  "attachments_count": 0,
  "email_template_id": 1
}
```

### Send Notification
**POST** `/api/notifications/{id}/send`

Simulates sending a notification. Changes status from 'pending' to 'sent' and sets sent_at timestamp.

#### Response (200 OK)
```json
{
  "id": 1,
  "user_id": 1,
  "user_name": "John Doe",
  "user_email": "john.doe@example.com",
  "recipient_email": null,
  "subject": "Welcome to Our Service",
  "body": "Thank you for joining our service!",
  "status": "sent",
  "status_label": "Sent",
  "created_at": "2024-01-01 12:00:00",
  "sent_at": "2024-01-01 12:05:00",
  "attachments_count": 0,
  "email_template_id": 1,
  "message": "Notification sent successfully"
}
```

#### Business Rules
- Only notifications with 'pending' status can be sent
- Sending a notification changes its status to 'sent'
- Sets the `sent_at` timestamp to current time
- Cannot send already sent notifications

---

## Notification Status System

The notification system uses an enum-based status system for type safety and validation:

### Available Statuses
- `pending` - Newly created, waiting to be sent
- `sent` - Successfully sent
- `failed` - Failed to send
- `delivered` - Delivered to recipient
- `archived` - Archived notification

### Status Transitions
- New notifications are automatically created with `pending` status
- `pending` → `sent` (via send endpoint)
- `pending` → `failed` (if sending fails)
- `sent` → `delivered` (when delivery confirmed)
- Any status → `archived` (for cleanup)

### Status Properties
Each status has helper methods:
- `getLabel()` - Human-readable label
- `isCompleted()` - Whether the notification is in a final state
- `canBeSent()` - Whether the notification can be sent

---

## Error Handling

The API uses a centralized exception handling system with proper HTTP status codes:

### Common Error Responses

#### Validation Error (400)
```json
{
  "error": "Validation failed: Email is required",
  "code": 400
}
```

#### Not Found (404)
```json
{
  "error": "Notification with ID 999 not found",
  "code": 404
}
```

#### Conflict (409)
```json
{
  "error": "User with email john@example.com already exists",
  "code": 409
}
```

#### Business Logic Error (400)
```json
{
  "error": "Notification cannot be sent. Current status: sent",
  "code": 400
}
```

---

## Code Quality Features

### Architecture
- **DTOs**: Data Transfer Objects for request/response handling
- **Services**: Business logic separation from controllers
- **Custom Exceptions**: Domain-specific exception handling
- **Enum-based Status**: Type-safe status management
- **Validation**: Comprehensive input validation

### Best Practices
- **Separation of Concerns**: Controllers handle HTTP, services handle business logic
- **Error Handling**: Centralized exception handling with proper HTTP status codes
- **Type Safety**: Enum-based status system prevents invalid states
- **Validation**: Input validation at both DTO and entity levels
- **Immutability**: Readonly properties in response DTOs
- **Clean Code**: Single responsibility principle, dependency injection

### Testing
Use the provided `test_notification_api.php` script to test all endpoints:

```bash
php test_notification_api.php
```

This script demonstrates:
- Creating users and notifications
- Automatic status management
- Status transitions
- Error handling
- Business rule validation
