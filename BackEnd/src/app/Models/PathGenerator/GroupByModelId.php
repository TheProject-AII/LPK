<?php

namespace App\Models\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Illuminate\Support\Facades\Log;

class GroupByModelId implements PathGenerator
{
    public function getPath(Media $media) : string
    {
        return $this->getBasePath($media).'/';

        // return md5($media->id).'/';
    }

    public function getPathForConversions(Media $media) : string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }

     /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $prefix = config('media-library.prefix', '');
        if ($prefix !== '') {
            return $prefix . '/' . $media->model_id. '/'. $media->id;
        }

        return $media->model_id . '/'. $media->id;
    }
}
