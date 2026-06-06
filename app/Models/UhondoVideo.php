<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UhondoVideo extends Model
{
    protected $fillable = [
        'title',
        'episode_label',
        'description',
        'video_path',
        'thumbnail_path',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getVideoUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->video_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::disk('public')->url($this->thumbnail_path) : null;
    }
}
