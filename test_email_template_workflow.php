<?php

/**
 * Email Template Workflow Test Script
 * 
 * This script demonstrates how to use email templates with notifications:
 * 1. Create email templates
 * 2. Create notifications with template references
 * 3. Show template usage tracking
 * 4. Demonstrate template-based notifications
 */

$baseUrl = 'http://127.0.0.1:8000/api';

function makeRequest($method, $url, $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

function printResult($title, $result) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "🔹 $title\n";
    echo str_repeat("=", 80) . "\n";
    echo "HTTP Code: {$result['code']}\n";
    echo "Response: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
}

echo "📧 Email Template Workflow Test\n";
echo "Demonstrating email template integration with notifications\n";

// ============================================================================
// SETUP: Create a user first
// ============================================================================
$userData = [
    'email' => 'template.user@example.com',
    'first_name' => 'Template',
    'last_name' => 'User'
];

$result = makeRequest('POST', "$baseUrl/users", $userData);
printResult('Setup: Create User', $result);
$userId = $result['data']['id'] ?? null;

if (!$userId) {
    echo "❌ Failed to create user. Exiting.\n";
    exit(1);
}

// ============================================================================
// STEP 1: Create Email Templates
// ============================================================================
echo "\n" . str_repeat("📧", 20) . " STEP 1: CREATE TEMPLATES " . str_repeat("📧", 20) . "\n";

// Template 1: Welcome Email
$welcomeTemplate = [
    'name' => 'Welcome Email Template',
    'subject_template' => 'Welcome to {company_name}, {first_name}!',
    'html_body_template' => '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h1 style="color: #2c3e50;">Welcome to {company_name}, {first_name}!</h1>
            <p>Dear {first_name} {last_name},</p>
            <p>We are thrilled to welcome you to {company_name}! Your account has been successfully created.</p>
            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3>Your Account Details:</h3>
                <ul>
                    <li><strong>Email:</strong> {email}</li>
                    <li><strong>Account Type:</strong> {account_type}</li>
                    <li><strong>Registration Date:</strong> {registration_date}</li>
                </ul>
            </div>
            <p>If you have any questions, please don\'t hesitate to contact our support team.</p>
            <p>Best regards,<br>The {company_name} Team</p>
        </div>
    ',
    'plain_text_body_template' => '
        Welcome to {company_name}, {first_name}!
        
        Dear {first_name} {last_name},
        
        We are thrilled to welcome you to {company_name}! Your account has been successfully created.
        
        Your Account Details:
        - Email: {email}
        - Account Type: {account_type}
        - Registration Date: {registration_date}
        
        If you have any questions, please don\'t hesitate to contact our support team.
        
        Best regards,
        The {company_name} Team
    '
];

$result = makeRequest('POST', "$baseUrl/email-templates", $welcomeTemplate);
printResult('1.1 Create Welcome Email Template', $result);
$welcomeTemplateId = $result['data']['id'] ?? null;

// Template 2: Password Reset
$passwordResetTemplate = [
    'name' => 'Password Reset Template',
    'subject_template' => 'Password Reset Request - {company_name}',
    'html_body_template' => '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h1 style="color: #e74c3c;">Password Reset Request</h1>
            <p>Hello {first_name},</p>
            <p>We received a request to reset your password for your {company_name} account.</p>
            <div style="background-color: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
                <h3>Reset Your Password</h3>
                <p>Click the link below to reset your password:</p>
                <a href="{reset_link}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a>
                <p><small>This link will expire in {expiry_hours} hours.</small></p>
            </div>
            <p>If you didn\'t request this password reset, please ignore this email.</p>
            <p>Best regards,<br>The {company_name} Security Team</p>
        </div>
    ',
    'plain_text_body_template' => '
        Password Reset Request
        
        Hello {first_name},
        
        We received a request to reset your password for your {company_name} account.
        
        Reset Your Password:
        Click the link below to reset your password:
        {reset_link}
        
        This link will expire in {expiry_hours} hours.
        
        If you didn\'t request this password reset, please ignore this email.
        
        Best regards,
        The {company_name} Security Team
    '
];

