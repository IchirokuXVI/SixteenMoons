<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Course extends Model
{
    protected $fillable = ['title', 'image', 'description', 'topic', 'difficulty'];

    //Get the X topics with most followers
    public static function topicsByFollowers($amount) {
        return Course::withCount('followedBy')->orderBy('followed_by_count', 'DESC')->limit($amount)->pluck('topic')->toArray();

        //Using DB::raw to put a column name and also to prevent eloquent from putting quotes in COUNT(*) because it is not a column
        $topicsCount = DB::table('courses')
            ->join('user_follows_courses', 'courses.id', '=', 'user_follows_courses.course_id')
            ->select(array(DB::raw('courses.topic name'), DB::raw('COUNT(*)')))
            ->groupBy('courses.topic')
            ->orderBy('COUNT(*)', 'DESC')
            ->limit($amount)
            ->get();

        $topics = [];

        foreach($topicsCount as $topic) {
            $topics[] = $topic->name;
        }
        return $topics;
    }

    //Get the 5 topics with most followers from last variable days
    public static function topicsByRecentFollowers($amount, $days) {
        $topics = Course::withCount(['followedBy' => function($query) use ($days) {
            $query->whereDate('user_follows_courses.created_at', '<=', Carbon::now())
                ->whereDate('user_follows_courses.created_at', '>=', Carbon::now()->subDays($days));
        }])->orderBy('followed_by_count', 'DESC')->limit($amount)->pluck('topic')->toArray();
    }

    public static function trendingCourses($amount, $days) {
        $courses = Course::withCount(['followedBy' => function($query) use ($days) {
            $query->whereDate('user_follows_courses.created_at', '<=', Carbon::now())
                ->whereDate('user_follows_courses.created_at', '>=', Carbon::now()->subDays($days));
        }])->orderBy('followed_by_count', 'DESC')->limit($amount)->get();

        return $courses;
    }

    public function createdBy() {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function followedBy() {
        return $this->belongsToMany('App\User', 'user_follows_courses', 'course_id', 'user_id')->withTimestamps();
    }

    public function users() {
        $users = User::whereHas('customRoles', function($query) {
            $query->where('course_id', $this->id);
        });
        $users = $users->orWhereHas('followCourses', function($query) {
            $query->where('course_id', $this->id);
        });

        return $users;
    }

    public function hasSupporters() {
        return $this->customRoles()->whereHas('users', function($query) {
            $query->where('supporter', true);
        })->exists();
    }

    public function notifications() {
        return $this->hasMany('App\Notification', 'course_id');
    }

    public function unloggedHasPrivilege(Privilege $privilege) {
        $customRoles = $this->customRoles()->where('binded_to', BindRoles::where('name', 'users')->pluck('id')->first())->get();
        if (!$customRoles->isEmpty()) {
            foreach ($customRoles as $customRole) {
                if ($customRole->privileges->contains('id', $privilege->id) || $customRole->privileges->contains('id', Privilege::ADMIN)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function recentFollowers() {
        return $this->followedBy()->whereDate('user_follows_courses.created_at', Carbon::today());
    }

    public function followersByDate($start, $end) {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        return $this->followedBy()->whereDate('user_follows_courses.created_at', '<=', $end)
            ->whereDate('user_follows_courses.created_at', '>=', $start);
    }

    public function lessons() {
        return $this->hasMany('App\Lesson', 'course_id');
    }

    public function customRoles() {
        return $this->hasMany('App\CustomRole', 'course_id');
    }
}
