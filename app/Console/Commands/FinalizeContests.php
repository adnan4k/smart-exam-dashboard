<?php

namespace App\Console\Commands;

use App\Models\Contest;
use App\Services\ContestFinalizer;
use App\Services\ContestService;
use Illuminate\Console\Command;

class FinalizeContests extends Command
{
    protected $signature = 'contests:finalize {--contest= : Finalize one contest by id}';

    protected $description = 'Close finished contests: score stragglers, rank, pay out stars, release the paper';

    public function handle(ContestService $contests, ContestFinalizer $finalizer): int
    {
        // Students who were cut off mid-paper get scored on what reached us.
        $closed = $contests->closeExpiredAttempts();
        if ($closed) {
            $this->info("Closed {$closed} expired attempt(s).");
        }

        $query = Contest::where('status', 'scheduled')->where('ends_at', '<=', now());

        if ($id = $this->option('contest')) {
            $query = Contest::whereKey($id);
        }

        foreach ($query->get() as $contest) {
            $result = $finalizer->finalize($contest);

            if (! ($result['finalized'] ?? false)) {
                $this->warn("Skipped #{$contest->id} {$contest->title}: {$result['reason']}");
                continue;
            }

            $this->info("Finalized #{$contest->id} {$contest->title} - {$result['participants']} participant(s), {$result['paid']} paid.");
        }

        return self::SUCCESS;
    }
}
