@extends('layouts.app')

@section('head')
    <link href="{{ asset('css/courses_edit.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-2 text-right rounded-right pr-0 d-none d-lg-block">
                <div class="row" id="side-nav">
                    @if(auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editCourseInfo')->first()) || auth()->user()->isSuperAdmin())
                        <div class="col-xl-8 offset-xl-4 col-lg-10 offset-lg-2">
                            <a class="text-decoration-none" href="#courseInfo"><button class="btn btn-block p-3 btn-hover text-left">{{ __('Course information') }}</button></a>
                        </div>
                    @endif
                    @if(auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editRoles')->first()) || auth()->user()->isSuperAdmin())
                        <div class="col-xl-8 offset-xl-4 col-lg-10 offset-lg-2">
                            <a class="text-decoration-none" href="#rolesUsers"><button class="btn btn-block p-3 btn-hover text-left">{{ __('Roles') }}</button></a>
                        </div>
                    @endif
                    <div class="col-xl-8 offset-xl-4 col-lg-10 offset-lg-2">
                        <a class="text-decoration-none" href="#usersManagement"><button class="btn btn-block p-3 btn-hover text-left">{{ __('Users management') }}</button></a>
                    </div>
                </div>
            </div>
            <div class="col-md-8 mx-lg-0 mx-auto">
                @if(auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editCourseInfo')->first()) || auth()->user()->isSuperAdmin())
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header" id="courseInfo">{{ __('Course info') }}</div>

                                <div class="card-body">
                                    <form action="{{ route('courses.update', $course) }}" method="POST" enctype="multipart/form-data" id="courseInfoForm">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-xl-10 col-12 offset-xl-2">
                                                <div class="form-group row">
                                                    <label for="title" class="col-lg-2 col-md-12 col-form-label text-lg-right">{{ __('Title') }}</label>

                                                    <div class="col-xl-8 col-lg-10 col-md-9 col-12">
                                                        <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" id="courseTitle" value="{{ $course->title }}" required autofocus>

                                                        @error('title')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <div class="col-12 text-center">
                                                        <div>
                                                            <label for="courseImageUpload">{{ __('Course image') }}</label>
                                                        </div>
                                                        <label for="courseImageUpload" id="labelCourseImageUpload">
                                                            <img class="img-fluid ml-auto mr-auto border border-secondary" id="courseImage" src="{{ asset('storage/'.$course->image) }}">
                                                            <div id="imageBackground" class="align-self-center">
                                                                <div class="text-light" id="backgroundText">{{ __('Change') }}</div>
                                                            </div>
                                                        </label>

                                                        <input accept="image/x-png,image/gif,image/jpeg" type="file" id="courseImageUpload" class="form-control-file d-none @error('image') is-invalid @enderror" name="image" autofocus>

                                                        @error('image')
                                                        <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="description" class="col-12 col-form-label text-lg-center">{{ __('Description') }}</label>

                                                    <div class="col-12">
                                                        <textarea rows="16" class="form-control @error('description') is-invalid @enderror" name="description" id="courseDescription" required>{{ $course->description }}</textarea>

                                                        @error('description')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>

{{--                                                <div class="form-group row">--}}
{{--                                                    <label for="difficulty" class="col-lg-2 col-md-12 col-form-label text-lg-right">{{ __('Difficulty') }}</label>--}}

{{--                                                    <div class="col-xl-8 col-lg-10 col-12">--}}
{{--                                                        <input type="text" class="form-control @error('difficulty') is-invalid @enderror" name="difficulty" id="courseDifficulty" value="{{ $course->difficulty }}" required>--}}

{{--                                                        @error('difficulty')--}}
{{--                                                        <span class="invalid-feedback" role="alert">--}}
{{--                                                            <strong>{{ $message }}</strong>--}}
{{--                                                        </span>--}}
{{--                                                        @enderror--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}

                                                <div class="form-group row">
                                                    <label for="topic" class="col-lg-2 col-md-12 col-form-label text-lg-right">{{ __('Topic') }}</label>

                                                    <div class="col-xl-8 col-lg-10 col-12">
                                                        <input type="text" class="form-control" name="topic" id="courseTopic" value="{{ $course->topic }}" required autocomplete="topic">
                                                    </div>
                                                </div>

                                                <div class="form-group row mb-0">
                                                    <div class="col-md-2 mx-auto">
                                                        <button type="submit" class="btn btn-primary btn-block" id="courseInfoSubmit" style="display: none;">
                                                            {{ __('Save changes') }}
                                                        </button>
                                                    </div>
                                                    <div class="col-md-2 mx-auto">
                                                        <button type="button" class="btn btn-danger btn-block" id="courseInfoReset" style="display: none;">
                                                            {{ __('Reset') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div> {{-- End div.card-body --}}
                            </div> {{-- End div.card --}}
                        </div> {{-- End div.col-12 --}}
                    </div> {{-- End div.row --}}
                @endif

                @if(auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editRoles')->first()) || auth()->user()->isSuperAdmin())
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header" id="rolesUsers">{{ __('Users and roles') }}</div>

                                <div class="card-body">
                                    <form method="POST" id="rolesForm">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-lg-2 col-md-12" id="roles">
                                                <div class="row">
                                                <button type="button" class="btn btn-primary btn-block text-center mb-2 ml-auto mr-auto" id="addNewRole">{{ __('Add new role') }}</button>
                                                @foreach($course->customRoles()->orderBy('target_level', 'DESC')->get() as $customRole)
                                                    <div class="col-lg-12 col-sm-3 col-4 font-weight-bold roleContainer">
                                                        <button type="button" class="btn btn-block p-md-3 p-1 btn-hover text-center text-lg-left text-truncate role @if(auth()->user()->customRoles()->where('id', $customRole->id)->exists()) alert-info ownRole @endif" data-id="{{ $customRole->id }}" data-targetLevel="{{ $customRole->target_level }}">{{ $customRole->name }}</button>
                                                        <hr class="w-25 mt-1 mb-1 d-none d-lg-block">
                                                    </div>
                                                @endforeach
                                                </div>
                                            </div>

                                            <div class="col-lg-10 col-md-12" id="roleFields">
                                                <div class="form-group row">
                                                    <label for="roleName" class="col-lg-2 col-md-12 col-form-label text-lg-right">{{ __('Role name') }}</label>

                                                    <div class="col-xl-8 col-lg-10 col-12">
                                                        <input type="text" class="form-control" name="name" id="roleName" required disabled>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="roleDescription" class="col-lg-2 col-md-12 col-form-label text-lg-right">{{ __('Description') }}</label>

                                                    <div class="col-xl-8 col-lg-10 col-12">
                                                        <textarea class="form-control" name="description" id="roleDescription" disabled></textarea>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="roleBindedTo" class="col-lg-2 col-md-12 col-form-label text-lg-right">{{ __('Binded to') }}</label>

                                                    <div class="col-xl-4 col-lg-6 col-12">
                                                        <select class="form-control" name="bindedTo" id="roleBindedTo" disabled>
                                                            <option selected hidden disabled></option>
                                                            @foreach(App\BindRoles::orderBy('id')->get() as $bindRoles)
                                                                <option @if($bindRoles->is(App\BindRoles::where('name', 'supporters')->first()) && !auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editPrices')->first())) class="ungranted" @endif value="{{ $bindRoles->id }}">
                                                                    @if(App::isLocale('es'))
                                                                        {{ $bindRoles->translations->description_es }}
                                                                    @else
                                                                        {{ $bindRoles->translations->description_en }}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-xl-6 col-12" id="roleSearchUsers" style="display: none;">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" placeholder="{{ __('Search users to add them') }}" disabled>
                                                            <div class="input-group-append">
                                                                <button type="button" class="btn alert-success" id="roleAddUser">{{ __('Add') }}</button>
                                                            </div>
                                                        </div>
                                                        <div id="roleSearchUsersResult" class="rounded-bottom position-absolute" style="display: none;">
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="form-group row" style="display: none;" id="rolePriceContainer">
                                                    <label for="rolePrice" class="col-lg-2 col-md-12 col-form-label text-lg-right">{{ __('Price') }}</label>

                                                    <div class="col-xl-2 col-lg-3 col-12">
                                                        <input type="number" min="0" class="form-control @if(!auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editPrices')->first())) ungranted @endif" name="price" id="rolePrice" disabled>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="roleTargetLevel" class="col-lg-2 col-md-12 col-form-label text-lg-right">{{ __('Target level') }}</label>

                                                    <div class="col-xl-2 col-lg-3 col-12">
                                                        <input type="number" @if (auth()->user()->isCourseAdmin($course) || auth()->user()->isSuperAdmin())max="99" @else max="{{ auth()->user()->maxTargetLevel($course)-1 }}" @endif min="1" class="form-control" name="targetLevel" id="roleTargetLevel" disabled>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-xl-10 col-12 mx-auto">
                                                        <div class="row" id="privileges">
                                                            <div class="col-lg-6 col-12">
                                                                <div class="text-center text-secondary">{{ __('User limitations') }}</div>
                                                                @foreach($privileges->where('type', 'users_limitations') as $privilege)
                                                                    <div class="form-group row">
                                                                        <div class="col-10">
                                                                            <label for="{{ $privilege->name }}Privilege">
                                                                                @if(App::isLocale('es'))
                                                                                    {{ $privilege->translations->description_es }}
                                                                                @else
                                                                                    {{ $privilege->translations->description_en }}
                                                                                @endif
                                                                            </label>
                                                                        </div>
                                                                        <div class="col-2 text-md-center text-right">
                                                                            <div class="custom-control custom-switch">
                                                                                <input type="checkbox" class="custom-control-input @if(!auth()->user()->hasPrivilege($course, $privilege)) ungranted @endif" name="privileges[]" value="{{ $privilege->id }}" id="{{ $privilege->name }}Privilege" disabled>
                                                                                <label class="custom-control-label" for="{{ $privilege->name }}Privilege"></label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-12 text-muted text-justify">
                                                                            @if(App::isLocale('es'))
                                                                                {{ $privilege->translations->long_description_es }}
                                                                            @else
                                                                                {{ $privilege->translations->long_description_en }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    @if(!$loop->last)
                                                                        <hr class="w-25">
                                                                    @endif
                                                                @endforeach
                                                            </div>

                                                            <div class="col-lg-6 col-12">
                                                                <div class="text-center text-secondary">{{ __('Course management') }}</div>
                                                                @foreach($privileges->where('type', 'course_management') as $privilege)
                                                                    <div class="form-group row">
                                                                        <div class="col-10">
                                                                            <label for="{{ $privilege->name }}Privilege">
                                                                                @if(App::isLocale('es'))
                                                                                    {{ $privilege->translations->description_es }}
                                                                                @else
                                                                                    {{ $privilege->translations->description_en }}
                                                                                @endif
                                                                            </label>
                                                                        </div>
                                                                        <div class="col-2 text-md-center text-right">
                                                                            <div class="custom-control custom-switch">
                                                                                <input type="checkbox" class="custom-control-input @if(!auth()->user()->hasPrivilege($course, $privilege)) ungranted @endif" name="privileges[]" value="{{ $privilege->id }}" id="{{ $privilege->name }}Privilege" disabled>
                                                                                <label class="custom-control-label" for="{{ $privilege->name }}Privilege"></label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-12 text-muted text-justify">
                                                                            @if(App::isLocale('es'))
                                                                                {{ $privilege->translations->long_description_es }}
                                                                            @else
                                                                                {{ $privilege->translations->long_description_en }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    @if(!$loop->last)
                                                                        <hr class="w-25">
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row" style="display: none;" id="roleUnsavedChanges">
                                                    <div class="col-md-8 fixed-bottom mx-auto">
                                                        <div class="row">
                                                            <div class="col-lg-10 col-md-12 p-0 offset-lg-2">
                                                                <div class="row text-center mb-5">
                                                                    <div class="col-6 lightBg rounded">
                                                                        <span class="btn">{{ __('You have unsaved changes') }}</span>
                                                                    </div>
                                                                    <div class="col-6 lightBg">
                                                                        <button type="button" id="resetRole" class="btn mr-1">
                                                                            {{ __('Reset') }}
                                                                        </button>
                                                                        <button type="submit" id="saveRole" class="btn btn-success">
                                                                            {{ __('Save changes') }}
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row mb-0">
                                                    <div class="col-xl-2 col-lg-4 offset-xl-4 offset-lg-2">
                                                        <button type="button" id="deleteRole" style="display: none;" class="btn btn-danger btn-block">
                                                            {{ __('Delete role') }}
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </form>
                                </div> {{-- End div.card-body --}}
                            </div> {{-- End div.card --}}
                        </div> {{-- End div.col-12 --}}
                    </div> {{-- End div.row --}}
                @endif

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header" id="usersManagement">{{ __('Users management') }}</div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-2 col-lg-3 col-12" id="usersFilters">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <label for="usersFilterUsername" class="col-12 col-form-label text-muted">{{ __('Username') }}</label>

                                                    <div class="col-12">
                                                        <input type="text" class="form-control" id="usersFilterUsername" placeholder="{{ __('Search users') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <label for="usersFilterRole" class="col-12 col-form-label text-muted">{{ __('Role') }}</label>

                                                    <div class="col-12">
                                                        <select class="form-control" name="role" id="usersFilterRole">
                                                            <option selected value="">{{ __('Not selected') }}</option>
                                                            @foreach($course->customRoles()->whereDoesntHave('bindedTo', function($query) { $query->where('name', 'users')->orWhere('name', 'followers'); })->get() as $customRole)
                                                                <option value="{{ $customRole->id }}">
                                                                    {{ $customRole->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="d-lg-none">
                                    <div class="col-10">
                                        <div class="row">
                                            <div class="col-12" id="usersContainer">
                                                @foreach($course->users()->paginate(2) as $user)
                                                    <div class="row user" data-userid="{{ $user->id }}">
                                                        <div class="col-xl-1 col-lg-2 col-md-3 col-4">
                                                            <img src="{{ asset('storage/'.$user->avatar) }}" class="img-fluid rounded-circle">
                                                        </div>
                                                        <div class="col-xl-2 col-lg-3 col-md-9 col-8">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <span>{{ $user->username }}#{{ $user->suffixId }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <span class="text-muted">@if ($user->followCourses->contains($course->id)) {{ $user->followCourses()->find($course->id)->pivot->created_at->toDateString() }} @else {{ __('Not following') }} @endif</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-9 col-lg-7 col-12 mt-3 mt-lg-0 userRolesList">
                                                            @foreach($user->customRoles()->where('course_id', $course->id)->get() as $customRole)
                                                                <span class="border rounded-pill pl-2 pr-2 pt-1 pb-1" data-roleid="{{ $customRole->id }}">
                                                                    {{ $customRole->name }}
                                                                    @if ($customRole->pivot->supporter == 0 || auth()->user()->isSuperAdmin())
                                                                        <i class="fas fa-times text-danger userRemoveRole"></i>
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @if(!$loop->last)
                                                        <hr class="w-75">
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div class="mt-4 ml-3" id="usersLinks">
                                                {!! $course->users()->paginate(2)->links()->render() !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> {{-- End div.card-body --}}
                        </div> {{-- End div.card --}}
                    </div> {{-- End div.col-12 --}}
                </div> {{-- End div.row --}}

                @if ($course->createdBy->is(auth()->user()) || auth()->user()->isSuperAdmin())
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header" id="usersManagement">{{ __('Delete course') }}</div>

                                <div class="card-body">
                                    @if(!$course->hasSupporters() || auth()->user()->isSuperAdmin())
                                        <div class="row">
                                            <div class="col-xl-7 col-lg-8 col-12">
                                                <div class="row">
                                                    <div class="col-12">
                                                        {{ __('You can delete this course by pressing the delete button') }}
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        {{ __('Keep in mind that this action cannot be undone and all the content of the course will be lost') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-5 col-lg-4 col-12 my-auto">
                                                <form action="{{ route('courses.destroy', $course) }}" method="POST" id="deleteCourseForm">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <button id="deleteCourse" class="btn btn-block btn-danger">Delete course</button>
                                            </div>
                                        </div>
                                        @else
                                        <div class="row">
                                            <div class="col-xl-8 col-lg-10 col-12 mx-auto">
                                                <div class="alert-warning rounded text-center">
                                                    <p>
                                                        {{ __("This course cannot be deleted because there are users that have paid for a supporter role.") }}
                                                    </p>
                                                    <p>
                                                        {{ __("If you still want to delete it you can contact with an administrator at cosadiesma0@gmail.com") }}
                                                    </p>
                                                    <p>
                                                        {{ __("Chances are that the course won't be deleted but give a good enough reason we will delete it") }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div> {{-- End div.col-md-8 --}}
        </div> {{-- End div.row --}}
    </div> {{-- End div.container-fluid --}}

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
@endsection
@section('scripts')
    <script>
        var isSuperAdmin = {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }};
        var isCourseCreator = {{ $course->createdBy->is(auth()->user()) ? 'true' : 'false' }};
        var course = {!! json_encode($course, JSON_HEX_TAG) !!};
        var assetUrl = "{{ asset('') }}";
        var publicStorage = "{{ asset('storage/') }}/";
        var supporterBindId = {{ App\BindRoles::where('name', 'supporters')->first()->id }};
        var specificUsersBindId = {{ App\BindRoles::where('name', 'specific')->first()->id }};
        var usersIndex = "{{ route('users.index') }}";
    </script>
    <script src="{{ asset('js/courses_edit.js') }}"></script>
    <script>
        tinymce.init({
            selector: '#courseDescription',
            @if(App::isLocale('es'))
            language:'es',
            @else
            language: 'en',
            @endif
            setup: function(ed) {
                ed.on('change', function (e) {
                    $('#courseDescription').trigger('change');
                });
            },
        });
    </script>
@endsection
