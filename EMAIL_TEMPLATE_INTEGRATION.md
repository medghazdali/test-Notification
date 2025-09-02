# Email Template Integration Guide

## Overview

The `email_template_id` parameter in the create notification endpoint allows you to link notifications to pre-defined email templates. This provides a powerful way to maintain consistent branding, track template usage, and enable template-based email rendering.

## How It Works

### 1. Template Creation
First, create email templates with placeholders:

```json
POST /api/email-templates
{
  "name": "Welcome Email Template",
  "subject_template": "Welcome to {company_name}, {first_name}!",
  "html_body_template": "<h1>Welcome to {company_name}, {first_name}!</h1><p>Dear {first_name} {last_name},</p>...",
  "plain_text_body_template": "Welcome to {company_name}, {first_name}!\n\nDear {first_name} {last_name},..."
}
```

### 2. Notification Creation with Template
Link notifications to templates using `email_template_id`:

```json
POST /api/notifications
{
  "subject": "Welcome to TechCorp, John!",
  "body": "Dear John Doe, We are thrilled to welcome you to TechCorp!...",
  "user_id": 1,
  "email_template_id": 1,
  "attachments": [...]
}
```

### 3. Template Usage Tracking
The system automatically tracks how many notifications use each template:

```json
GET /api/email-templates/1
{
  "id": 1,
  "name": "Welcome Email Template",
  "notifications_count": 5,
  ...
}
```

## Template Placeholders

### Common Placeholders
- `{first_name}` - User's first name
- `{last_name}` - User's last name
- `{email}` - User's email address
- `{company_name}` - Company name
- `{current_date}` - Current date
- `{current_year}` - Current year

### Custom Placeholders
You can use any custom placeholders in your templates:
- `{account_type}` - Account type
- `{reset_link}` - Password reset link
- `{expiry_hours}` - Link expiry time
- `{newsletter_title}` - Newsletter title
- `{featured_content}` - Featured content
- `{company_updates}` - Company updates

## Template Types

### 1. Welcome Email Template
```json
{
  "name": "Welcome Email Template",
  "subject_template": "Welcome to {company_name}, {first_name}!",
  "html_body_template": "<h1>Welcome to {company_name}, {first_name}!</h1><p>Your account has been created.</p>",
  "plain_text_body_template": "Welcome to {company_name}, {first_name}!\n\nYour account has been created."
}
```

### 2. Password Reset Template
```json
{
  "name": "Password Reset Template",
  "subject_template": "Password Reset Request - {company_name}",
  "html_body_template": "<h1>Password Reset</h1><p>Click <a href=\"{reset_link}\">here</a> to reset your password.</p>",
  "plain_text_body_template": "Password Reset\n\nClick here to reset your password: {reset_link}"
}
```

### 3. Newsletter Template
```json
{
  "name": "Newsletter Template",
  "subject_template": "{newsletter_title} - {month} {year}",
  "html_body_template": "<h1>{newsletter_title}</h1><p>{featured_content}</p><p>{company_updates}</p>",
  "plain_text_body_template": "{newsletter_title}\n\n{featured_content}\n\n{company_updates}"
}
```

## API Endpoints

### Create Notification with Template
```http
POST /api/notifications
Content-Type: application/json

{
  "subject": "Welcome to TechCorp, John!",
  "body": "Dear John Doe, welcome to TechCorp!",
  "user_id": 1,
  "email_template_id": 1,
  "attachments": [
    {
      "file_name": "welcome_guide.pdf",
      "mime_type": "application/pdf",
      "file_path": "/uploads/welcome_guide.pdf"
    }
  ]
}
```

### Response
```json
{
  "id": 1,
  "user_id": 1,
  "subject": "Welcome to TechCorp, John!",
  "body": "Dear John Doe, welcome to TechCorp!",
  "status": "pending",
  "email_template_id": 1,
  "attachments_count": 1,
  "created_at": "2024-01-01 12:00:00"
}
```

## Template Management

### List All Templates
```http
GET /api/email-templates
```

