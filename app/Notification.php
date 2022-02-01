<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function course() {
        return $this->belongsTo('App\Course', 'course_id');
    }

    public function type() {
        return $this->belongsTo('App\NotificationType', 'type_id');
    }

    public function translations() {
        return $this->hasOne('App\NotificationTranslation', 'notification_id');
    }
}
