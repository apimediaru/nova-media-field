<?php


namespace OptimistDigital\MediaField;

use Laravel\Nova\Tool;

class NovaMediaLibrary extends Tool
{

    public function renderNavigation()
    {
        return view('nova-media::navigation');
    }

    public static function getImageSizes($collection = false)
    {
        $collectionSizes = [];
        $collections = config('nova-media-field.collections') ?: [];

        if ($collection && array_key_exists($collection, $collections)) {
            $sizes = $collections[$collection]['image_sizes'] ?: [];
            foreach ($sizes as $size) {
                $collectionSizes[] = $size;
            }
        }

        return array_merge(['thumbnail' => [
            'width' => 150,
            'height' => 150,
            'crop' => true,
        ]], config('nova-media-field.image_sizes', $collectionSizes));
    }
}
