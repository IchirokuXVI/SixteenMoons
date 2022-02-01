<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    //A role has multiple users while users can have only one role. One to many relationship, method belongsTo('App\Role') in User
    public function user() {
        return $this->hasMany('App\User');
    }
}
