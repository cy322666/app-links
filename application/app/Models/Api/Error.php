<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Error extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'text',
        'file',
        'line',
    ];
}
