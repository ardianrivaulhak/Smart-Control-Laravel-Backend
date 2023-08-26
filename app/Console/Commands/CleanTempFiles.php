<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CleanTempFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-temp-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete files in the temp folder that are older than one day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $tempFolderPath = storage_path('app/public/temps'); // Change to your temp folder path
        $files = File::files($tempFolderPath); // List all files in the temp folder
        $now = now();
        $cutoffTime = $now->subDay(); // Calculate the cutoff time (one day ago)

        foreach ($files as $file) {
            $fileInfo = pathinfo($file);
            $fileModifiedTime = filemtime($tempFolderPath . '/' . $fileInfo['basename']);

            if ($fileModifiedTime < $cutoffTime->timestamp) {
                File::delete($file);
            }
        }
    }
}
