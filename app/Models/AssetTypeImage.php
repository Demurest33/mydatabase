<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetTypeImage extends Model
{
    protected $primaryKey = 'type';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = ['type', 'image'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/type-images/' . $this->image) : null;
    }
}
