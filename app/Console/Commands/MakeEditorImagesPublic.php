<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeEditorImagesPublic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:make-public {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make all editor images public so they can be viewed by all workspace members';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = config('filesystems.default_upload_disk', 'do_spaces');
        $storage = Storage::disk($disk);

        $this->info("Scanning for editor images on disk: {$disk}");

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get all directories (company IDs)
        $directories = $storage->directories();
        $totalUpdated = 0;

        foreach ($directories as $directory) {
            // Look for editor-images folder in each company directory
            $editorImagesPath = $directory . '/editor-images';

            if (!$storage->exists($editorImagesPath)) {
                continue;
            }

            $this->info("Processing: {$editorImagesPath}");

            // Get all files recursively
            $files = $storage->allFiles($editorImagesPath);

            foreach ($files as $file) {
                if ($dryRun) {
                    $this->line("  Would update: {$file}");
                } else {
                    try {
                        // Set visibility to public
                        $storage->setVisibility($file, 'public');
                        $this->line("  Updated: {$file}");
                    } catch (\Exception $e) {
                        $this->error("  Failed to update {$file}: {$e->getMessage()}");
                        continue;
                    }
                }
                $totalUpdated++;
            }
        }

        if ($dryRun) {
            $this->info("Would update {$totalUpdated} files. Run without --dry-run to apply changes.");
        } else {
            $this->info("Successfully updated {$totalUpdated} files to public visibility.");
        }

        return Command::SUCCESS;
    }
}
