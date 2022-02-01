@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">{{ __('New lesson') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('lessons.store', $course) }}" id="form">
                            @csrf

                            <div class="form-group row">
                                <label for="title" class="col-md-4 col-form-label text-md-right">{{ __('Title') }}</label>

                                <div class="col-md-6">
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required autofocus>

                                    @error('title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-12">
                                    <textarea id="lessonBody" class="form-control @error('body') is-invalid @enderror" name="body" rows="16">
                                        {{ old('body') }}
                                    </textarea>

                                    @error('body')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6 border rounded pt-2 mx-auto">
                                    <div class="row border-bottom pb-2">
                                        <div class="col-12">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="fileUpload" multiple>
                                                <label class="custom-file-label border-0" for="customFile">{{ __('Choose files to upload') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="files">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mx-auto">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        {{ __('Create lesson') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Used in lessons.js for ajax
        var counter = 0;
        var assetUrl = "{{ asset('') }}";
        var uploadUrl = '{{ route('files.storeNewLesson', $course) }}';
        var filesName = [];
        let filesId = [];
        var courseId = {{ $course->id }};
    </script>
    <script src="{{ asset('js/lessons.js') }}"></script>
    <script>
        tinymce.init({
            selector: '#lessonBody',
            @if(App::isLocale('es'))
                language:'es',
                @else
                language: 'en',
            @endif
        });
    </script>
@endsection
