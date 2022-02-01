<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationTranslation extends Model
{
    public $timestamps = false;

    public function notification() {
        return $this->belongsTo('App\Notification', 'notification_id');
    }
}