### Get Template Details
```http
GET /api/email-templates/{id}
```

### Update Template
```http
PUT /api/email-templates/{id}
{
  "name": "Updated Welcome Template",
  "subject_template": "Welcome to {company_name}, {first_name}!",
  "html_body_template": "...",
  "plain_text_body_template": "..."
}
```

### Delete Template
```http
DELETE /api/email-templates/{id}
```

**Note:** Cannot delete templates that are being used by notifications.

## Real-World Email Rendering

In a production email system, the templates would be rendered with actual data:

### Template Rendering Process
1. **Retrieve Template**: Get template by `email_template_id`
2. **Gather Data**: Collect user data, company info, etc.
3. **Replace Placeholders**: Substitute placeholders with actual values
4. **Render HTML/Text**: Generate final email content
5. **Send Email**: Deliver rendered email to recipient

### Example Rendering
**Template:**
```
Subject: Welcome to {company_name}, {first_name}!
Body: Dear {first_name} {last_name}, welcome to {company_name}!
```

**Rendered:**
```
Subject: Welcome to TechCorp, John!
Body: Dear John Doe, welcome to TechCorp!
```

## Benefits

### 1. **Consistent Branding**
- Standardized email templates across the platform
- Consistent look and feel for all communications
- Easy brand updates across all emails

### 2. **Template Reusability**
- Create once, use many times
- Reduce development time for new email types
- Maintain template library for different use cases

### 3. **Usage Analytics**
- Track which templates are most used
- Monitor email performance by template
- Optimize templates based on usage data

### 4. **Easy Maintenance**
- Update templates without changing notification code
- A/B test different template versions
- Centralized template management

### 5. **Developer Experience**
- Clean separation of content and logic
- Easy to create new email types
- Template versioning and rollback capabilities

## Best Practices

### 1. **Template Design**
- Use semantic placeholder names
- Keep templates focused on single purpose
- Include both HTML and plain text versions
- Test templates with various data inputs

### 2. **Placeholder Naming**
- Use descriptive names: `{user_first_name}` vs `{fn}`
- Follow consistent naming conventions
- Document all available placeholders
- Use lowercase with underscores

### 3. **Template Organization**
- Group related templates by category
- Use descriptive template names
- Maintain template documentation
- Version control template changes

### 4. **Error Handling**
- Handle missing placeholder data gracefully
- Provide fallback values for optional placeholders
- Log template rendering errors
- Validate template syntax

## Testing

### Test Template Creation
```bash
php test_email_template_workflow.php
```

### Test Scenarios
- ✅ Create templates with placeholders
- ✅ Create notifications with template references
- ✅ Track template usage counts
- ✅ Send notifications with templates
- ✅ Update and delete templates
- ✅ Handle template validation errors

## Integration Examples

### 1. **User Registration Flow**
```json
POST /api/notifications
{
  "subject": "Welcome to TechCorp, John!",
  "body": "Dear John Doe, your account has been created...",
  "user_id": 1,
  "email_template_id": 1,
  "attachments": [{"file_name": "welcome_guide.pdf", ...}]
}
```

### 2. **Password Reset Flow**
```json
POST /api/notifications
{
  "subject": "Password Reset Request - TechCorp",
  "body": "Hello John, click here to reset your password...",
  "user_id": 1,
  "email_template_id": 2
}
```

### 3. **Newsletter Campaign**
```json
POST /api/notifications
{
  "subject": "Monthly Tech Update - January 2024",
  "body": "Tech Update Newsletter. Featured: New AI features...",
  "recipient_email": "newsletter@example.com",
  "email_template_id": 3,
  "attachments": [{"file_name": "newsletter.pdf", ...}]
}
```

## Conclusion

The `email_template_id` parameter provides a powerful way to integrate email templates with notifications, enabling:

- **Consistent branding** across all communications
- **Template reusability** and maintenance
- **Usage tracking** and analytics
- **Easy content management** without code changes
- **Professional email rendering** with placeholders

This integration makes the notification system production-ready for real-world email campaigns and user communications.
