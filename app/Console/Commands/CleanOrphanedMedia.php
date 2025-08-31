<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

class CleanOrphanedMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example: php artisan media:clean-orphans --dry-run
     */
    protected $signature = 'media:clean-orphans {--dry-run : Show what would be deleted without actually removing it}';

    /**
     * The console command description.
     */
    protected $description = 'Remove orphaned Spatie media files that have no related model';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in DRY RUN mode. No files or DB records will be deleted.');
        } else {
            $this->info('Cleaning orphaned media...');
        }

        $count = 0;

        Media::all()->each(function (Media $media) use (&$count, $dryRun) {
            // Check if the related model still exists
            if (! $media->model()->exists()) {
                $count++;

                if ($dryRun) {
                    $this->warn("Would delete orphaned media ID {$media->id} (file: {$media->file_name}, disk: {$media->disk})");
                } else {
                    $this->warn("Deleting orphaned media ID {$media->id} (file: {$media->file_name}, disk: {$media->disk})");

                    // Delete files from disk
                    Storage::disk($media->disk)->deleteDirectory($media->getPath());

                    // Delete DB record
                    $media->delete();
                }
            }
        });

        $this->info("Total orphaned media found: {$count}");

        if ($dryRun) {
            $this->info('Dry run complete. Nothing was deleted.');
        } else {
            $this->info('Cleanup complete ✅');
        }

        return Command::SUCCESS;
    }
}
