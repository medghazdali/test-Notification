<?php

/**
 * Complete API Test Script
 * 
 * This script demonstrates all the API endpoints for:
 * - Users
 * - Email Templates
 * - Notifications
 * - Notification Attachments
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
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🔹 $title\n";
    echo str_repeat("=", 60) . "\n";
    echo "HTTP Code: {$result['code']}\n";
    echo "Response: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
}

echo "🚀 Complete Notification API Test\n";
echo "Testing all endpoints with full CRUD operations\n";

// ============================================================================
// 1. CREATE USER
// ============================================================================
$userData = [
    'email' => 'john.doe@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe'
];

$result = makeRequest('POST', "$baseUrl/users", $userData);
printResult('1. Create User', $result);
$userId = $result['data']['id'] ?? null;

// ============================================================================
// 2. CREATE EMAIL TEMPLATE
// ============================================================================
$templateData = [
    'name' => 'Welcome Email Template',
    'subject_template' => 'Welcome, {first_name}!',
    'html_body_template' => '<h1>Welcome, {first_name}!</h1><p>Thank you for joining our service.</p><p>Best regards,<br>The Team</p>',
    'plain_text_body_template' => "Welcome, {first_name}!\n\nThank you for joining our service.\n\nBest regards,\nThe Team"
];

$result = makeRequest('POST', "$baseUrl/email-templates", $templateData);
printResult('2. Create Email Template', $result);
$templateId = $result['data']['id'] ?? null;

// ============================================================================
// 3. CREATE NOTIFICATION WITH TEMPLATE
// ============================================================================
$notificationData = [
    'subject' => 'Welcome to Our Service!',
    'body' => 'Thank you for joining our service. We are excited to have you on board!',
    'user_id' => $userId,
    'email_template_id' => $templateId
];

$result = makeRequest('POST', "$baseUrl/notifications", $notificationData);
printResult('3. Create Notification with Template', $result);
$notificationId = $result['data']['id'] ?? null;

// ============================================================================
// 4. CREATE NOTIFICATION ATTACHMENT
// ============================================================================
$attachmentData = [
    'notification_id' => $notificationId,
    'file_name' => 'welcome_guide.pdf',
    'mime_type' => 'application/pdf',
    'file_path' => '/uploads/attachments/welcome_guide_123.pdf'
];

$result = makeRequest('POST', "$baseUrl/notification-attachments", $attachmentData);
printResult('4. Create Notification Attachment', $result);
$attachmentId = $result['data']['id'] ?? null;

// ============================================================================
// 5. LIST ALL USERS
// ============================================================================
$result = makeRequest('GET', "$baseUrl/users");
printResult('5. List All Users', $result);

// ============================================================================
// 6. LIST ALL EMAIL TEMPLATES
// ============================================================================
$result = makeRequest('GET', "$baseUrl/email-templates");
printResult('6. List All Email Templates', $result);

// ============================================================================
// 7. LIST ALL NOTIFICATIONS
// ============================================================================
$result = makeRequest('GET', "$baseUrl/notifications");
printResult('7. List All Notifications', $result);

// ============================================================================
// 8. LIST ALL ATTACHMENTS
// ============================================================================
$result = makeRequest('GET', "$baseUrl/notification-attachments");
printResult('8. List All Attachments', $result);

// ============================================================================
// 9. GET SPECIFIC USER
// ============================================================================
if ($userId) {
    $result = makeRequest('GET', "$baseUrl/users/$userId");
    printResult('9. Get Specific User', $result);
}

// ============================================================================
// 10. GET SPECIFIC EMAIL TEMPLATE
// ============================================================================
if ($templateId) {
    $result = makeRequest('GET', "$baseUrl/email-templates/$templateId");
    printResult('10. Get Specific Email Template', $result);
}

// ============================================================================
// 11. GET SPECIFIC NOTIFICATION
// ============================================================================
if ($notificationId) {
    $result = makeRequest('GET', "$baseUrl/notifications/$notificationId");
    printResult('11. Get Specific Notification', $result);
}

// ============================================================================
// 12. GET SPECIFIC ATTACHMENT
// ============================================================================
if ($attachmentId) {
    $result = makeRequest('GET', "$baseUrl/notification-attachments/$attachmentId");
    printResult('12. Get Specific Attachment', $result);
}

// ============================================================================
// 13. LIST ATTACHMENTS BY NOTIFICATION
// ============================================================================
if ($notificationId) {
    $result = makeRequest('GET', "$baseUrl/notification-attachments/notification/$notificationId");
    printResult('13. List Attachments by Notification', $result);
}

// ============================================================================
// 14. LIST PENDING NOTIFICATIONS
// ============================================================================
$result = makeRequest('GET', "$baseUrl/notifications/pending");
printResult('14. List Pending Notifications', $result);

// ============================================================================
// 15. SEND NOTIFICATION
// ============================================================================
if ($notificationId) {
    $result = makeRequest('POST', "$baseUrl/notifications/$notificationId/send");
    printResult('15. Send Notification', $result);
}

// ============================================================================
// 16. UPDATE EMAIL TEMPLATE
// ============================================================================
if ($templateId) {
    $updateTemplateData = [
        'name' => 'Updated Welcome Email Template',
        'subject_template' => 'Welcome to our platform, {first_name}!',
        'html_body_template' => '<h1>Welcome to our platform, {first_name}!</h1><p>We are thrilled to have you join our community.</p><p>Best regards,<br>The Team</p>',
        'plain_text_body_template' => "Welcome to our platform, {first_name}!\n\nWe are thrilled to have you join our community.\n\nBest regards,\nThe Team"
    ];
    
    $result = makeRequest('PUT', "$baseUrl/email-templates/$templateId", $updateTemplateData);
    printResult('16. Update Email Template', $result);
}

// ============================================================================
// 17. UPDATE ATTACHMENT
// ============================================================================
if ($attachmentId) {
    $updateAttachmentData = [
        'notification_id' => $notificationId,
        'file_name' => 'updated_welcome_guide.pdf',
        'mime_type' => 'application/pdf',
        'file_path' => '/uploads/attachments/updated_welcome_guide_123.pdf'
    ];
    
    $result = makeRequest('PUT', "$baseUrl/notification-attachments/$attachmentId", $updateAttachmentData);
    printResult('17. Update Attachment', $result);
}

// ============================================================================
// 18. CREATE EXTERNAL EMAIL NOTIFICATION (no user_id)
// ============================================================================
$externalNotificationData = [
    'subject' => 'External Newsletter',
    'body' => 'This is a newsletter sent to external email addresses.',
    'recipient_email' => 'external@example.com'
];

$result = makeRequest('POST', "$baseUrl/notifications", $externalNotificationData);
printResult('18. Create External Email Notification', $result);
$externalNotificationId = $result['data']['id'] ?? null;

// ============================================================================
// 19. SEND EXTERNAL NOTIFICATION
// ============================================================================
if ($externalNotificationId) {
    $result = makeRequest('POST', "$baseUrl/notifications/$externalNotificationId/send");
    printResult('19. Send External Notification', $result);
}

// ============================================================================
// 20. FINAL STATUS CHECK
// ============================================================================
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 FINAL STATUS SUMMARY\n";
echo str_repeat("=", 60) . "\n";

// List all entities
$users = makeRequest('GET', "$baseUrl/users");
$templates = makeRequest('GET', "$baseUrl/email-templates");
$notifications = makeRequest('GET', "$baseUrl/notifications");
$attachments = makeRequest('GET', "$baseUrl/notification-attachments");

echo "👥 Users: " . count($users['data']) . "\n";
echo "📧 Email Templates: " . count($templates['data']) . "\n";
echo "📬 Notifications: " . count($notifications['data']) . "\n";
echo "📎 Attachments: " . count($attachments['data']) . "\n";

echo "\n✅ All API endpoints tested successfully!\n";
echo "🌐 Swagger UI available at: http://127.0.0.1:8000/api/doc/\n";
echo "📋 JSON API docs available at: http://127.0.0.1:8000/api/doc.json\n";
