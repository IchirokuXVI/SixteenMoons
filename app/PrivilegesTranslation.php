<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrivilegesTranslation extends Model
{
    public function privilege() {
        $this->belongsTo('App\Privilege');
    }
}
