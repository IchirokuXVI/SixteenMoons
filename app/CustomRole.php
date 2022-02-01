<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomRole extends Model
{
    protected $fillable = ['name', 'description'];

    public function course() {
        return $this->belongsTo('App\Course', 'course_id');
    }

    public function users() {
        return $this->belongsToMany('App\User', 'custom_roles_users', 'custom_role_id', 'user_id')->withPivot('supporter');
    }

    public function privileges() {
        return $this->belongsToMany('App\Privilege', 'custom_role_privileges', 'custom_role_id', 'privilege_id');
    }

    public function bindedTo() {
        return $this->belongsTo('App\BindRoles', 'binded_to');
    }

}
