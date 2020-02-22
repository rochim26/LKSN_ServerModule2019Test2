<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $guarded = [];

    public function boardList()
    {
        return $this->belongsTo(\App\BoardList::class);
    }
}
