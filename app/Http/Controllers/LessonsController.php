<?php

namespace App\Http\Controllers;

use App\BindRoles;
use App\CustomRole;
use App\File;
use App\Notification;
use App\NotificationTranslation;
use App\NotificationType;
use App\Privilege;
use App\Role;
use App\CustomRoleRole;
use App\User;
use App\Course;
use App\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class LessonsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Course $course
     * @return Response
     */
    public function index(Course $course)
    {
        return view('lessons.index', ['course' => $course, 'lessons' => $course->lessons()->paginate(3)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Course $course)
    {
        return view('lessons.create', ['course' => $course]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request, Course $course)
    {
        if (auth()->user()->hasPrivilege($course, Privilege::find(Privilege::CREATE_LESSONS))) {
            request()->validate([
                'title' => ['string', 'required', 'max:64'],
                'body' => ['required']
            ]);


            $lesson = new Lesson();

            $lesson->title = request()->title;
            $lesson->body = request()->body;
            $lesson->course()->associate($course);

            $lesson->save();

            // If the followers have access to lessons then notify all the followers otherwise notify only followers with the required privileges
            if (CustomRole::whereIn('binded_to', [BindRoles::where('name', 'followers')->first()->id, BindRoles::where('name', 'users')->first()->id])
                ->where('course_id', $course->id)
                ->whereHas('privileges', function($query) {
                    $query->where('name', 'seeLessons');
                })->exists()) {
                $users = $course->followedBy;
            } else {
                $users = $course->followedBy()->whereHas('customRoles', function($query) use($course) {
                    $query->where('course_id', $course->id)
                        ->whereHas('privileges', function($subquery) {
                            $subquery->where('id', Privilege::SEE_LESSONS);
                        });
                })->get();
            }

            foreach($users as $user) {
                $notification = new Notification();
                $notification->url = route('lessons.show', ['course' => $course, 'lesson' => $lesson], false);
                $notification->user()->associate($user);
                $notification->course()->associate($course);
                $notification->type()->associate(NotificationType::where('name', 'newContent')->first());
                $notification->save();
                $notificationTrans = new NotificationTranslation();
                $notificationTrans->message_es = $lesson->title;
                $notificationTrans->message_en = $lesson->title;
                $notificationTrans->notification()->associate($notification);
                $notificationTrans->save();
            }

            if (isset(request()->filesId)) {
                foreach(request()->filesId as $file) {
                    $dbFile = File::find($file);

                    //Only associate file with lesson if the file don't have a lesson associated and the lesson is from the same course that is on the request
                    if (!isset($dbFile->lesson) && $lesson->course->is($course)) $dbFile->lesson()->associate($lesson);

                    $newPath = $course->id.'/'.$lesson->id.'/'.basename($dbFile->path);
                    Storage::disk('courses')->move($dbFile->path, $newPath);
                    $dbFile->path = $course->id.'/'.$lesson->id.'/'.basename($dbFile->path);
                    $dbFile->update();
                }
            }

            return redirect()->route('lessons.show', ['course' => $course, 'lesson' => $lesson]);
        }
        return redirect()->route('home')->withErrors(['swal' => ['title' => trans('Not enough privileges'), 'text' => trans('You can\'t create lessons on this course')]]);
    }

    /**
     * Display the specified resource.
     *
     * @param Course $course
     * @param  Lesson $lesson
     * @return Response
     */
    public function show(Course $course, Lesson $lesson)
    {
        if ((auth()->check() && auth()->user()->hasPrivilege($course, Privilege::find(Privilege::SEE_LESSONS))) || $course->unloggedHasPrivilege(Privilege::find(Privilege::SEE_LESSONS))) {
            if (auth()->check()) auth()->user()->notifications()->where('url', '/'.request()->path())->delete();
            return view('lessons.show', ['course' => $course, 'lesson' => $lesson]);
        } else {
            return redirect()->back()->with(['swal' => ['title' => trans('Not enough privileges'), 'text' => trans('You can\'t see lessons in this course with your current role')]]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Course $course
     * @param Lesson $lesson
     * @return Response
     */
    public function edit(Course $course, Lesson $lesson)
    {
        return view('lessons.edit', ['course' => $course, 'lesson' => $lesson]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Course $course
     * @param Lesson $lesson
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Course $course, Lesson $lesson)
    {
        if (auth()->user()->hasPrivilege($course, Privilege::find(Privilege::EDIT_LESSONS))) {
            $attributes = request()->validate([
                'title' => ['string', 'required', 'max:64'],
                'body' => ['required']
            ]);


            $lesson->update($attributes);


            return redirect()->route('lessons.show', ['course' => $course, 'lesson' => $lesson]);
        }
        return redirect()->route('home');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Course $course
     * @param Lesson $lesson
     * @return void
     * @throws \Exception
     */
    public function destroy(Course $course, Lesson $lesson)
    {
        if (auth()->user()->hasPrivilege($course, Privilege::find(Privilege::EDIT_LESSONS))) {
            $lesson->delete();
            return redirect()->back();
        }
    }
}
