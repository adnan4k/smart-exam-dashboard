<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\Subscription;
use Illuminate\Console\Command;

class GrandfatherLegacySubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:grandfather-legacy {--dry-run : Only show how many subscriptions would be updated without applying changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grandfather legacy subscriptions where package_id is NULL into the All Access package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $allAccess = Package::where('slug', Package::SLUG_ALL_ACCESS)->first();

        if (! $allAccess) {
            $this->error("All Access package ('all_access') was not found. Please run db:seed --class=PackageSeeder first.");
            return Command::FAILURE;
        }

        $query = Subscription::whereNull('package_id');
        $count = $query->count();

        if ($count === 0) {
            $this->info('No legacy subscriptions with NULL package_id were found. Everything is up to date.');
            return Command::SUCCESS;
        }

        $this->info("Found {$count} subscription(s) with NULL package_id.");

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN mode enabled. No changes were made.');
            return Command::SUCCESS;
        }

        $updated = Subscription::whereNull('package_id')->update([
            'package_id' => $allAccess->id,
            'updated_at' => now(),
        ]);

        $this->info("Successfully grandfathered {$updated} subscription(s) to '{$allAccess->name}' (ID: {$allAccess->id}).");

        return Command::SUCCESS;
    }
}
