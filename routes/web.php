<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Change locale
Route::get('language/{locale}', function ($locale) {
    if (!in_array($locale, ['en', 'es'])) {
        abort(400);
    }

    return redirect()->back()->withCookie(cookie()->forever('language', $locale, null, null, false, false));
})->name('changeLocale');

//Home
Route::get('/', 'HomeController@index')->name('home');

//User controller
Route::get('/users', 'UsersController@index')->name('users.index');
Route::put('/users/resetNotifications', 'UsersController@resetNotifications')->name('users.resetNotifications')->middleware('auth');
Route::get('/users/{user}', 'UsersController@show')->name('users.show')->middleware('auth');
Route::put('/users/{user}', 'UsersController@update')->name('users.update')->middleware('auth');
//Attach users to courses
Route::get('/users/followCourse/{course}', 'UsersController@followCourse')->name('users.followCourse')->middleware('auth');
Route::get('/users/unFollowCourse/{course}', 'UsersController@unFollowCourse')->name('users.unFollowCourse')->middleware('auth');

//Notifications controller
Route::get('/users/{user}/notifications', 'NotificationsController@index')->name('notifications.index')->middleware('auth');
Route::put('/users/{user}/notifications/{notification}', 'NotificationsController@update')->name('notifications.update')->middleware('auth');
Route::delete('/users/notifications/{notification}', 'NotificationsController@destroy')->name('notifications.destroy')->middleware('auth');

//Course controller
Route::get('/courses', 'CoursesController@index')->name('courses.index');
Route::get('/courses/create', 'CoursesController@create')->name('courses.create')->middleware('auth');
Route::post('/courses', 'CoursesController@store')->name('courses.store')->middleware('auth');
Route::get('/courses/{course}', 'CoursesController@show')->name('courses.show');
Route::delete('/courses/{course}', 'CoursesController@destroy')->name('courses.destroy')->middleware('auth');
Route::get('/courses/{course}/historicalFollowers', 'CoursesController@showHistoricalFollowers')->name('courses.historicalFollowers')->middleware('auth');
Route::get('/courses/{course}/edit', 'CoursesController@edit')->name('courses.edit')->middleware('auth');
Route::get('/courses/{course}/users', 'CoursesController@indexUsers')->name('courses.indexUsers')->middleware('auth');
Route::get('/courses/{course}/followers', 'CoursesController@indexFollowers')->name('courses.indexFollowers');
Route::get('/courses/{course}/followersJson', 'CoursesController@indexFollowersJson')->name('courses.indexFollowersJson');
Route::put('/courses/{course}', 'CoursesController@update')->name('courses.update')->middleware('auth');

//CustomRoles controller
Route::post('/courses/{course}/roles/{customRole}/addUser', 'CustomRolesController@addUser')->name('customRoles.addUser')->middleware('auth');
Route::get('/courses/{course}/roles/{customRole}', 'CustomRolesController@data')->name('customRoles.data')->middleware('auth');
Route::post('/courses/{course}/roles', 'CustomRolesController@store')->name('customRoles.store')->middleware('auth');
Route::put('/courses/{course}/roles/{customRole}', 'CustomRolesController@update')->name('customRoles.update')->middleware('auth');
Route::delete('/courses/{course}/roles/{customRole}/users/{user}', 'CustomRolesController@removeUser')->name('customRoles.removeUser')->middleware('auth');
Route::delete('/courses/{course}/roles/{customRole}', 'CustomRolesController@destroy')->name('customRoles.destroy')->middleware('auth');

//Lessons controller
Route::post('/courses/{course}/lessons', 'LessonsController@store')->name('lessons.store')->middleware('auth');
Route::get('/courses/{course}/lessons', 'LessonsController@index')->name('lessons.index');
Route::get('/courses/{course}/lessons/create', 'LessonsController@create')->name('lessons.create')->middleware('auth');
Route::get('/courses/{course}/lessons/{lesson}/edit', 'LessonsController@edit')->name('lessons.edit')->middleware('auth');
Route::put('/courses/{course}/lessons/{lesson}', 'LessonsController@update')->name('lessons.update')->middleware('auth');
Route::get('/courses/{course}/lessons/{lesson}', 'LessonsController@show')->name('lessons.show');
Route::delete('/courses/{course}/lessons/{lesson}', 'LessonsController@destroy')->name('lessons.destroy')->middleware('auth');

//Files controller
Route::post('/courses/{course}/lessons/files', 'FilesController@store')->name('files.storeNewLesson')->middleware('auth');
Route::post('/courses/{course}/lessons/{lesson}/files', 'FilesController@store')->name('files.store')->middleware('auth');
Route::get('/courses/{course}/lessons/{lesson}/files/{file}', 'FilesController@download')->name('files.download');
Route::delete('/courses/{course}/files/{file}', 'FilesController@destroy')->name('files.destroy')->middleware('auth');

//Comments controller
Route::get('/courses/{course}/lessons/{lesson}/comments', 'CommentsController@index')->name('comments.index');
Route::post('/courses/{course}/lessons/{lesson}/comments', 'CommentsController@store')->name('comments.store')->middleware('auth');
Route::delete('/courses/{course}/lessons/{lesson}/comments/{comment}', 'CommentsController@destroy')->name('comments.destroy')->middleware('auth');

//PaymentController controller
Route::get('/courses/{course}/support', 'PaymentController@create')->name('payment.create')->middleware('auth');
Route::post('/courses/{course}/support/{customRole}', 'PaymentController@store')->name('payment.store')->middleware('auth');
Route::get('/courses/{course}/support/confirm', 'PaymentController@confirm')->name('payment.confirm')->middleware('auth');
Route::get('/courses/{course}/support/success', 'PaymentController@success')->name('payment.success')->middleware('auth');

//Render angular index.html, it won't be rendered if we get into a laravel route
//Route::get('{any}', function () {
//    return view('welcome');
//})->where('any', '(.*)');

Auth::routes(['verify' => true]);

Route::get('/artisan/migrateFresh', function() {
    Artisan::call('migrate:fresh');
});