$result = makeRequest('POST', "$baseUrl/email-templates", $passwordResetTemplate);
printResult('1.2 Create Password Reset Template', $result);
$passwordResetTemplateId = $result['data']['id'] ?? null;

// Template 3: Newsletter Template
$newsletterTemplate = [
    'name' => 'Monthly Newsletter Template',
    'subject_template' => '{newsletter_title} - {month} {year}',
    'html_body_template' => '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h1 style="color: #2c3e50;">{newsletter_title}</h1>
            <p style="color: #7f8c8d; font-size: 14px;">{month} {year} Edition</p>
            
            <div style="background-color: #ecf0f1; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h2>Featured Content</h2>
                <p>{featured_content}</p>
            </div>
            
            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h2>Company Updates</h2>
                <p>{company_updates}</p>
            </div>
            
            <div style="background-color: #e8f5e8; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h2>Upcoming Events</h2>
                <p>{upcoming_events}</p>
            </div>
            
            <p>Thank you for being part of our community!</p>
            <p>Best regards,<br>The {company_name} Team</p>
        </div>
    ',
    'plain_text_body_template' => '
        {newsletter_title}
        {month} {year} Edition
        
        Featured Content:
        {featured_content}
        
        Company Updates:
        {company_updates}
        
        Upcoming Events:
        {upcoming_events}
        
        Thank you for being part of our community!
        
        Best regards,
        The {company_name} Team
    '
];

$result = makeRequest('POST', "$baseUrl/email-templates", $newsletterTemplate);
printResult('1.3 Create Newsletter Template', $result);
$newsletterTemplateId = $result['data']['id'] ?? null;

// ============================================================================
// STEP 2: Create Notifications with Templates
// ============================================================================
echo "\n" . str_repeat("📬", 20) . " STEP 2: CREATE NOTIFICATIONS WITH TEMPLATES " . str_repeat("📬", 20) . "\n";

// Notification 1: Welcome notification with template
$welcomeNotification = [
    'subject' => 'Welcome to TechCorp, Template!', // This would be rendered from template
    'body' => 'Dear Template User, We are thrilled to welcome you to TechCorp! Your account has been successfully created. Best regards, The TechCorp Team', // This would be rendered from template
    'user_id' => $userId,
    'email_template_id' => $welcomeTemplateId,
    'attachments' => [
        [
            'file_name' => 'welcome_guide.pdf',
            'mime_type' => 'application/pdf',
            'file_path' => '/uploads/attachments/welcome_guide.pdf'
        ]
    ]
];

$result = makeRequest('POST', "$baseUrl/notifications", $welcomeNotification);
printResult('2.1 Create Welcome Notification with Template', $result);
$welcomeNotificationId = $result['data']['id'] ?? null;

// Notification 2: Password reset with template
$passwordResetNotification = [
    'subject' => 'Password Reset Request - TechCorp',
    'body' => 'Hello Template, We received a request to reset your password. Click the link to reset: https://techcorp.com/reset?token=abc123. Best regards, The TechCorp Security Team',
    'user_id' => $userId,
    'email_template_id' => $passwordResetTemplateId
];

$result = makeRequest('POST', "$baseUrl/notifications", $passwordResetNotification);
printResult('2.2 Create Password Reset Notification with Template', $result);
$passwordResetNotificationId = $result['data']['id'] ?? null;

// Notification 3: Newsletter with template (external email)
$newsletterNotification = [
    'subject' => 'Monthly Tech Update - January 2024',
    'body' => 'Tech Update Newsletter. Featured: New AI features. Updates: System maintenance completed. Events: Webinar next week. Thank you for being part of our community!',
    'recipient_email' => 'newsletter@example.com',
    'email_template_id' => $newsletterTemplateId,
    'attachments' => [
        [
            'file_name' => 'newsletter_january.pdf',
            'mime_type' => 'application/pdf',
            'file_path' => '/uploads/attachments/newsletter_jan_2024.pdf'
        ]
    ]
];

