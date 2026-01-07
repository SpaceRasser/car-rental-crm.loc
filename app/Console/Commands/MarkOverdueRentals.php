<?php

namespace App\Console\Commands;

use App\Models\Rental;
use Illuminate\Console\Command;

class MarkOverdueRentals extends Command
{
    protected $signature = 'rentals:mark-overdue {--dry-run}';
    protected $description = 'Mark rentals as overdue when ends_at is in the past and rental is not closed/cancelled.';

    public function handle(): int
    {
        $now = now();

        $query = Rental::query()
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->whereNull('returned_at')
            ->where('ends_at', '<', $now);

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("DRY RUN: would mark {$count} rentals as overdue.");
            return self::SUCCESS;
        }

        $updated = $query->update(['status' => 'overdue']);

        $this->info("Marked {$updated} rentals as overdue (matched {$count}).");
        return self::SUCCESS;
    }
}
