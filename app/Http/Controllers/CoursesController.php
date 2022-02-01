<?php

namespace App\Http\Controllers;

use App\BindRoles;
use App\Course;
use App\Role;
use App\CustomRole;
use App\User;
use App\Privilege;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CoursesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $data = ['topics' => Course::topicsByFollowers(5), 'selectedTopics' => [], 'difficulties' => ['easy', 'hard', 'very hard'], 'selectedDifficulties' => []];

        $data['courses'] = Course::withCount('followedBy');

        // Query would be something like
        // "select *
        // from `courses`
        // inner join `users`
        // on `users`.`id` = `courses`.`created_by`
        // where `topic` in (?)
        // and `difficulty` in (?)
        // and `title` LIKE ?
        // or `topic` LIKE ?
        // or `username` LIKE ?"


        if (request()->has('searchTerm')) {
            $data['courses']->join('users', 'users.id', '=', 'courses.created_by');
            //Must use closure to wrap between parentheses
            $data['courses']->where(function($query) {
                $query->where('title', 'LIKE', '%'.request()->searchTerm.'%')
                    ->orWhere('topic', 'LIKE', '%'.request()->searchTerm.'%')
                    ->orWhere('username', 'LIKE', '%'.request()->searchTerm.'%');
            });
            $data['searchTerm'] = request()->searchTerm;
        }

        if (request()->has('topic')) {
            $data['courses']->whereIn('topic', request()->topic);
            $data['selectedTopics'] = request()->topic;
        }

        if (request()->has('difficulty')) {
            $data['courses']->whereIn('difficulty', request()->difficulty);
            $data['selectedDifficulties'] = request()->difficulty;
        }

        // Paginate 3 per page order by followers and also title for same followers
        $data['courses'] = $data['courses']->orderBy('followed_by_count', 'DESC')->orderBy('title')->paginate(6);

        return view('courses.index', $data);
    }

    /**
     * Display a paginated list of all the user followers
     *
     * @return View
     */
    public function indexFollowers(Course $course) {
        return view('courses.followers', ['course' => $course, 'followers' => $course->followedBy()->paginate(1)]);
    }

    /**
     * Returns the needed information to populate the followers list through ajax
     *
     * @return Response
     */
    public function indexFollowersJson(Course $course) {
        return response()->json(['followers' => $course->followedBy()->paginate(1)]);
    }

    /**
     * Returns a json with the users that follows or has a role in the course according to the selected filters
     *
     * @return Response
     */
    public function indexUsers(Course $course) {
        $users = User::query();

        if (request()->has('username') && !empty(request()->username)) {
            // Explode username to get suffixId and username
            $usernameExplode = explode('#', request()->username);
            $username = $usernameExplode[0];

            $users = $users->where('username', 'LIKE', '%'.$username.'%');

            // If count > 1 then the suffixId is present
            if (count($usernameExplode) > 1) {
                $suffixId = $usernameExplode[1];
                $users = $users->where('suffixId', 'LIKE', '%'.$suffixId.'%');
            }
        }

        if (request()->has('role') && !empty(request()->role)) {
            $customRoleId = request()->role;
            $users = $users->whereHas('customRoles', function($q) use ($customRoleId) {
                $q->where('custom_role_id', $customRoleId);
            });
        }

        $users = $users->where(function($query) use ($course) {
            $query->whereHas('customRoles', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->orWhereHas('followCourses', function($query) use ($course) {
                $query->where('course_id', $course->id);
            });
        });

        $users = $users->paginate(2);

        // Save the links to sent them as json and display them in the view using ajax
        $links = $users->links()->render();

        // Load the course in the user to get the created_at and display the date when the user started to follow the course
        $users = $users->load(['followCourses' => function ($query) use ($course) {
            $query->where('course_id', $course->id);
        }]);

        // Load all the custom roles in the user to display them
        $users = $users->load(['customRoles' => function ($query) use ($course) {
            $query->where('course_id', $course->id);
        }]);

        return response()->json([
            'users' => $users,
            'links' => $links
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('courses.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        request()->validate([
            'title' => ['string', 'required', 'max: 64'],
            'description' => ['string', 'required'],
            'topic' => ['string', 'required', 'max:24'],
//            'difficulty' => ['string', 'required', 'max:16'],
        ]);

        $course = new Course();
        $course->createdBy()->associate(auth()->user());
        $course->title = $request->title;
        $course->description = $request->description;
        $course->topic = $request->topic;
        // Not used right now
        $course->difficulty = 'N/A';

        $course->save();

        $customRoles = [];

        $customRole = new CustomRole();
        if (App::isLocale('es')) {
            $customRole->name = 'Administrador';
            $customRole->description = 'Rol creado automáticamente y otorgado al creador del curso';
        } else {
            $customRole->name = 'Administrator';
            $customRole->description = 'Role automatically created and granted to the course creator';
        }
        // Set target level to maximum so admin can't be modified by other roles
        $customRole->target_level = 99;
        $customRole->bindedTo()->associate(BindRoles::where('name', 'specific')->first());
        $customRole->course()->associate($course);
        $customRole->save();
        // Add admin privilege to the admin role
        $customRole->bindedTo()->associate(BindRoles::where('name', 'selected')->first());
        $customRole->privileges()->attach(Privilege::where('name', 'admin')->first());
        // Add the creator to admin
        $customRole->users()->attach($course->createdBy);

        $customRole = new CustomRole();
        if (App::isLocale('es')) {
            $customRole->name = 'Usuario';
            $customRole->description = 'Este es el rol que le será otorgado por defecto a todos los usuarios';
        } else {
            $customRole->name = 'User';
            $customRole->description = 'This is the role that every user will have by default';
        }
        $customRole->target_level = 1;
        $customRole->bindedTo()->associate(BindRoles::where('name', 'users')->first());
        $customRole->course()->associate($course);
        $customRole->save();

        $customRole = new CustomRole();
        if (App::isLocale('es')) {
            $customRole->name = 'Seguidor';
            $customRole->description = 'Rol otorgado a todos los usuarios que sigan el curso, perderán el rol si dejan de seguir el curso';
        } else {
            $customRole->name = 'Follower';
            $customRole->description = 'Role granted to users following the course and removed when they unfollow it';
        }
        $customRole->target_level = 1;
        $customRole->bindedTo()->associate(BindRoles::where('name', 'followers')->first());
        $customRole->course()->associate($course);
        $customRole->save();

        return redirect()->route('courses.show', $course);
    }

    /**
     * Display the specified resource.
     *
     * @param Course $course
     * @return Response
     */
    public function show(Course $course)
    {
        return view('courses.show', ['course' => $course]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Course $course
     * @return Response
     */
    public function edit(Course $course)
    {
        if (auth()->user()->hasPrivilege($course, Privilege::where('name', 'editCourseInfo')->first()) || auth()->user()->hasPrivilege($course, Privilege::where('name', 'editRoles')->first())) {
            return view('courses.edit', ['course' => $course, 'privileges' => Privilege::all()]);
        }
        return redirect()->route('home');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Course $course
     * @return Response
     */
    public function update(Request $request, Course $course)
    {
        if (auth()->user()->hasPrivilege($course, Privilege::where('name', 'editCourseInfo')->first()) || auth()->user()->isSuperAdmin()) {
            $attributes = request()->validate([
                'title' => ['string', 'required', 'max: 64'],
                'image' => ['image'],
                'description' => ['string', 'required'],
                'topic' => ['string', 'required', 'max:24'],
//                'difficulty' => ['string', 'required', 'max:16'],
            ]);

//            $course->title = $request->title;
//            $course->image = $request->file('image')->store('images/courses');
//            $course->description = $request->description;
//            $course->topic = $request->topic;
//            $course->difficulty = $request->difficulty;

            if (isset($attributes['image']) ) {
                if ($course->image != 'images/courses/default.png') {
                    Storage::delete($course->image);
                }
                $attributes['image'] = $request->image->store('images/courses');
            }

            $course->update($attributes);

            return response()->json([
                'success' => true,
                'course' => $course
            ]);
        }
        return response()->json([
            'error' => 'Not enough privileges',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Course $course
     * @return Response
     * @throws \Exception
     */
    public function destroy(Course $course)
    {
        if (auth()->user()->isSuperAdmin() || $course->createdBy->is(auth()->user())) {
            $course->delete();
            return redirect()->route('home')->with(['swal' => ['title' => trans('Deleted'), 'text' => trans('The course was deleted')]]);
        }
    }

    public function showHistoricalFollowers(Course $course) {
        return view('courses.historicalFollowers', ['course' => $course]);
    }

//    private function validateCourse(): array
//    {
//        return request()->validate([
//            'title' => 'required',
//            'description' => 'required',
//            'topic' => 'required',
//            'difficulty' => 'required',
//        ]);
//    }
}
