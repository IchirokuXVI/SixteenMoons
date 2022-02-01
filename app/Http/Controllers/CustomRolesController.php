<?php

namespace App\Http\Controllers;

use App\BindRoles;
use App\Course;
use App\CustomRole;
use App\Notification;
use App\NotificationTranslation;
use App\NotificationType;
use App\Privilege;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomRolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Course $course
     * @return Response
     */
    public function store(Course $course)
    {
        $user = auth()->user();
        if ($user->hasPrivilege($course, Privilege::where('name', 'editRoles')->first())) {
            $customRole = new CustomRole();
            if (App::isLocale('en')) {
                $default = "New Role";
            } else {
                $default = "Nuevo rol";
            }
            $name = $default;
            $counter = 0;
            while (CustomRole::where('name', $name)->exists()) {
                $counter++;
                $name = $default.' '.$counter;
            }
            $customRole->name = $name;
            $customRole->description = $name;
            $customRole->target_level = 1;
            $customRole->bindedTo()->associate(BindRoles::where('name', 'specific')->first());
            $customRole->course()->associate($course);

            $customRole->save();

            return response()->json($customRole);
        } else {
            return response()->json([
                'error' => 'Not enough privileges'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param CustomRole $customRole
     * @return Response
     */
    public function show(CustomRole $customRole)
    {
        //
    }

    public function data(Course $course, CustomRole $customRole) {
        if ($customRole->course->is($course)) {
            $user = auth()->user();
            if ($user->hasPrivilege($course, Privilege::where('name', 'editRoles')->first()) || $user->isSuperAdmin()) {
                // Saves the privileges inside the customRole object
                $customRole->load('privileges');
                $customRole->load('bindedTo');
                return response()->json($customRole);
            } else {
                return response()->json([
                    'error' => 'Not enough privileges'
                ]);
            }
        } else {
            return response()->json([
                'error' => 'Invalid course'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CustomRole $customRole
     * @return Response
     */
    public function edit(CustomRole $customRole)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Course $course
     * @param CustomRole $customRole
     * @return void
     * @throws ValidationException
     */
    public function update(Request $request, Course $course, CustomRole $customRole)
    {
        if ($customRole->course->is($course)) {
            $user = auth()->user();
            if ($course->createdBy->is($user) || $user->isSuperAdmin() || ($user->hasPrivilege($course, Privilege::where('name', 'editRoles')->first()) && $user->maxTargetLevel($course) > $customRole->target_level)) {
                request()->validate([
                    'name' => ['required', 'string', 'max: 40'],
                    'description' => ['sometimes', 'string', 'max: 255'],
                    'bindedTo' => ['required', 'integer', 'between: 1,'.BindRoles::all()->count()],
                    'price' => ['sometimes', 'numeric', 'gt: 0'],
                    'targetLevel' => ['required', 'integer', 'between: 1,99'],
                    'privilege' => ['sometimes', 'size: 9']
                ]);

//                $validator = Validator::make(request()->all(), [
//                    'name' => ['required', 'string', 'max: 40'],
//                    'description' => ['sometimes', 'string', 'max: 255'],
//                    'bindedTo' => ['required', 'integer', 'between: 1,'.BindRoles::all()->count()],
//                    'price' => ['sometimes', 'numeric', 'gt: 0'],
//                    'targetLevel' => ['required', 'integer', 'between: 1,99'],
//                    'privilege' => ['sometimes', 'size: 9']
//                ]);

//                if ($validator->fails()) {
//                    return response()->json(array(
//                        'success' => false,
//                        'errors' => $validator->getMessageBag()->toArray()
//                    ), 400); // 400 being the HTTP code for an invalid request.
//                }

                if ($course->customRoles()->where('name', request()->name)->where('id', '!=', $customRole->id)->exists()) {
                    throw ValidationException::withMessages([
                        'name' => [trans('validation.unique', ['attribute' => 'name'])],
                    ]);
                }

                if (!$course->createdBy($user) && !$user->isSuperAdmin() && !($user->maxTargetLevel($course) > request()->targetLevel)) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['targetLevel' => 'Your target level is lower or equal than the one you have set']
                    ], 400);
                }

                if (request()->has('privileges')) {
                    $attachedPrivileges = $customRole->privileges()->whereIn('privilege_id', request()->privileges)->pluck('id')->toArray();
                    $newPrivileges = array_diff(request()->privileges, $attachedPrivileges);
                    $adminPrivilege = Privilege::where('name', 'admin')->first();
                    // If the admin privilege was selected or all roles but the admin are selected then we must ensure that the user editing this privilege is also an admin or the creator
                    if (in_array($adminPrivilege->id, $newPrivileges) || count(request()->privileges) === Privilege::all()->count() -1) {
                        if ($user->isCourseAdmin($course) || $user->isSuperAdmin()) {
                            if (!$customRole->privileges->contains('id', $adminPrivilege->id)) {
                                // Detach all roles because we don't need them if the user is admin
                                $customRole->privileges()->detach();
                                $customRole->privileges()->attach($adminPrivilege);
                            }
                        } else {
                            return response()->json([
                                'error' => 'Not enough privileges to grant admin privilege'
                            ], 400);
                        }
                    } else if ($customRole->privileges->contains('id', $adminPrivilege->id)) { // Admin was removed in the form
                        if ($user->isCourseAdmin($course) || $user->isSuperAdmin()) {
                            $customRole->privileges()->detach($adminPrivilege);
                        } else {
                            return response()->json([
                                'error' => 'Not enough privileges to remove admin privilege'
                            ], 400);
                        }
                    }

                    // Refresh the model with actual data so $customRole->privileges are updated
                    $customRole->refresh();

                    if (!$customRole->privileges->contains('id', $adminPrivilege->id)) { // The admin privilege wasn't modified
                        // Must check the in array with request()->privileges because the privilege could be granted before
                        // If we do that check with the $newPrivileges array then it will evaluate to true because the newprivileges doesn't have old privileges
                        // Steps to reproduce: save a role able to see lessons but not edit, then grant edit lessons to the role
                        // If it is done with the newprivileges then the condition will evaluate to true
                        if (in_array(Privilege::where('name', 'editLessons')->pluck('id')->first(), request()->privileges)
                            && !in_array(Privilege::where('name', 'seeLessons')->pluck('id')->first(), request()->privileges))
                        {
                            return response()->json([
                                'error' => 'A role able to edit lessons must be able to see them'
                            ], 400);
                        }
                        if (in_array(Privilege::where('name', 'editPrices')->pluck('id')->first(), request()->privileges)
                            && !in_array(Privilege::where('name', 'editRoles')->pluck('id')->first(), request()->privileges))
                        {
                            return response()->json([
                                'error' => 'A role able to edit prices must be able to edit roles'
                            ], 400);
                        }

                        // Loop all the privileges and check if they weren't already attached
                        foreach($newPrivileges as $privilege) {
                            // In order to attach the roles we must ensure that the user has this privilege too, hasPrivilege will return always true if the user is admin
                            if ($user->hasPrivilege($course, Privilege::find($privilege)) || $user->isSuperAdmin()) {
                                $customRole->privileges()->attach($privilege);
                            }
                        }

                        // Detach all privileges that wasn't selected
                        foreach($customRole->privileges as $privilege) {
                            // In order to detach the roles we must ensure that the user has this privilege too, hasPrivilege will return always true if the user is admin
                            if ($user->hasPrivilege($course, $privilege) || $user->isSuperAdmin()) {
                                // If the role has a privilege that wasn't sent then detach it
                                if (!in_array($privilege->id, request()->privileges)) {
                                    $customRole->privileges()->detach($privilege);
                                }
                            }
                        }
                    }
                } else { // There wasn't any privilege selected so detach all of them
                    // If the user is an admin we can detach all privileges because none of them were selected
                    if ($user->isCourseAdmin($course) || $user->isSuperAdmin()) {
                        $customRole->privileges()->detach();
                    } else { // The user isn't an admin so we must ensure that he isn't removing a privilege that isn't granted to him
                        foreach($customRole->privileges as $privilege) {
                            // If the user is a course admin then has privilege always returns true
                            if ($user->hasPrivilege($course, Privilege::find($privilege))) {
                                $customRole->privileges()->detach($privilege);
                            }
                        }
                    }
                }
                $supporterBind = BindRoles::where('name', 'supporters')->first();
                $currentBind = $customRole->bindedTo;
                $hasEditPrices = $user->hasPrivilege($course, Privilege::where('name', 'editPrices')->first()) || $user->isSuperAdmin();
                if ($hasEditPrices || (!$currentBind->is($supporterBind) && request()->bindedTo != $supporterBind->id)) {
                    $customRole->bindedTo()->associate(BindRoles::find(request()->bindedTo));
                    if ($request->has('price') && $hasEditPrices) {
                        $customRole->price = request()->price;
                    } else if (!$request->has('price')) {
                        $customRole->price = 0;
                    }
                }
                $customRole->target_level = request()->targetLevel;
                $customRole->name = request()->name;
                $customRole->description = request()->description;
                $customRole->update();
                $customRole->refresh();
                // If the custom role doesn't have any privilege then it returns an empty array
                // It is because we used the load method, otherwise it wouldn't return anything. The privileges property wouldn't be in the object and the javascript would throw an error
                $customRole->load('privileges');
                return response()->json([
                    'success' => 'Role updated',
                    'customRole' => $customRole
                ]);
            } else {
                return response()->json([
                    'error' => 'Not enough privileges'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => 'Invalid course'
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CustomRole $customRole
     * @return Response
     * @throws \Exception
     */
    public function destroy(Course $course, CustomRole $customRole)
    {
        if ($customRole->course->is($course)) {
            $user = auth()->user();
            if ($course->createdBy->is($user) || $user->isSuperAdmin() || ($user->hasPrivilege($course, Privilege::where('name', 'editRoles')->first()) && $user->maxTargetLevel($course) > $customRole->target_level)) {
                foreach($customRole->users as $user) {
                    // Create a notification to notify the user about his role being revoked
                    $notification = new Notification();
                    // Uri of the notification, it will be put in the href attribute of the notification when displaying it
                    $notification->url = route('courses.show', $course, false);
                    $notification->type()->associate(NotificationType::where('name', 'removedRole')->first());
                    $notification->user()->associate($user);
                    $notification->course()->associate($course);
                    // Save notification to get id and then associate after with translation
                    $notification->save();
                    // Increment unseen notifications
                    $user->unseenNotifications = $user->unseenNotifications+1;
                    $user->update();
                    $notificationTrans = new NotificationTranslation();
                    // Two different messages so when the user changes the language the message will change
                    $notificationTrans->message_en = "You have been revoked the role '$customRole->name' from the course '$course->title'";
                    $notificationTrans->message_es = "Se te ha quitado el rol '$customRole->name' del curso '$course->title'";
                    $notificationTrans->notification()->associate($notification);
                    $notificationTrans->save();
                }
                $customRole->delete();
                return response()->json([
                    'success' => 'true'
                ]);
            } else {
                return response()->json([
                    'error' => 'Not enough privileges'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => 'Invalid course'
            ], 400);
        }
    }

    public function addUser(Course $course, CustomRole $customRole) {
        if ($customRole->course->is($course)) {
            $user = auth()->user();
            if ($course->createdBy->is($user) || $user->isSuperAdmin() || ($user->hasPrivilege($course, Privilege::where('name', 'editRoles')->first()) && $user->maxTargetLevel($course) > $customRole->target_level)) {
                $usernameExplode = explode('#', request()->username);
                $username = $usernameExplode[0];
                if (!isset($usernameExplode[1])) {
                    return response()->json([
                        'success' => 'false',
                        'error' => trans('validation.missingSuffix')
                    ], 400);
                }
                $suffix = $usernameExplode[1];

                $user = User::where('username', '=', $username)->where('suffixId', '=', $suffix)->first();

                if (!empty($user)) {
                    if (!$user->customRoles->contains($customRole)) {
                        $customRole->users()->attach($user);
                        $notification = new Notification();
                        $notification->url = route('courses.show', $course, false);
                        $notification->type()->associate(NotificationType::where('name', 'newRole')->first());
                        $notification->user()->associate($user);
                        $notification->course()->associate($course);
                        $notification->save();
                        $user->unseenNotifications = $user->unseenNotifications+1;
                        $user->update();

                        $notificationTrans = new NotificationTranslation();
                        $notificationTrans->message_en = "You have been granted the role '$customRole->name' from the course '$course->title'";
                        $notificationTrans->message_es = "Se te ha otorgado el rol '$customRole->name' en el curso '$course->title'";
                        $notificationTrans->notification()->associate($notification);
                        $notificationTrans->save();
                        return response()->json([
                            'success' => true,
                            'user' => $user
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'error' => trans("validation.userAlreadyAttached", ['username' => $username.'#'.$suffix, 'roleName' => $customRole->name])
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => trans("validation.userNotFound", ['username' => $username.'#'.$suffix])
                    ], 400);
                }
            } else {
                return response()->json([
                    'error' => 'Not enough privileges'
                ], 400);
            }
        } else {
            // The given role is not from the specified course
            return response()->json([
                'error' => 'Invalid course'
            ], 400);
        }
    }

    public function removeUser(Course $course, CustomRole $customRole, User $user) {
        if ($customRole->course->is($course)) {
            $loggedUser = auth()->user();
            if ($course->createdBy->is($loggedUser) || $loggedUser->isSuperAdmin() || ($loggedUser->hasPrivilege($course, Privilege::where('name', 'editRoles')->first()) && $loggedUser->maxTargetLevel($course) > $customRole->target_level)) {
                if ($user->customRoles()->find($customRole->id)->pivot->supporter == 0 || auth()->user()->isSuperAdmin()) {
                    $user->customRoles()->detach($customRole);

                    // Create a notification to notify the user about his role being revoked
                    $notification = new Notification();
                    // Uri of the notification, it will be put in the href attribute of the notification when displaying it
                    $notification->url = route('courses.show', $course, false);
                    $notification->type()->associate(NotificationType::where('name', 'removedRole')->first());
                    $notification->user()->associate($user);
                    $notification->course()->associate($course);
                    // Save notification to get id and then associate after with translation
                    $notification->save();
                    // Increment unseen notifications
                    $user->unseenNotifications = $user->unseenNotifications+1;
                    $user->update();
                    $notificationTrans = new NotificationTranslation();
                    // Two different messages so when the user changes the language the message will change
                    $notificationTrans->message_en = "You have been revoked the role '$customRole->name' from the course '$course->title'";
                    $notificationTrans->message_es = "Se te ha quitado el rol '$customRole->name' del curso '$course->title'";
                    $notificationTrans->notification()->associate($notification);
                    $notificationTrans->save();

                    return response()->json([
                        'success' => true,
                        'customRole' => $customRole,
                        'user' => $user
                    ]);
                } else {
                    // The user tried to remove a role from a user that paid for it
                    return response()->json([
                        'error' => trans("You can't remove a supporter role, contact with an administrator for more information")
                    ], 400);
                }
            } else {
                return response()->json([
                    'error' => 'Not enough privileges'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => 'Invalid course'
            ], 400);
        }
    }
}
