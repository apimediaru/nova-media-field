<?php

namespace OptimistDigital\MediaField\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OptimistDigital\MediaField\Classes\MediaHandler;
use OptimistDigital\MediaField\Models\Media;

class RegenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:regenerate-thumbnails
                            {--collection=* : Update media with provided collections name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates media library thumbnails for images';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $Media = config('nova-media-field.media_model');
        $collections = $this->option('collection');
        $medias = [];
        // Only collections option is implemented now
        // TODO: implement id (probably)
        if (!empty($collections) && is_array($collections)) {
            $medias = $Media::whereIn('collection_name', $collections)->get();
        } else {
            $medias = $Media::all();
        }

        /** @var MediaHandler $handler */
        $handler = app()->make(MediaHandler::class);
        $storage = Storage::disk(config('nova-media-field.storage_driver'));
        $updateCount = 0;
        $totalCount = $medias->count();
        $this->output->write("\n");
        foreach ($medias as $media) {
            $mediaPath = $media->path . $media->file_name;
            if ($storage->exists($mediaPath)) {
                try {
                    $generatedImages = $handler->generateImageSizes(
                        file_get_contents($storage->path($mediaPath)),
                        $mediaPath,
                        $media->mime_type,
                        $storage,
                        $media->collection_name,
                    );
                    $media->image_sizes = json_encode($generatedImages);
                    $media->save();
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                    error_log($msg);
                    $this->output->write("<error>" . " $msg \n\n" . "</error>");
                    continue;
                }
            } else {
                Log::debug('Not readable: ' . $rootPath . $media->path . $media->file_name);
            }

            $updateCount++;
            $this->output->write("<info>" . " Updated $updateCount/$totalCount entities \r" . "</info>");
        }

        $this->info("\n\nRegeneration done\n\n");
    }
}
