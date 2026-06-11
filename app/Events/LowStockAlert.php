<?php

namespace App\Events;

use App\Models\Medication;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Medication $medication, public int $currentStock, public int $threshold)
    {
    }
}