$result = makeRequest('POST', "$baseUrl/notifications", $newsletterNotification);
printResult('2.3 Create Newsletter Notification with Template', $result);
$newsletterNotificationId = $result['data']['id'] ?? null;

// ============================================================================
// STEP 3: Show Template Usage Tracking
// ============================================================================
echo "\n" . str_repeat("📊", 20) . " STEP 3: TEMPLATE USAGE TRACKING " . str_repeat("📊", 20) . "\n";

// List all templates with usage counts
$result = makeRequest('GET', "$baseUrl/email-templates");
printResult('3.1 List All Templates with Usage Counts', $result);

// Show specific template details
if ($welcomeTemplateId) {
    $result = makeRequest('GET', "$baseUrl/email-templates/$welcomeTemplateId");
    printResult('3.2 Welcome Template Details', $result);
}

// ============================================================================
// STEP 4: Send Notifications and Show Results
// ============================================================================
echo "\n" . str_repeat("📤", 20) . " STEP 4: SEND NOTIFICATIONS " . str_repeat("📤", 20) . "\n";

// Send welcome notification
if ($welcomeNotificationId) {
    $result = makeRequest('POST', "$baseUrl/notifications/$welcomeNotificationId/send");
    printResult('4.1 Send Welcome Notification', $result);
}

// Send password reset notification
if ($passwordResetNotificationId) {
    $result = makeRequest('POST', "$baseUrl/notifications/$passwordResetNotificationId/send");
    printResult('4.2 Send Password Reset Notification', $result);
}

// Send newsletter notification
if ($newsletterNotificationId) {
    $result = makeRequest('POST', "$baseUrl/notifications/$newsletterNotificationId/send");
    printResult('4.3 Send Newsletter Notification', $result);
}

// ============================================================================
// STEP 5: Show Final Results
// ============================================================================
echo "\n" . str_repeat("📋", 20) . " STEP 5: FINAL RESULTS " . str_repeat("📋", 20) . "\n";

// List all notifications
$result = makeRequest('GET', "$baseUrl/notifications");
printResult('5.1 All Notifications', $result);

// List all templates with updated usage counts
$result = makeRequest('GET', "$baseUrl/email-templates");
printResult('5.2 Templates with Updated Usage Counts', $result);

// ============================================================================
// SUMMARY
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "📧 EMAIL TEMPLATE WORKFLOW SUMMARY\n";
echo str_repeat("=", 80) . "\n";

echo "✅ Email Template Features:\n";
echo "   • Template-based notifications with placeholders\n";
echo "   • HTML and plain text template support\n";
echo "   • Template usage tracking and analytics\n";
echo "   • Reusable templates for consistent branding\n";
echo "   • Template-based notifications with attachments\n";

echo "\n📊 Template Placeholders Used:\n";
echo "   • {first_name}, {last_name}, {email}\n";
echo "   • {company_name}, {account_type}\n";
echo "   • {reset_link}, {expiry_hours}\n";
echo "   • {newsletter_title}, {month}, {year}\n";
echo "   • {featured_content}, {company_updates}, {upcoming_events}\n";

echo "\n🔄 Template Workflow:\n";
echo "   1. Create email templates with placeholders\n";
echo "   2. Create notifications with email_template_id\n";
echo "   3. System links notification to template\n";
echo "   4. Track template usage for analytics\n";
echo "   5. Render templates with actual data (in real email system)\n";

echo "\n🌐 Swagger UI: http://127.0.0.1:8000/api/doc/\n";
echo "📋 JSON API docs: http://127.0.0.1:8000/api/doc.json\n";
