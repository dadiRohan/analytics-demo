<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_id', 'event_name', 'event_category', 'properties',
        'anonymous_id', 'session_id', 'user_id', 'page_url',
        'user_agent', 'ip', 'occurred_at', 'received_at',
    ];

    protected $casts = [
        'properties'  => 'array',
        'occurred_at' => 'datetime',
        'received_at' => 'datetime',
    ];
}