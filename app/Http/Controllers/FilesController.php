<?php

namespace App\Http\Controllers;

use App\Course;
use App\File;
use App\Lesson;
use App\Privilege;
use App\SupportedFormat;
use App\User;
use App\Role;
use App\CustomRole;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
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
     * @param \Illuminate\Http\Request $request
     * @param Course $course
     * @param Lesson $lesson
     * @return void
     */
    public function store(Request $request, Course $course, Lesson $lesson = null)
    {
        if ((auth()->check() && (auth()->user()->hasPrivilege($course, Privilege::find(Privilege::CREATE_LESSONS)) || auth()->user()->hasPrivilege($course, Privilege::find(Privilege::EDIT_LESSONS)))) || $course->unloggedHasPrivilege(Privilege::find(Privilege::CREATE_LESSONS)) || $course->unloggedHasPrivilege(Privilege::find(Privilege::EDIT_LESSONS))) {
            $files = [];
            foreach ($request->file('files') as $requestFile) {
                if (!in_array($requestFile->getMimeType(), SupportedFormat::pluck('mime')->toArray())) {
                    return response()->json([
                        'error' => 'File format not allowed, try uploading a zip compressed file'
                    ], 400);
                }
                $file = new File();
                $file->format()->associate(SupportedFormat::where('mime', $requestFile->getMimeType())->first());
                $file->lesson()->associate($lesson);
                $file->original_name = $requestFile->getClientoriginalName();
                $file->path = $requestFile->store($course->id . (isset($lesson) ? '/' . $lesson->id : ''), 'courses');
                $file->save();

                $files[] = $file->id;
            }

            return json_encode($files);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Course $course
     * @param Lesson $lesson
     * @param File $file
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Course $course, Lesson $lesson, File $file)
    {
        if ((auth()->check() && auth()->user()->hasPrivilege($course, Privilege::find(Privilege::DOWNLOAD_FILES))) || $course->unloggedHasPrivilege(Privilege::find(Privilege::DOWNLOAD_FILES))) {
            if ($file->format->name == 'pdf') {
                return response()->file(Storage::disk('courses')->path($file->path));
            }
            return Storage::disk('courses')->download($file->path, $file->original_name);
        }
        return back()->with(['swal' => ['title' => 'Not enough privileges', 'text' => "You can't download files from this course"]]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param File $file
     * @return Response
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param File $file
     * @return Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Course $course
     * @param File $file
     * @return Response
     * @throws \Exception
     */
    public function destroy(Course $course, File $file)
    {
        if (auth()->user()->hasPrivilege($course, Privilege::find(Privilege::EDIT_LESSONS))) {
            $removed = Storage::disk('courses')->delete($file->path);
            if ($removed) $file->delete();
            return response()->json($removed);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Not enough privileges'
            ]);
        }
    }
}
