<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Course;
use App\Lesson;
use App\Privilege;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Course $course
     * @param Lesson $lesson
     * @return Response
     */
    public function index(Course $course, Lesson $lesson)
    {
        return response()->json([
            'comments' => $lesson->comments()->with('user')->orderBy('created_at', 'DESC')->paginate(10)
        ]);
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
     * @param Lesson $lesson
     * @param Request $request
     * @return void
     */
    public function store(Course $course, Lesson $lesson, Request $request)
    {
        if ($lesson->course->is($course)) {
            $user = auth()->user();
            if ($user->hasPrivilege($lesson->course, Privilege::find(Privilege::COMMENT_LESSONS))) {
                request()->validate([
                    'body' => ['required']
                ]);

                $comment = new Comment();

                $comment->body = request()->body;
                $comment->user()->associate($user);
                $comment->lesson()->associate($lesson);

                $comment->save();
                return response()->json([
                    'success' => true,
                    'comment' => $comment
                ]);
            } else {
                return response()->json([
                    'error' => 'Not enough privileges'
                ]);
            }
        } else {
            return response()->json([
                'error' => 'Invalid course or lesson'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Comment  $comment
     * @return Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Comment  $comment
     * @return Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  \App\Comment  $comment
     * @return Response
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Comment  $comment
     * @return Response
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
