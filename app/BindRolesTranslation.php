<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BindRolesTranslation extends Model
{
    public function bindRoles() {
        $this->belongsTo('App\BindRoles');
    }
}
