@extends('layouts.sidebar')
@section('head')
    <link href="{{ asset('css/profile.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h3 id="accountInformation">{{ __('Account information') }}</h3>
                <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" id="profileForm">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-xl-2 col-lg-3 col-12 text-center" id="avatar_container">
                            {{--                            When clicking the label (avatar) the file manager will open because the input type file has the same id as the label --}}
                            <label for="avatar_upload" id="label_avatar_upload">
                                <img id="avatar" class="img-fluid rounded-circle" src="{{ asset('storage/'. $user->avatar) }}">
                                <div id="avatar_background" class="align-self-center"></div>
                                <div class="text-light" id="avatar_text">{{ __('Change avatar') }}</div>
                            </label>
                            <input style="display: none;" id="avatar_upload" type="file" name="avatar" accept="image/x-png,image/gif,image/jpeg" disabled>
                            @error('avatar')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="row mt-3">
                                <div class="col-lg-4 col-12">
                                    <div class="text-secondary">{{ __('Username') }}</div>
                                    @if (auth()->check() && (auth()->user()->is($user) || auth()->user()->isSuperAdmin()))
                                        <input class="form-control @error('username') is-invalid @enderror" style="display: none;" id="usernameInput" type="text" name="username" value="{{ $user->username }}">
                                    @endif
                                    <span id="username">{{ $user->username }}</span><span id="suffixId">#{{ $user->suffixId }}</span>
                                    @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="col-lg-8 col-12">
                                    <div class="text-secondary">{{ __('E-Mail Address') }}</div>
                                    @if (auth()->check() && (auth()->user()->is($user) || auth()->user()->isSuperAdmin()))
                                        <input class="form-control @error('email') is-invalid @enderror" style="display: none;" id="emailInput" type="email" name="email" value="{{ $user->email }}">
                                    @endif
                                    <span id="email">{{ $user->email }}</span>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            @if (auth()->check() && (auth()->user()->is($user) || auth()->user()->isSuperAdmin()))
                                <div class="row">
                                    @if(!auth()->user()->isSuperAdmin())
                                        <div style="display: none;" class="col-lg-4 col-12" id="currentPasswordContainer">
                                            <label for="current_password" class="text-secondary">{{ __('Current password') }}</label>
                                            <input class="form-control @error('current_password') is-invalid @enderror" type="password" name="current_password" id="current_password" placeholder="{{ __('Current password') }}"></input>
                                            @error('current_password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    @endif
                                    <div class="col-lg-8 col-12">
                                        <div class="row">
                                            <div id="changePasswordButtonContainer" style="display: none;" class="col-12">
                                                <div id="email" class="text-secondary">{{ __('Press if you want to change the password') }}</div>
                                                <button type="button" class="btn alert-success rounded mt-2" id="changePassword">{{ __('Change password') }}</button>
                                            </div>
                                            <div id="changePasswordInputs" class="col-12" style="display: none;">
                                                <div class="row">
                                                    <div class="col-lg-6 col-12">
                                                        <label for="password" class="text-secondary">{{ __('New password') }}</label>
                                                        <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" id="password" placeholder="{{ __('New password') }}"></input>
                                                        @error('password')
                                                        <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-lg-6 col-12">
                                                        <label for="password_confirmation" class="text-secondary">{{ __('Confirm password') }}</label>
                                                        <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" placeholder="{{ __('Confirm password') }}"></input>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if (auth()->check() && (auth()->user()->is($user) || auth()->user()->isSuperAdmin()))
                            <div class="col-lg-1 col-12">
                                <button type="button" class="btn btn-block alert-primary btn-sm" id="editUser">{{ __('Edit') }}</button>
                                <button type="submit" style="display: none;" class="btn btn-block alert-success btn-sm" id="saveUser">{{ __('Save') }}</button>
                                <button type="button" style="display: none;" class="btn btn-block alert-danger btn-sm" id="cancelUser">{{ __('Cancel') }}</button>
                            </div>
                        @endif
                    </div>
                </form>
                <hr class="w-75">
                @if($user->createdCourses->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h3 id="myCourses" class="mb-4">{{ __('Courses') }}</h3>
                            @foreach($user->createdCourses as $course)
                                <div class="row">
                                    <div class="col-lg-2 col-md-3 d-none d-md-block">
                                        <img class="img-fluid course-image" src="{{ asset('storage/'.$course->image) }}">
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-12">
                                        <a href="{{ route('courses.show', $course) }}" class="text-decoration-none text-secondary"><span class="h5">{{ $course->title }}</span></a>
                                        <div class="row text-center mt-1">
                                            <div class="col-4">{{ __('Followers') }}</div>
                                            <div class="col-8">{{ __('Recent followers') }}</div>
                                        </div>
                                        <div class="row text-center mb-2">
                                            <div class="col-4">
                                                {{ $course->followedBy->count() }}
                                            </div>
                                            <div class="col-8">
                                                {{ $course->recentFollowers()->count() }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-12 align-self-center text-center">
                                        <a href="{{ route('courses.historicalFollowers', $course) }}" class="text-decoration-none"><span class="text-info">{{ __('Historical followers') }}</span></a>
                                    </div>
                                </div>
                                <hr class="w-75">
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@if (auth()->check() && (auth()->user()->is($user) || auth()->user()->isSuperAdmin()))
    <div class="modal fade" id="cropperModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <p>{{ __('You can use the wheel of your mouse or the gestures of your screen to increase or decrease the size of the image') }}</p>
                    <div class="row p-0 m-0">
                        <div class="col-12 m-0 p-0 cropper-container">
                            <img class="cropper-img img-fluid">
                        </div>
    {{--                    <div class="col-4 overflow-hidden preview rounded-circle">--}}
    {{--                        <img class="previewImg">--}}
    {{--                    </div>--}}
                    </div>
                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-primary" id="saveCroppedImg">{{ __('Save') }}</button>
                        <button type="button" class="btn btn-danger" id="cancelCroppedImg">{{ __('Cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
    @if (auth()->check() && (auth()->user()->is($user) || auth()->user()->isSuperAdmin()))
        <script>
            var assetUrl = "{{ asset('') }}";
            var userId = {{ $user->id }};
        </script>
        <script src="{{ asset('js/profile.js') }}"></script>
        @if($errors->any())
    {{--        Simply clicking the buttons with javascript if there was any error, this way the profile is opened to see the errors --}}
            <script>
                $('#editUser').trigger('click');
                @error('password')
                    $('#changePassword').trigger('click');
                @enderror
            </script>
        @endif
    @endif
@endsection
