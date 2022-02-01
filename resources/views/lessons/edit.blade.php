@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-12">
                <div class="card">
                    <div class="card-header">{{ __('Edit lesson') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('lessons.update', ['course' => $course, 'lesson' => $lesson]) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group row">
                                <label for="title" class="col-md-4 col-form-label text-md-right">{{ __('Title') }}</label>

                                <div class="col-md-6">
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ $lesson->title }}" required autofocus>

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
                                        {{ $lesson->body }}
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
                                        @foreach($lesson->files as $file)
{{--                                            Iteration starts at 1, $loop->index starts at 0, in the create view it starts at 1 in jquery, that is why I am starting at 1 --}}
                                            <div class="row" id="file{{ $loop->iteration }}">
                                                <div class="col-md-5 col-6 fileName">{{ $file->original_name }}</div>
                                                <div class="col-md-3 d-md-block d-none">{{ Carbon\Carbon::parse($file->created_at)->format('d/m/Y') }}</div>
                                                @php($fileSize = Storage::disk('courses')->size($file->path))
                                                <div class="col-md-3 col-4">{{ $fileSize/1024 > 1024 ? round(($fileSize/1024/1024), 2).'MB' : round(($fileSize/1024), 2).'KB' }}</div>
                                                <div class="col-lg-1 col-2">
                                                    <i class="fas fa-times text-danger removeFile" data-fileId="{{ $file->id }}"></i>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mx-auto">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        {{ __('Update lesson') }}
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
        var counter = {{ $lesson->files->count() }};
        var assetUrl = "{{ asset('') }}";
        var uploadUrl = '{{ route('files.store', ['course' => $course, 'lesson' => $lesson]) }}';
        var filesName = [
            @foreach($lesson->files as $file)
            '{{ $file->original_name }}',
            @endforeach
        ];
        //filesId has all the ids of the files that are already associated with the lesson and the new files are also added to this array
        let filesId = [
            @foreach($lesson->files as $file)
            {{ $file->id }},
            @endforeach
        ];
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
