<?php

namespace C14r\DataStore\Console\Commands;

use Illuminate\Console\Command;
use C14r\DataStore\Models\DataStore;

class CleanupExpiredDataStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datastore:cleanup 
                            {--dry-run : Show what would be deleted without deleting}
                            {--namespace= : Only cleanup specific namespace}
                            {--type= : Only cleanup specific storable type (e.g., App\Models\User)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired data store entries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $namespace = $this->option('namespace');
        $type = $this->option('type');

        $this->info('Starting cleanup of expired data store entries...');

        $query = DataStore::expired();

        if ($namespace) {
            $query->inNamespace($namespace);
            $this->info("Filtering by namespace: {$namespace}");
        }

        if ($type) {
            $query->where('storable_type', $type);
            $this->info("Filtering by storable type: {$type}");
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No expired entries found.');
            return Command::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn("DRY RUN: Would delete {$count} expired entries");
            
            // Show some examples
            $examples = $query->limit(5)->get();
            $this->table(
                ['ID', 'Storable Type', 'Storable ID', 'Namespace', 'Key', 'Expired At'],
                $examples->map(fn($item) => [
                    $item->id,
                    $item->storable_type ?? 'global',
                    $item->storable_id ?? '-',
                    $item->namespace ?? '-',
                    $item->key,
                    $item->expires_at->format('Y-m-d H:i:s'),
                ])
            );

            return Command::SUCCESS;
        }

        if (!$this->confirm("Delete {$count} expired entries?")) {
            $this->info('Cleanup cancelled.');
            return Command::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("Successfully deleted {$deleted} expired entries.");

        return Command::SUCCESS;
    }
}
