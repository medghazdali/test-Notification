#!/bin/bash

# Notification System Test Runner
# This script runs all tests related to the notification system

echo "🧪 Running Notification System Tests..."
echo "========================================"

echo ""
echo "📋 Running NotificationController Tests..."
./vendor/bin/phpunit tests/Controller/NotificationControllerTest.php

echo ""
echo "⚙️  Running NotificationService Tests..."
./vendor/bin/phpunit tests/Service/NotificationServiceTest.php

echo ""
echo "🎯 Running All Notification Tests Together..."
./vendor/bin/phpunit tests/Controller/NotificationControllerTest.php tests/Service/NotificationServiceTest.php

echo ""
echo "✅ All notification tests completed!"
