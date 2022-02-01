@extends('layouts.app')
@section('sidebar')
    <div class="col-lg-2 bg-white shadow-sm pt-3 pl-4 sticky-top d-lg-block d-none" id="followedCourses">
        <div class="row">
            @if(auth()->check() && auth()->user()->followCourses->count() > 0)
                <div class="col-11">
                    <div class="row">
                        <div class="col-12 h5 text-dark">
                            {{ __('Followed courses') }}
                        </div>
                    </div>
                    @foreach(auth()->user()->coursesOrderedByNewContent() as $course)
                            <div class="row py-1 followedCourse" data-id="{{ $course->id }}">
                                <div class="col-10 btn btn-hover text-left">
                                    <div class="row" onclick="location.href='{{ route('courses.show', $course) }}';">
                                        <div class="col-5">
                                            <img src="{{ asset('storage/'.$course->image) }}" class="img-fluid border rounded">
                                        </div>
                                        <div class="col-7 my-auto text-truncate">
                                            <span class="text-muted">{{ $course->title }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if ($course->notifications_count > 0)
                                    <div class="col-1 my-auto">
                                        <span class="dropdown">
                                            <a id="sidebarNewLesson{{ $loop->iteration }}" class="dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                                <span class="caret">
                                                    {{ $course->notifications_count }}
                                                </span>
                                            </a>

                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                                @foreach(auth()->user()->newContent($course) as $notification)
                                                    <a class="dropdown-item" href="{{ asset($notification->url) }}">
                                                        {{ $notification->translations->message_en }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </span>
                                    </div>
                                @endif
                            </div>

                        @if (!$loop->last)
                            <hr class="w-75 mt-1 mb-1">
                        @endif
                    @endforeach
                </div>
            @elseif (!auth()->check())
                <span class="text-info mx-auto">{{ __('Login to start following courses !') }}</span>
            @else
                <span class="text-info mx-auto">{{ __('Follow courses to view them here !') }}</span>
            @endif
        </div>
    </div>
@endsection
