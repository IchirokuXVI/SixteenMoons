<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'suffixId', 'username', 'email', 'password', 'name', 'age', 'role_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // A user has only one role but multiple users can have the same role. One to many relationship, method hasMany('App\User') in Role
    public function role() {
        return $this->belongsTo('App\Role', 'role_id');
    }

    public function customRoles() {
        return $this->belongsToMany('App\CustomRole','custom_roles_users', 'user_id', 'custom_role_id')->withPivot('supporter');
    }

    public function notifications() {
        return $this->hasMany('App\Notification', 'user_id');
    }

    public function notContentNotifications() {
        return $this->notifications()
            ->where('type_id', '!=', NotificationType::where('name' ,'newContent')->first()->id)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function coursesOrderedByNewContent() {
        $courses = $this->followCourses();

        $courses = $courses->withCount(['notifications' => function($query) {
            $query->where('type_id', NotificationType::where('name', 'newContent')->first()->id)
                ->where('user_id', $this->id);
        }])->orderBy('notifications_count', 'DESC')->get();

        return $courses;
    }

    public function newContent($course) {
        return $this->notifications()
            ->where('type_id', NotificationType::where('name', 'newContent')->first()->id)
            ->where('course_id', $course->id)
            ->get();
    }

    //Start friends reflexive relationship
    public function friendsSent() {
        return $this->belongsToMany('App\User', 'friends', 'idSender', 'idReceiver')
            ->withPivot('accepted')->where('accepted', 1)->withTimestamps();
    }

    public function friendsReceived() {
        return $this->belongsToMany('App\User', 'friends', 'idReceiver', 'idSender')
            ->withPivot('accepted')->where('accepted', 1)->withTimestamps();
    }

    //Merge both collections to retrieve all the friends
    public function friends() {
        return $this->friendsSent->merge($this->friendsReceived);
    }

    public function sentFriendRequests() {
        return $this->belongsToMany('App\User', 'friends', 'idSender', 'idReceiver')
            ->withPivot('accepted')->where('accepted', 0)->withTimestamps();
    }

    public function receivedFriendRequests() {
        return $this->belongsToMany('App\User', 'friends', 'idReceiver', 'idSender')
            ->withPivot('accepted')->where('accepted', 0)->withTimestamps();
    }
    //Ends friends reflexive relationship


    //Start messages reflexive relationship
    public function messagesSent() {
        return $this->belongsToMany('App\User', 'user_messages', 'idSender', 'idReceiver')->withPivot('body')->withTimestamps();
    }

    public function messagesReceived() {
        return $this->belongsToMany('App\User', 'user_messages', 'idReceiver', 'idSender')->withPivot('body')->withTimestamps();
    }

    public function getMessages() {
        return $this->messagesSent->merge($this->messagesReceived);
    }
    //Ends messages reflexive relationship

    public function comments() {
        return $this->hasMany('App\Comment', 'user_id');
    }

    // One to many creator of the course
    public function createdCourses() {
        return $this->hasMany('App\Course', 'created_by');
    }

    // Many to many user follow courses
    public function followCourses() {
        return $this->belongsToMany('App\Course', 'user_follows_courses', 'user_id', 'course_id')->withTimestamps();;
    }

    public function isSuperAdmin() {
        return $this->role->is(Role::where('name', '=', 'admin')->first());
    }

    public function isCourseAdmin(Course $course) {
        // The creator of the course will be always admin even if he doesn't have a role
        if ($course->createdBy->is($this)) return true;

        $customRoles = $this->customRoles()->where('course_id', $course->id)->get();
        foreach ($customRoles as $customRole) {
            if ($customRole->privileges->contains('name', 'admin')) {
                return true;
            }
        }
        return false;
    }

    public function hasPrivilege(Course $course, Privilege $privilege) {
        // Return true if the user is super admin or if the user is a course admin
        if ($this->isSuperAdmin()) return true;
        if ($this->isCourseAdmin($course)) return true;
        $customRoles = $this->customRoles()->where('course_id', $course->id)->get();
        if ($customRoles->isEmpty()) {
            if ($course->followedBy->contains('id', $this->id)) {
                $customRoles = $course->customRoles()->where('binded_to', BindRoles::where('name', 'followers')->pluck('id')->first())->get();
            } else {
                $customRoles = $course->customRoles()->where('binded_to', BindRoles::where('name', 'users')->pluck('id')->first())->get();
            }
        }

        if (!$customRoles->isEmpty()) {
            foreach ($customRoles as $customRole) {
                if ($customRole->privileges->contains('id', $privilege->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getAllPrivileges(Course $course) {
        $customRoles = $this->customRoles()->where('course_id', $course->id)->pluck('id')->toArray();

        $privilegeIds = DB::table('custom_role_privileges')->whereIn('custom_role_id', $customRoles)->distinct()->pluck('privilege_id')->toArray();

        return Privilege::whereIn('id', $privilegeIds)->get();
    }

    public function maxTargetLevel(Course $course) {
        $customRole = $this->customRoles()->where('course_id', $course->id)->orderBy('target_level', 'DESC')->first();

        return isset($customRole) ? $customRole->target_level : 1;
    }
}
