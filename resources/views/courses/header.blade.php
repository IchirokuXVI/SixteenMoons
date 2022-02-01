@extends('layouts.sidebar')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-1 col-12 pr-0 mr-0">
                <img class="img-fluid border rounded" src="{{ asset('storage/'.$course->image) }}">
            </div>
            <div class="col-xl-2 col-lg-3 col-12 text-center text-lg-left my-auto h5">
                {{ $course->title }}
            </div>
            <div class="col-lg-3 col-12 my-3 my-lg-auto">
                <div class="row">
                    <div class="col-3 text-center"><a class="@if('/'.request()->path() === route('courses.show', $course, false)) active @else text-decoration-none @endif" href="{{ route('courses.show', $course) }}">{{ __('Home') }}</a></div>
                    <div class="col-4 text-center"><a class="@if('/'.request()->path() === route('lessons.index', $course, false)) active @else text-decoration-none  @endif" href="{{ route('lessons.index', $course) }}">{{ __('Lessons') }}</a></div>
                    <div class="col-5 text-center"><a class="@if('/'.request()->path() === route('courses.indexFollowers', $course, false))  active @else text-decoration-none  @endif" href="{{ route('courses.indexFollowers', $course) }}">{{ __('Followers') }}</a></div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-12 order-12 ml-auto mr-5 my-auto">
                <div class="row">
                    <div class="col-5">
                        @if(auth()->check() && auth()->user()->followCourses()->where('id', $course->id)->exists())
                            <a class="text-decoration-none" href="{{ route('users.unFollowCourse', $course) }}">
                                <button type="button" class="btn btn-block btn-danger">
                                        {{ __('Unfollow') }}
                                </button>
                            </a>
                        @elseif(!auth()->check() || (auth()->check() && !$course->createdBy->is(auth()->user())))
                            <a class="text-decoration-none" href="{{ route('users.followCourse', $course) }}">
                                <button type="button" class="btn btn-block btn-primary">
                                    {{ __('Follow') }}
                                </button>
                            </a>
                        @endif
                    </div>
                    <div class="col-5">
                        @if(auth()->check() && !$course->customRoles->where('binded_to', App\BindRoles::where('name', 'supporters')->first()->id)->isEmpty())
                            <a class="text-decoration-none" href="{{ route('payment.create', $course) }}">
                                <button type="button" class="btn btn-block btn-success text-white">
                                {{ __('Support') }}
                                </button>
                            </a>
                        @endif
                    </div>

                    <div class="col-1 my-auto">
                        @if(auth()->check() && (auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editCourseInfo')->first()) || auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editRoles')->first()) || auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editLessons')->first())))
                            <span class="dropdown">
                                <a id="courseSettings" class="dropdown-toggle text-decoration-none text-muted" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <span class="caret">
                                        <i class="fas fa-cog text-muted"></i>
                                    </span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="courseSettings">
                                    @if(auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editCourseInfo')->first()) || auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editRoles')->first()))
                                        <a class="dropdown-item" href="{{ route('courses.edit', $course) }}">
                                            {{ __('Edit course') }}
                                        </a>
                                    @endif
                                    @yield('settings')
                                </div>

                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <hr>
        @yield('body')
    </div>
@endsection
