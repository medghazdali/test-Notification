# Notification System Tests

This directory contains comprehensive unit tests for the notification system components.

## Test Structure

### Controller Tests
- **`tests/Controller/NotificationControllerTest.php`** - Unit tests for the NotificationController
  - Tests all HTTP endpoints (create, list, show, send, listPending)
  - Tests request validation and error handling
  - Tests JSON response formatting
  - Tests attachment handling

### Service Tests
- **`tests/Service/NotificationServiceTest.php`** - Unit tests for the NotificationService
  - Tests business logic for notification creation
  - Tests validation and error handling
  - Tests user and email template integration
  - Tests notification status management
  - Tests attachment creation and management

## Test Coverage

### NotificationController Tests (8 tests, 42 assertions)
- ✅ `testCreateWithValidData()` - Tests notification creation with user ID and attachments
- ✅ `testCreateWithRecipientEmail()` - Tests notification creation with email recipient
- ✅ `testCreateWithInvalidJson()` - Tests error handling for invalid JSON
- ✅ `testList()` - Tests listing all notifications
- ✅ `testSend()` - Tests sending notifications with attachments
- ✅ `testSendWithoutAttachments()` - Tests sending notifications without attachments
- ✅ `testShow()` - Tests retrieving a specific notification
- ✅ `testListPending()` - Tests listing pending notifications

### NotificationService Tests (16 tests, 96 assertions)
- ✅ `testCreateNotificationWithValidData()` - Tests notification creation with all fields
- ✅ `testCreateNotificationWithRecipientEmail()` - Tests email-only notification creation
- ✅ `testCreateNotificationWithValidationErrors()` - Tests validation error handling
- ✅ `testCreateNotificationWithoutRecipient()` - Tests missing recipient validation
- ✅ `testCreateNotificationWithUserNotFound()` - Tests user not found error
- ✅ `testCreateNotificationWithEmailTemplateNotFound()` - Tests template not found error
- ✅ `testGetNotification()` - Tests retrieving a notification by ID
- ✅ `testGetNotificationNotFound()` - Tests notification not found error
- ✅ `testGetAllNotifications()` - Tests retrieving all notifications
- ✅ `testGetPendingNotifications()` - Tests retrieving pending notifications
- ✅ `testSendNotification()` - Tests sending a notification
- ✅ `testSendNotificationWithAttachments()` - Tests sending with attachments
- ✅ `testSendNotificationNotFound()` - Tests sending non-existent notification
- ✅ `testSendNotificationCannotBeSent()` - Tests sending already sent notification
- ✅ `testGetNotificationsByUser()` - Tests retrieving notifications by user
- ✅ `testGetNotificationsByStatus()` - Tests retrieving notifications by status

## Running Tests

### Run All Tests
```bash
./vendor/bin/phpunit
```

### Run Notification Tests Only
```bash
./run_notification_tests.sh
```

### Run Specific Test Files
```bash
# Controller tests only
./vendor/bin/phpunit tests/Controller/NotificationControllerTest.php

# Service tests only
./vendor/bin/phpunit tests/Service/NotificationServiceTest.php

# Both notification test files
./vendor/bin/phpunit tests/Controller/NotificationControllerTest.php tests/Service/NotificationServiceTest.php
```

### Run Specific Test Methods
```bash
# Run a specific test method
./vendor/bin/phpunit --filter testCreateWithValidData tests/Controller/NotificationControllerTest.php
```

## Test Features

### Mocking Strategy
- **Controllers**: Mock the NotificationService dependency
- **Services**: Mock all external dependencies (EntityManager, Validator, Repositories)
- **Entities**: Use real entity instances where possible, mock only when necessary

### Test Data
- Uses realistic test data that matches the API documentation
- Tests both success and error scenarios
- Covers edge cases and validation rules

### Assertions
- Tests return types and response structures
- Validates HTTP status codes
- Checks JSON response content
- Verifies business logic behavior

## Test Dependencies

The tests use the following PHPUnit features:
- **Mock Objects** - For dependency injection and external services
- **Data Providers** - For testing multiple scenarios
- **Reflection** - For setting entity IDs in tests
- **Constraint Violations** - For testing validation errors

## Integration with CI/CD

These tests are designed to run in continuous integration environments:
- No external dependencies (database, file system, etc.)
- Fast execution (all tests complete in < 1 second)
- Deterministic results
- Clear error messages for debugging

## Adding New Tests

When adding new functionality to the notification system:

1. **Add Controller Tests** for new endpoints
2. **Add Service Tests** for new business logic
3. **Update this README** with new test descriptions
4. **Ensure 100% test coverage** for new code paths

## Test Best Practices

- **Arrange-Act-Assert** pattern for clear test structure
- **Descriptive test names** that explain the scenario
- **Single responsibility** - each test focuses on one behavior
- **Mock external dependencies** to ensure unit test isolation
- **Test both success and failure paths**
- **Use realistic test data** that matches production scenarios
