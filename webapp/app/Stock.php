<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function topics() {
        return $this->hasMany('App\Topic');
    }
}
