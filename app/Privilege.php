<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Privilege extends Model
{
    public const ADMIN = 1;
    public const EDIT_COURSE_INFO = 2;
    public const EDIT_ROLES = 3;
    public const CREATE_LESSONS = 4;
    public const EDIT_LESSONS = 5;
    public const EDIT_PRICES = 6;
    public const SEE_LESSONS = 7;
    public const DOWNLOAD_FILES = 8;
    public const COMMENT_LESSONS = 9;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    public function customRoles() {
        return $this->belongsToMany('App\CustomRole', 'custom_role_privileges', 'privilege_id', 'custom_role_id');
    }

    public function translations() {
        return $this->hasOne('App\PrivilegesTranslation', 'privilege_id');
    }
}
