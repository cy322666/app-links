<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Screen\AsSource;

class Link extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'app_id',
        'body',
        'name',
        'uuid',
        'is_prelanding',
        'prelanding_url',
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
