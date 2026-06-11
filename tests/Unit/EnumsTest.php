<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Enums\DeliveryStatus;
use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PharmacyStatus;
use App\Enums\ReservationStatus;
use App\Enums\StockMovementType;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_role_enum(): void
    {
        $this->assertTrue(Role::ADMIN->label() === 'Administrator');
        $this->assertTrue(Role::PHARMACY->label() === 'Pharmacy');
        $this->assertTrue(Role::DISTRIBUTOR->label() === 'Distributor');
        $this->assertTrue(Role::USER->label() === 'User');
        $this->assertContains('ADMIN', Role::values());
        $this->assertContains('USER', Role::values());
        $this->assertCount(4, Role::values());
    }

    public function test_pharmacy_status_enum(): void
    {
        $this->assertTrue(PharmacyStatus::PENDING->value === 'PENDING');
        $this->assertTrue(PharmacyStatus::VERIFIED->value === 'VERIFIED');
        $this->assertTrue(PharmacyStatus::REJECTED->value === 'REJECTED');
        $this->assertTrue(PharmacyStatus::SUSPENDED->value === 'SUSPENDED');
    }

    public function test_reservation_status_enum(): void
    {
        $this->assertTrue(ReservationStatus::PENDING->value === 'PENDING');
        $this->assertTrue(ReservationStatus::CONFIRMED->value === 'CONFIRMED');
        $this->assertTrue(ReservationStatus::PAID->value === 'PAID');
        $this->assertTrue(ReservationStatus::COMPLETED->value === 'COMPLETED');
        $this->assertTrue(ReservationStatus::CANCELLED->value === 'CANCELLED');
        $this->assertTrue(ReservationStatus::EXPIRED->value === 'EXPIRED');
    }

    public function test_delivery_status_enum(): void
    {
        $this->assertTrue(DeliveryStatus::PENDING->value === 'PENDING');
        $this->assertTrue(DeliveryStatus::IN_TRANSIT->value === 'IN_TRANSIT');
        $this->assertTrue(DeliveryStatus::DELIVERED->value === 'DELIVERED');
        $this->assertTrue(DeliveryStatus::FAILED->value === 'FAILED');
    }

    public function test_order_status_enum(): void
    {
        $this->assertTrue(OrderStatus::PENDING->value === 'PENDING');
        $this->assertTrue(OrderStatus::PROCESSING->value === 'PROCESSING');
        $this->assertTrue(OrderStatus::SHIPPED->value === 'SHIPPED');
        $this->assertTrue(OrderStatus::DELIVERED->value === 'DELIVERED');
        $this->assertTrue(OrderStatus::CANCELLED->value === 'CANCELLED');
    }

    public function test_payment_status_enum(): void
    {
        $this->assertTrue(PaymentStatus::PENDING->value === 'PENDING');
        $this->assertTrue(PaymentStatus::COMPLETED->value === 'COMPLETED');
        $this->assertTrue(PaymentStatus::FAILED->value === 'FAILED');
        $this->assertTrue(PaymentStatus::REFUNDED->value === 'REFUNDED');
    }

    public function test_notification_type_enum(): void
    {
        $this->assertTrue(NotificationType::ALERT->value === 'ALERT');
        $this->assertTrue(NotificationType::RESERVATION->value === 'RESERVATION');
        $this->assertTrue(NotificationType::ORDER->value === 'ORDER');
        $this->assertTrue(NotificationType::DELIVERY->value === 'DELIVERY');
        $this->assertTrue(NotificationType::STOCK->value === 'STOCK');
        $this->assertTrue(NotificationType::SYSTEM->value === 'SYSTEM');
        $this->assertTrue(NotificationType::PAYMENT->value === 'PAYMENT');
    }

    public function test_stock_movement_type_enum(): void
    {
        $this->assertTrue(StockMovementType::IN->value === 'IN');
        $this->assertTrue(StockMovementType::OUT->value === 'OUT');
        $this->assertTrue(StockMovementType::ADJUSTMENT->value === 'ADJUSTMENT');
        $this->assertTrue(StockMovementType::RETURN->value === 'RETURN');
        $this->assertTrue(StockMovementType::EXPIRED->value === 'EXPIRED');
    }
}
