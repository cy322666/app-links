<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Action extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'type',
        'app_id',
        'os',
        'transition_type',
        'country',
        'campaign_id',
        'cost',
        'click_id',
        'date',
        'zone_id',
        'zone_type',
        'is_install',
        'install_at',
    ];
}
