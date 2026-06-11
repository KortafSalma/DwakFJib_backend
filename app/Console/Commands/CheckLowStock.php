<?php

namespace App\Console\Commands;

use App\Models\Medication;
use App\Models\Notification;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature = 'stock:check-low';

    protected $description = 'Check for low stock medications and notify pharmacy owners';

    public function handle(): int
    {
        $lowStockMedications = Medication::lowStock()->with('pharmacy.user')->get();

        foreach ($lowStockMedications as $medication) {
            Notification::create([
                'user_id' => $medication->pharmacy->user_id,
                'title' => 'Low Stock Alert',
                'message' => "{$medication->name} is running low ({$medication->stock} remaining).",
                'type' => 'ALERT',
                'metadata' => [
                    'medication_id' => $medication->id,
                    'current_stock' => $medication->stock,
                    'threshold' => $medication->low_stock_threshold,
                ],
            ]);
        }

        $this->info("Checked low stock. Found {$lowStockMedications->count()} medications below threshold.");

        return Command::SUCCESS;
    }
}
