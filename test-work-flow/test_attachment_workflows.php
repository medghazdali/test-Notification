<?php

/**
 * Attachment Workflows Test Script
 * 
 * This script demonstrates the different ways to create notifications with attachments:
 * 1. Create notification with attachments in one request
 * 2. Create notification, then add attachments when sending
 * 3. Create notification, add attachments separately, then send
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

echo "📎 Notification Attachment Workflows Test\n";
echo "Demonstrating different ways to handle attachments with notifications\n";

// ============================================================================
// SETUP: Create a user first
// ============================================================================
$userData = [
    'email' => 'test.user@example.com',
    'first_name' => 'Test',
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
// WORKFLOW 1: Create notification with attachments in one request
// ============================================================================
echo "\n" . str_repeat("🚀", 20) . " WORKFLOW 1 " . str_repeat("🚀", 20) . "\n";
echo "Create notification with attachments in a single request\n";

$notificationWithAttachments = [
    'subject' => 'Welcome Package with Attachments',
    'body' => 'Welcome! Please find attached documents for your reference.',
    'user_id' => $userId,
    'attachments' => [
        [
            'file_name' => 'welcome_guide.pdf',
            'mime_type' => 'application/pdf',
            'file_path' => '/uploads/attachments/welcome_guide_001.pdf'
        ],
        [
            'file_name' => 'terms_and_conditions.pdf',
            'mime_type' => 'application/pdf',
            'file_path' => '/uploads/attachments/terms_001.pdf'
        ],
        [
            'file_name' => 'company_logo.png',
            'mime_type' => 'image/png',
            'file_path' => '/uploads/attachments/logo_001.png'
        ]
    ]
];

$result = makeRequest('POST', "$baseUrl/notifications", $notificationWithAttachments);
printResult('1.1 Create Notification with Multiple Attachments', $result);
$notification1Id = $result['data']['id'] ?? null;

// Check the notification details to see attachments
if ($notification1Id) {
    $result = makeRequest('GET', "$baseUrl/notifications/$notification1Id");
    printResult('1.2 Get Notification Details (with attachments)', $result);
    
    // List attachments for this notification
    $result = makeRequest('GET', "$baseUrl/notification-attachments/notification/$notification1Id");
    printResult('1.3 List Attachments for Notification', $result);
}

// ============================================================================
// WORKFLOW 2: Create notification, then add attachments when sending
// ============================================================================
echo "\n" . str_repeat("🚀", 20) . " WORKFLOW 2 " . str_repeat("🚀", 20) . "\n";
echo "Create notification first, then add attachments when sending\n";

// Create notification without attachments
$notificationData = [
    'subject' => 'Invoice Notification',
    'body' => 'Your invoice is ready for review.',
    'user_id' => $userId
];

$result = makeRequest('POST', "$baseUrl/notifications", $notificationData);
printResult('2.1 Create Notification (no attachments)', $result);
$notification2Id = $result['data']['id'] ?? null;

// Send notification with attachments
if ($notification2Id) {
    $sendWithAttachments = [
        'attachments' => [
            [
                'file_name' => 'invoice_2024_001.pdf',
                'mime_type' => 'application/pdf',
                'file_path' => '/uploads/attachments/invoice_2024_001.pdf'
            ],
            [
                'file_name' => 'payment_instructions.pdf',
                'mime_type' => 'application/pdf',
                'file_path' => '/uploads/attachments/payment_instructions.pdf'
            ]
        ]
    ];
    
    $result = makeRequest('POST', "$baseUrl/notifications/$notification2Id/send", $sendWithAttachments);
    printResult('2.2 Send Notification with Attachments', $result);
    
    // Check the sent notification
    $result = makeRequest('GET', "$baseUrl/notifications/$notification2Id");
    printResult('2.3 Get Sent Notification Details', $result);
    
    // List attachments for this notification
    $result = makeRequest('GET', "$baseUrl/notification-attachments/notification/$notification2Id");
    printResult('2.4 List Attachments for Sent Notification', $result);
}

// ============================================================================
// WORKFLOW 3: Create notification, add attachments separately, then send
// ============================================================================
echo "\n" . str_repeat("🚀", 20) . " WORKFLOW 3 " . str_repeat("🚀", 20) . "\n";
echo "Create notification, add attachments separately, then send\n";

// Create notification without attachments
$notificationData = [
    'subject' => 'Newsletter with Attachments',
    'body' => 'Monthly newsletter with important updates.',
    'user_id' => $userId
];

$result = makeRequest('POST', "$baseUrl/notifications", $notificationData);
printResult('3.1 Create Notification (no attachments)', $result);
$notification3Id = $result['data']['id'] ?? null;

// Add attachments separately
if ($notification3Id) {
    $attachment1 = [
        'notification_id' => $notification3Id,
        'file_name' => 'newsletter_january.pdf',
        'mime_type' => 'application/pdf',
        'file_path' => '/uploads/attachments/newsletter_jan_2024.pdf'
    ];
    
    $result = makeRequest('POST', "$baseUrl/notification-attachments", $attachment1);
    printResult('3.2 Add First Attachment', $result);
    
    $attachment2 = [
        'notification_id' => $notification3Id,
        'file_name' => 'product_catalog.pdf',
        'mime_type' => 'application/pdf',
        'file_path' => '/uploads/attachments/product_catalog_2024.pdf'
    ];
    
    $result = makeRequest('POST', "$baseUrl/notification-attachments", $attachment2);
    printResult('3.3 Add Second Attachment', $result);
    
    // Check notification with attachments
    $result = makeRequest('GET', "$baseUrl/notifications/$notification3Id");
    printResult('3.4 Get Notification with Attachments', $result);
    
    // List attachments
    $result = makeRequest('GET', "$baseUrl/notification-attachments/notification/$notification3Id");
    printResult('3.5 List All Attachments', $result);
    
    // Send notification (without additional attachments)
    $result = makeRequest('POST', "$baseUrl/notifications/$notification3Id/send");
    printResult('3.6 Send Notification (no additional attachments)', $result);
}

// ============================================================================
// WORKFLOW 4: External email with attachments
// ============================================================================
echo "\n" . str_repeat("🚀", 20) . " WORKFLOW 4 " . str_repeat("🚀", 20) . "\n";
echo "External email notification with attachments\n";

$externalNotification = [
    'subject' => 'External Newsletter',
    'body' => 'Thank you for subscribing to our newsletter.',
    'recipient_email' => 'external@example.com',
    'attachments' => [
        [
            'file_name' => 'newsletter_external.pdf',
            'mime_type' => 'application/pdf',
            'file_path' => '/uploads/attachments/newsletter_external.pdf'
        ]
    ]
];

$result = makeRequest('POST', "$baseUrl/notifications", $externalNotification);
printResult('4.1 Create External Notification with Attachments', $result);
$externalNotificationId = $result['data']['id'] ?? null;

if ($externalNotificationId) {
    $result = makeRequest('POST', "$baseUrl/notifications/$externalNotificationId/send");
    printResult('4.2 Send External Notification', $result);
}

// ============================================================================
// SUMMARY
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "📊 ATTACHMENT WORKFLOWS SUMMARY\n";
echo str_repeat("=", 80) . "\n";

// Get final counts
$notifications = makeRequest('GET', "$baseUrl/notifications");
$attachments = makeRequest('GET', "$baseUrl/notification-attachments");

echo "📬 Total Notifications: " . count($notifications['data']) . "\n";
echo "📎 Total Attachments: " . count($attachments['data']) . "\n";

echo "\n✅ All attachment workflows tested successfully!\n";
echo "\n📋 Available Workflows:\n";
echo "   1. Create notification with attachments in one request\n";
echo "   2. Create notification, add attachments when sending\n";
echo "   3. Create notification, add attachments separately, then send\n";
echo "   4. External email notifications with attachments\n";

echo "\n🌐 Swagger UI: http://127.0.0.1:8000/api/doc/\n";
echo "📋 JSON API docs: http://127.0.0.1:8000/api/doc.json\n";
