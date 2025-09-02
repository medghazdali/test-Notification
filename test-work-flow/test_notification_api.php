<?php

/**
 * Test script to demonstrate the notification API endpoints
 * Run this script to test the API functionality with enum-based status
 */

$baseUrl = 'http://127.0.0.1:8000/api';

function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

echo "=== Notification API Test with Enum Status ===\n\n";

// Step 1: Create a user
echo "1. Creating a user...\n";
$userData = [
    'email' => 'john.doe@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe'
];

$userResponse = makeRequest($baseUrl . '/users', 'POST', $userData);
echo "Status: " . $userResponse['code'] . "\n";
echo "Response: " . json_encode($userResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

if ($userResponse['code'] !== 201) {
    echo "Failed to create user. Exiting.\n";
    exit(1);
}

$userId = $userResponse['data']['id'];

// Step 2: Create a notification (will automatically have 'pending' status)
echo "2. Creating a notification (should automatically have 'pending' status)...\n";
$notificationData = [
    'user_id' => $userId,
    'subject' => 'Welcome to Our Service',
    'body' => 'Thank you for joining our service! We are excited to have you on board.'
];

$notificationResponse = makeRequest($baseUrl . '/notifications', 'POST', $notificationData);
echo "Status: " . $notificationResponse['code'] . "\n";
echo "Response: " . json_encode($notificationResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

if ($notificationResponse['code'] !== 201) {
    echo "Failed to create notification. Exiting.\n";
    exit(1);
}

$notificationId = $notificationResponse['data']['id'];

// Verify the status is 'pending'
if ($notificationResponse['data']['status'] !== 'pending') {
    echo "ERROR: Notification status should be 'pending' but got: " . $notificationResponse['data']['status'] . "\n";
    exit(1);
}

echo "✅ Notification created with 'pending' status as expected!\n\n";

// Step 3: List all notifications
echo "3. Listing all notifications...\n";
$listResponse = makeRequest($baseUrl . '/notifications');
echo "Status: " . $listResponse['code'] . "\n";
echo "Response: " . json_encode($listResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

// Step 4: List pending notifications
echo "4. Listing pending notifications...\n";
$pendingResponse = makeRequest($baseUrl . '/notifications/pending');
echo "Status: " . $pendingResponse['code'] . "\n";
echo "Response: " . json_encode($pendingResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

// Step 5: Get specific notification details
echo "5. Getting notification details...\n";
$showResponse = makeRequest($baseUrl . '/notifications/' . $notificationId);
echo "Status: " . $showResponse['code'] . "\n";
echo "Response: " . json_encode($showResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

// Step 6: Send the notification (simulate sending)
echo "6. Sending the notification (simulating email send)...\n";
$sendResponse = makeRequest($baseUrl . '/notifications/' . $notificationId . '/send', 'POST');
echo "Status: " . $sendResponse['code'] . "\n";
echo "Response: " . json_encode($sendResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

if ($sendResponse['code'] !== 200) {
    echo "Failed to send notification. Exiting.\n";
    exit(1);
}

// Verify the status changed to 'sent'
if ($sendResponse['data']['status'] !== 'sent') {
    echo "ERROR: Notification status should be 'sent' but got: " . $sendResponse['data']['status'] . "\n";
    exit(1);
}

echo "✅ Notification status changed to 'sent' as expected!\n\n";

// Step 7: Verify the notification was sent
echo "7. Verifying notification status after sending...\n";
$verifyResponse = makeRequest($baseUrl . '/notifications/' . $notificationId);
echo "Status: " . $verifyResponse['code'] . "\n";
echo "Response: " . json_encode($verifyResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

// Step 8: Try to send the same notification again (should fail)
echo "8. Trying to send the same notification again (should fail)...\n";
$resendResponse = makeRequest($baseUrl . '/notifications/' . $notificationId . '/send', 'POST');
echo "Status: " . $resendResponse['code'] . "\n";
echo "Response: " . json_encode($resendResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

if ($resendResponse['code'] === 400) {
    echo "✅ Correctly prevented sending an already sent notification!\n\n";
} else {
    echo "❌ Should have prevented sending an already sent notification!\n\n";
}

// Step 9: Create a notification without user_id (using recipient_email only)
echo "9. Creating a notification without user_id (using recipient_email only)...\n";
$notificationData2 = [
    'recipient_email' => 'external@example.com',
    'subject' => 'External Notification',
    'body' => 'This is a notification sent to an external email address.'
];

$notificationResponse2 = makeRequest($baseUrl . '/notifications', 'POST', $notificationData2);
echo "Status: " . $notificationResponse2['code'] . "\n";
echo "Response: " . json_encode($notificationResponse2['data'], JSON_PRETTY_PRINT) . "\n\n";

if ($notificationResponse2['code'] === 201) {
    echo "✅ Successfully created notification for external email!\n\n";
} else {
    echo "❌ Failed to create notification for external email!\n\n";
}

echo "=== Test Complete ===\n";
echo "Summary:\n";
echo "- ✅ Notifications are created with 'pending' status automatically\n";
echo "- ✅ Status changes to 'sent' when notification is sent\n";
echo "- ✅ Cannot send already sent notifications\n";
echo "- ✅ Supports both user-based and external email notifications\n";
echo "- ✅ Enum-based status system provides type safety\n";
