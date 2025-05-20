<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    public $timestamps = true;

    protected $table = 'songs';

    protected $fillable = [
        'title',
        'artist',
        'album',
        'genre',
        'cover_img',
        'audio_file',
        'duration',
        'release_date',
        'description',
        'created_at',
        'updated_at',
        'cover_url',
        'audio_url',
    ];
}
