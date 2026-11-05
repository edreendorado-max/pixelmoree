<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtworkView extends Model
{
    public $timestamps = false;

    protected $fillable = ['artwork_id', 'viewer_key', 'created_at'];

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }
}