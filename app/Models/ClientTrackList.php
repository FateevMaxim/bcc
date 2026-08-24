<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTrackList extends Model
{
    protected $fillable = [
        'track_code',
        'detail',
        'user_id',
        'status',
    ];
}
