<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BindRoles extends Model
{
    public function customRoles() {
        return $this->hasMany('App\CustomRole', 'binded_to');
    }

    public function translations() {
        return $this->hasOne('App\BindRolesTranslation', 'bind_roles_id');
    }
}
