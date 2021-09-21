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
        $collectionBuilder = config('nova-media-field.collection_dynamic_builder', false);

        if ($collection) {
            if (array_key_exists($collection, $collections) && isset($collections[$collection]['image_sizes'])) {
                $sizes = $collections[$collection]['image_sizes'] ?: [];
                foreach ($sizes as $key => $size) {
                    $collectionSizes[$key] = $size;
                }
            } else if ($collectionBuilder && is_callable($collectionBuilder)) {
                $generatedConfig = $collectionBuilder($collection) ?: [];
                $sizes = $generatedConfig['image_sizes'] ?: [];
                foreach ($sizes as $key => $size) {
                    $collectionSizes[$key] = $size;
                }
            }
        }

        return array_merge(['thumbnail' => [
            'width' => 150,
            'height' => 150,
            'crop' => true,
        ]], config('nova-media-field.image_sizes'), $collectionSizes);
    }
}
