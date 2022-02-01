<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Course;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
//    public function create() {
//        return view('register');
//    }

    //Implemented in the register controller
//    private function store() {
//        $this->validateUser();
//
//        $user = new User();
//
//        $user->suffixId = $this->generateUserId(request('username'));
//        $user->username = request('username');
//        $user->email = request('email');
//        $user->password = Hash::make(request('password'));
//        $user->role()->associate(Role::where('name', '=', 'user')->first());
//
//        $user->save();
//
//        return view('welcome');
//    }
//
//    private function validateUser(): array
//    {
//        return request()->validate([
//            'username' => 'required',
//            'email' => 'required',
//            'password' => 'required',
//        ]);
//    }

    public function index() {
        if (request()->has('customRole')) {
            $customRoleId = request()->customRole;
            $users = User::whereDoesntHave('customRoles', function($q) use ($customRoleId) {
                $q->where('custom_role_id', $customRoleId);
            });
        } else {
            $users = User::query();
        }
        if (request()->has('username')) {
            $usernameExplode = explode('#', request()->username);
            $username = $usernameExplode[0];

            $users = $users->where('username', 'LIKE', '%'.$username.'%');

            if (count($usernameExplode) > 1) {
                $suffixId = $usernameExplode[1];
                $users = $users->where('suffixId', 'LIKE', '%'.$suffixId.'%');
            }

        } else {
            $users = $users->orderBy('username');
        }
        return response()->json($users->limit(6)->get()->toArray());
    }

    public function show(User $user) {
        return view('users.show', ['user' => $user]);
    }

    public function update(User $user) {
        if (auth()->user()->is($user) || auth()->user()->isSuperAdmin()) {
            $attributes = request()->validate([
                'username' => ['required', 'string', 'max:255'],
                'avatar' => ['nullable', 'image'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ]);

            if (!auth()->user()->isSuperAdmin()) {
                request()->validate([
                    'current_password' => ['required', 'string'],
                ]);
            }

            // Don't throw exception because when the user is superadmin it won't try to check the password because it will be evaluated to true just for being superadmin
            if (auth()->user()->isSuperAdmin() || Hash::check(request()->current_password, $user->password)) {
                if (isset($attributes['password'])) {
                    $attributes['password'] = Hash::make($attributes['password']);
                } else {
                    $attributes['password'] = $user->password;
                }
                if ((User::where('username', '=', $attributes['username'])->where('suffixId', '=', $user->suffixId)->where('id', '!=', $user->id)->first()) !== null) {
                    throw ValidationException::withMessages([
                        'username' => [trans('validation.unique', ['attribute' => 'username'])],
                    ]);
                }

                if (isset($attributes['avatar']) ) {
                    if ($user->avatar != 'images/avatar/default.png') {
                        Storage::delete($user->avatar);
                    }
                    $user->avatar = request()->avatar->store('images/avatar');
                }

                $user->update($attributes);
                return response()->json([
                    'success' => true,
                    'user' => $user
                ]);
            } else {
                throw ValidationException::withMessages([
                    'current_password' => [trans('validation.password')],
                ]);
            }
        } else {
            return response()->json([
                'error' => 'There was an error'
            ], 400);
        }
    }

    public function resetNotifications() {
        auth()->user()->unseenNotifications = 0;
        auth()->user()->update();
        return response()->json(['success' => true]);
    }

    public function followCourse(Course $course) {
        if (!$course->createdBy->is(auth()->user())) {
            auth()->user()->followCourses()->attach($course);
        } else {
            return redirect()->route('courses.show', $course)->with(['swal' => ['title' => trans('Error'), 'text' => trans('You can\'t follow your own course !')]]);
        }
        return redirect()->back();
    }

    public function unFollowCourse(Course $course) {
        auth()->user()->followCourses()->detach($course);
        return redirect()->back();
    }
}
