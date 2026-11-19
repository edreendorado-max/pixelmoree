<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtworkItem extends Model
{
    protected $primaryKey = 'item_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class, 'artwork_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
