<?php

namespace App\Console\Commands;

use App\Models\MedicalCertificate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupMedicalCertificates extends Command
{
    protected $signature = 'certificates:cleanup {--days=30 : Delete certificates older than this many days}';

    protected $description = 'Delete expired medical certificates older than the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $expiredCertificates = MedicalCertificate::where('expires_at', '<', $cutoffDate)
            ->orWhere('created_at', '<', $cutoffDate)
            ->get();

        if ($expiredCertificates->isEmpty()) {
            $this->info('No expired medical certificates found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiredCertificates->count()} expired certificates to delete.");

        $bar = $this->output->createProgressBar($expiredCertificates->count());
        $bar->start();

        foreach ($expiredCertificates as $certificate) {
            if ($certificate->file_path) {
                Storage::disk('local')->delete($certificate->file_path);
            }

            $certificate->delete();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully deleted {$expiredCertificates->count()} expired medical certificates.");

        return Command::SUCCESS;
    }
}
