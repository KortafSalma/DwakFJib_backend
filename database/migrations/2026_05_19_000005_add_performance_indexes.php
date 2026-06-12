<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->createSqliteIndex('users', 'users_role_index', ['role']);
            $this->createSqliteIndex('users', 'users_email_index', ['email']);
            $this->createSqliteIndex('users', 'users_created_at_index', ['created_at']);

            $this->createSqliteIndex('reservations', 'reservations_status_index', ['status']);
            $this->createSqliteIndex('reservations', 'reservations_user_id_index', ['user_id']);
            $this->createSqliteIndex('reservations', 'reservations_pharmacy_id_index', ['pharmacy_id']);
            $this->createSqliteIndex('reservations', 'reservations_status_created_at_index', ['status', 'created_at']);
            $this->createSqliteIndex('reservations', 'reservations_expires_at_index', ['expires_at']);

            $this->createSqliteIndex('medications', 'medications_pharmacy_id_index', ['pharmacy_id']);
            $this->createSqliteIndex('medications', 'medications_category_index', ['category']);
            $this->createSqliteIndex('medications', 'medications_stock_index', ['stock']);
            $this->createSqliteIndex('medications', 'medications_price_index', ['price']);
            $this->createSqliteIndex('medications', 'medications_name_index', ['name']);

            $this->createSqliteIndex('pharmacies', 'pharmacies_city_index', ['city']);
            $this->createSqliteIndex('pharmacies', 'pharmacies_is_verified_index', ['is_verified']);
            $this->createSqliteIndex('pharmacies', 'pharmacies_latitude_longitude_index', ['latitude', 'longitude']);
            $this->createSqliteIndex('pharmacies', 'pharmacies_rating_index', ['rating']);

            $this->createSqliteIndex('orders', 'orders_status_index', ['status']);
            $this->createSqliteIndex('orders', 'orders_pharmacy_id_index', ['pharmacy_id']);
            $this->createSqliteIndex('orders', 'orders_distributor_id_index', ['distributor_id']);
            $this->createSqliteIndex('orders', 'orders_status_created_at_index', ['status', 'created_at']);

            $this->createSqliteIndex('notifications', 'notifications_user_id_read_at_index', ['user_id', 'read_at']);
            $this->createSqliteIndex('notifications', 'notifications_type_index', ['type']);

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index');
            $table->index('email', 'users_email_index');
            $table->index('created_at', 'users_created_at_index');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->index('status', 'reservations_status_index');
            $table->index('user_id', 'reservations_user_id_index');
            $table->index('pharmacy_id', 'reservations_pharmacy_id_index');
            $table->index(['status', 'created_at'], 'reservations_status_created_at_index');
            $table->index('expires_at', 'reservations_expires_at_index');
        });

        Schema::table('medications', function (Blueprint $table) {
            $table->index('pharmacy_id', 'medications_pharmacy_id_index');
            $table->index('category', 'medications_category_index');
            $table->index('stock', 'medications_stock_index');
            $table->index('price', 'medications_price_index');
            $table->fullText('name', 'medications_name_fulltext_index');
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->index('city', 'pharmacies_city_index');
            $table->index('is_verified', 'pharmacies_is_verified_index');
            $table->index(['latitude', 'longitude'], 'pharmacies_latitude_longitude_index');
            $table->index('rating', 'pharmacies_rating_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status', 'orders_status_index');
            $table->index('pharmacy_id', 'orders_pharmacy_id_index');
            $table->index('distributor_id', 'orders_distributor_id_index');
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at'], 'notifications_user_id_read_at_index');
            $table->index('type', 'notifications_type_index');
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->dropSqliteIndex('users_role_index');
            $this->dropSqliteIndex('users_email_index');
            $this->dropSqliteIndex('users_created_at_index');

            $this->dropSqliteIndex('reservations_status_index');
            $this->dropSqliteIndex('reservations_user_id_index');
            $this->dropSqliteIndex('reservations_pharmacy_id_index');
            $this->dropSqliteIndex('reservations_status_created_at_index');
            $this->dropSqliteIndex('reservations_expires_at_index');

            $this->dropSqliteIndex('medications_pharmacy_id_index');
            $this->dropSqliteIndex('medications_category_index');
            $this->dropSqliteIndex('medications_stock_index');
            $this->dropSqliteIndex('medications_price_index');
            $this->dropSqliteIndex('medications_name_index');

            $this->dropSqliteIndex('pharmacies_city_index');
            $this->dropSqliteIndex('pharmacies_is_verified_index');
            $this->dropSqliteIndex('pharmacies_latitude_longitude_index');
            $this->dropSqliteIndex('pharmacies_rating_index');

            $this->dropSqliteIndex('orders_status_index');
            $this->dropSqliteIndex('orders_pharmacy_id_index');
            $this->dropSqliteIndex('orders_distributor_id_index');
            $this->dropSqliteIndex('orders_status_created_at_index');

            $this->dropSqliteIndex('notifications_user_id_read_at_index');
            $this->dropSqliteIndex('notifications_type_index');

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_email_index');
            $table->dropIndex('users_created_at_index');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_status_index');
            $table->dropIndex('reservations_user_id_index');
            $table->dropIndex('reservations_pharmacy_id_index');
            $table->dropIndex('reservations_status_created_at_index');
            $table->dropIndex('reservations_expires_at_index');
        });

        Schema::table('medications', function (Blueprint $table) {
            $table->dropIndex('medications_pharmacy_id_index');
            $table->dropIndex('medications_category_index');
            $table->dropIndex('medications_stock_index');
            $table->dropIndex('medications_price_index');
            $table->dropIndex('medications_name_fulltext_index');
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropIndex('pharmacies_city_index');
            $table->dropIndex('pharmacies_is_verified_index');
            $table->dropIndex('pharmacies_latitude_longitude_index');
            $table->dropIndex('pharmacies_rating_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_pharmacy_id_index');
            $table->dropIndex('orders_distributor_id_index');
            $table->dropIndex('orders_status_created_at_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_id_read_at_index');
            $table->dropIndex('notifications_type_index');
        });
    }

    protected function createSqliteIndex(string $table, string $indexName, array $columns): void
    {
        $columns = implode(', ', array_map(fn (string $column) => '"' . $column . '"', $columns));
        DB::statement("CREATE INDEX IF NOT EXISTS \"{$indexName}\" ON \"{$table}\" ({$columns})");
    }

    protected function dropSqliteIndex(string $indexName): void
    {
        DB::statement("DROP INDEX IF EXISTS \"{$indexName}\"");
    }
};
