<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Screen\AsSource;

class App extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'uuid',
        'url',
        'name',
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Action::class);
    }
}
