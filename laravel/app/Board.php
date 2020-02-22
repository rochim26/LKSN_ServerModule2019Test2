<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $guarded = [];
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function boardLists()
    {
        return $this->hasMany(\App\BoardList::class);
    }

    public function boardMembers()
    {
        return $this->hasMany(\App\BoardMember::class);
    }
}
