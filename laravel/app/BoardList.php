<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BoardList extends Model
{
    protected $guarded = [];

    public function board()
    {
        return $this->belongsTo(\App\Board::class);
    }

    public function cards()
    {
        return $this->hasMany(\App\Card::class, 'list_id');
    }
}
