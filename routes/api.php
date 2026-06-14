<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PharmacyController;
use App\Http\Controllers\Api\MedicationController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\DistributorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\MedicalCertificateController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\LoyalPatientController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\InventoryAnalyticsController;

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password reset (public)
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);
Route::post('/password/verify-token', [PasswordResetController::class, 'verifyToken']);

// Public read-only routes
Route::get('/pharmacies', [PharmacyController::class, 'index']);
Route::get('/pharmacies/{pharmacy}', [PharmacyController::class, 'show']);
Route::get('/pharmacies/{pharmacy}/medications', [PharmacyController::class, 'medications']);
Route::get('/pharmacies/{pharmacy}/reviews', [ReviewController::class, 'pharmacyReviews']);
Route::get('/medications', [MedicationController::class, 'index']);
Route::get('/medications/barcode/{barcode}', [MedicationController::class, 'scanBarcode']);
Route::get('/medications/{medication}', [MedicationController::class, 'show']);
Route::get('/distributors', [DistributorController::class, 'index']);
Route::get('/distributors/{distributor}', [DistributorController::class, 'show']);
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{review}', [ReviewController::class, 'show']);

// Authenticated routes
Route::middleware(['auth:sanctum', 'audit'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // User features
    Route::get('/user/medications/search', [UserController::class, 'searchMedications']);
    Route::get('/user/pharmacies/nearby', [UserController::class, 'nearbyPharmacies']);
    Route::get('/user/reservations', [UserController::class, 'myReservations']);
    Route::get('/user/reservations/summary', [UserController::class, 'reservationSummary']);
    Route::get('/user/notification-preferences', [UserController::class, 'notificationPreferences']);
    Route::put('/user/notification-preferences', [UserController::class, 'updateNotificationPreferences']);
    Route::patch('/user/notification-preferences', [UserController::class, 'updateNotificationPreferences']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{pharmacy}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{pharmacy}', [FavoriteController::class, 'destroy']);
    Route::get('/favorites/{pharmacy}/check', [FavoriteController::class, 'check']);

    // Pharmacies
    Route::post('/pharmacies', [PharmacyController::class, 'store']);
    Route::put('/pharmacies/{pharmacy}', [PharmacyController::class, 'update']);
    Route::patch('/pharmacies/{pharmacy}', [PharmacyController::class, 'update']);
    Route::delete('/pharmacies/{pharmacy}', [PharmacyController::class, 'destroy']);

    // Medications
    Route::post('/medications', [MedicationController::class, 'store']);
    Route::put('/medications/{medication}', [MedicationController::class, 'update']);
    Route::patch('/medications/{medication}', [MedicationController::class, 'update']);
    Route::delete('/medications/{medication}', [MedicationController::class, 'destroy']);
    Route::get('/medications/{medication}/stock-history', [MedicationController::class, 'stockHistory']);
    Route::post('/medications/{medication}/adjust-stock', [MedicationController::class, 'adjustStock']);
    Route::post('/medications/{medication}/purchase', [MedicationController::class, 'purchase']);

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::patch('/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::patch('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // Distributors
    Route::post('/distributors', [DistributorController::class, 'store']);
    Route::get('/distributors/{distributor}', [DistributorController::class, 'show']);
    Route::put('/distributors/{distributor}', [DistributorController::class, 'update']);
    Route::patch('/distributors/{distributor}', [DistributorController::class, 'update']);
    Route::delete('/distributors/{distributor}', [DistributorController::class, 'destroy']);
    Route::get('/distributors/{distributor}/orders', [DistributorController::class, 'orders']);
    Route::get('/distributors/{distributor}/deliveries', [DistributorController::class, 'deliveries']);
    Route::patch('/distributors/{distributor}/orders/{order}/status', [DistributorController::class, 'updateOrderStatus']);
    Route::get('/distributors/{distributor}/analytics', [DistributorController::class, 'analytics']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::patch('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    // Deliveries
    Route::get('/deliveries', [DeliveryController::class, 'index']);
    Route::post('/deliveries', [DeliveryController::class, 'store']);
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show']);
    Route::put('/deliveries/{delivery}', [DeliveryController::class, 'update']);
    Route::patch('/deliveries/{delivery}', [DeliveryController::class, 'update']);
    Route::patch('/deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus']);
    Route::get('/deliveries/track/{trackingNumber}', [DeliveryController::class, 'trackByNumber']);
    Route::delete('/deliveries/{delivery}', [DeliveryController::class, 'destroy']);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    Route::put('/payments/{payment}', [PaymentController::class, 'update']);
    Route::patch('/payments/{payment}', [PaymentController::class, 'update']);
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);

    // Medical Certificates
    Route::get('/medical-certificates', [MedicalCertificateController::class, 'index']);
    Route::post('/medical-certificates', [MedicalCertificateController::class, 'store']);
    Route::get('/medical-certificates/{medicalCertificate}', [MedicalCertificateController::class, 'show']);
    Route::put('/medical-certificates/{medicalCertificate}', [MedicalCertificateController::class, 'update']);
    Route::patch('/medical-certificates/{medicalCertificate}', [MedicalCertificateController::class, 'update']);
    Route::delete('/medical-certificates/{medicalCertificate}', [MedicalCertificateController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::get('/notifications/stats', [NotificationController::class, 'stats']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    // File downloads
    Route::get('/files/prescriptions/{reservation}', [FileController::class, 'downloadPrescription']);
    Route::get('/files/medical-certificates/{medicalCertificate}', [FileController::class, 'downloadCertificate']);

    // User profile photo
    Route::post('/user/photo', [UserController::class, 'updatePhoto']);
    Route::delete('/user/photo', [UserController::class, 'deletePhoto']);

    // Chatbot
    Route::post('/chatbot/message', [ChatbotController::class, 'chat']);

    // Loyal patients
    Route::get('/loyal-patients', [LoyalPatientController::class, 'index']);
    Route::get('/loyal-patients/{userId}', [LoyalPatientController::class, 'show']);

    // Messaging
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/unread-count', [ConversationController::class, 'unreadCount']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/conversations/{conversation}/read', [ConversationController::class, 'markAsRead']);
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

    // Inventory Analytics
    Route::get('/inventory/expiry-monitoring', [InventoryAnalyticsController::class, 'expiryMonitoring']);
    Route::get('/inventory/low-stock-forecast', [InventoryAnalyticsController::class, 'lowStockForecast']);
    Route::get('/inventory/movement-history', [InventoryAnalyticsController::class, 'movementHistory']);
    Route::get('/inventory/reorder-recommendations', [InventoryAnalyticsController::class, 'reorderRecommendations']);
    Route::get('/inventory/trends', [InventoryAnalyticsController::class, 'trends']);

    // Exports
    Route::get('/export/medications', [ExportController::class, 'medications']);
    Route::get('/export/reservations', [ExportController::class, 'reservations']);
    Route::get('/export/orders', [ExportController::class, 'orders']);
    Route::get('/export/stock-movements', [ExportController::class, 'stockMovements']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:ADMIN', 'audit'])->group(function () {
    Route::get('/admin', function () {
        return response()->json([
            'success' => true,
            'message' => 'Welcome Admin',
            'data' => [
                'endpoints' => [
                    'dashboard' => '/api/admin/dashboard',
                    'users' => '/api/admin/users',
                    'analytics' => '/api/admin/analytics/*',
                    'verify_pharmacy' => 'POST /api/pharmacies/{id}/verify',
                ]
            ],
            'errors' => [],
        ]);
    });

    // Dashboard analytics
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/analytics/revenue', [AdminController::class, 'revenueChart']);
    Route::get('/admin/analytics/reservations', [AdminController::class, 'reservationChart']);
    Route::get('/admin/analytics/top-medications', [AdminController::class, 'topMedications']);
    Route::get('/admin/analytics/top-pharmacies', [AdminController::class, 'topPharmacies']);
    Route::get('/admin/analytics/activity', [AdminController::class, 'activityTimeline']);

    // User management
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/{user}', [UserController::class, 'show']);
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::put('/admin/users/{user}', [UserController::class, 'update']);
    Route::patch('/admin/users/{user}', [UserController::class, 'update']);
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy']);
    Route::post('/admin/users/{user}/ban', [UserController::class, 'ban']);
    Route::post('/admin/users/{user}/unban', [UserController::class, 'unban']);
    Route::post('/admin/users/{user}/role', [UserController::class, 'changeRole']);
    Route::post('/admin/users/{user}/revoke-tokens', [UserController::class, 'revokeTokens']);

    // Pharmacy verification
    Route::post('/pharmacies/{pharmacy}/verify', [PharmacyController::class, 'verify']);
});
