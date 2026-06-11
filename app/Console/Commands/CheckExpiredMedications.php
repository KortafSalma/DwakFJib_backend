<?php

namespace App\Console\Commands;

use App\Models\Medication;
use App\Models\Notification;
use Illuminate\Console\Command;

class CheckExpiredMedications extends Command
{
    protected $signature = 'medications:check-expired';

    protected $description = 'Check for expired medications and notify pharmacy owners';

    public function handle(): int
    {
        $expiredMedications = Medication::expired()->with('pharmacy.user')->get();

        foreach ($expiredMedications as $medication) {
            Notification::create([
                'user_id' => $medication->pharmacy->user_id,
                'title' => 'Medication Expired',
                'message' => "{$medication->name} (Batch: {$medication->batch_number}) has expired on {$medication->expiry_date->format('Y-m-d')}.",
                'type' => 'ALERT',
                'metadata' => [
                    'medication_id' => $medication->id,
                    'expiry_date' => $medication->expiry_date->toDateString(),
                ],
            ]);
        }

        $this->info("Found {$expiredMedications->count()} expired medications.");

        return Command::SUCCESS;
    }
}
