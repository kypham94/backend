<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['title', 'time', 'rss'];

    public function getAll() {
        return self::all();
    }
    
}
