<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyEventAggregate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'date', 'event_name', 'event_category', 'total_count', 'unique_visitors',
    ];

    protected $casts = [
        'date'            => 'date',
        'total_count'     => 'integer',
        'unique_visitors' => 'integer',
    ];
}