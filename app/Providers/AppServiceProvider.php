<?php

namespace App\Providers;

use App\Models\Pharmacy;
use App\Models\Medication;
use App\Models\Reservation;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\Payment;
use App\Models\MedicalCertificate;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Delivery;
use App\Models\Conversation;
use App\Models\Message;
use App\Policies\PharmacyPolicy;
use App\Policies\MedicationPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\DistributorPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\MedicalCertificatePolicy;
use App\Policies\NotificationPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\DeliveryPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\MessagePolicy;
use App\Events\MessageSent;
use App\Listeners\SendMessageNotification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Gate::policy(Pharmacy::class, PharmacyPolicy::class);
        Gate::policy(Medication::class, MedicationPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);
        Gate::policy(Distributor::class, DistributorPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(MedicalCertificate::class, MedicalCertificatePolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(Delivery::class, DeliveryPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);

        Event::listen(
            MessageSent::class,
            SendMessageNotification::class,
        );
    }
}
