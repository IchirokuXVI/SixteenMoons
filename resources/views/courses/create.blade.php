@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">{{ __('New course') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('courses.store') }}">
                            @csrf

                            <div class="form-group row">
                                <label for="title" class="col-md-4 col-form-label text-md-right">{{ __('Title') }}</label>

                                <div class="col-md-6">
                                    <input type="text" id="title" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required autofocus>

                                    @error('title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="description" class="col-md-12 col-form-label text-center">{{ __('Description') }}</label>

                                <div class="col-12">
                                    <textarea rows="16" id="description" class="form-control @error('description') is-invalid @enderror" name="description">{{ old('description') }}</textarea>

                                    @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

{{--                            <div class="form-group row">--}}
{{--                                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Difficulty') }}</label>--}}

{{--                                <div class="col-md-6">--}}
{{--                                    <input type="text" id="difficulty" class="form-control @error('difficulty') is-invalid @enderror" name="difficulty" value="{{ old('difficulty') }}" required>--}}

{{--                                    @error('difficulty')--}}
{{--                                    <span class="invalid-feedback" role="alert">--}}
{{--                                        <strong>{{ $message }}</strong>--}}
{{--                                    </span>--}}
{{--                                    @enderror--}}
{{--                                </div>--}}
{{--                            </div>--}}

                            <div class="form-group row">
                                <label for="topic" class="col-md-4 col-form-label text-md-right">{{ __('Topic') }}</label>

                                <div class="col-md-6">
                                    <input type="text" class="form-control @error('topic') is-invalid @enderror" name="topic" value="{{ old('topic') }}" required autocomplete="topic">

                                    @error('topic')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Create course') }}
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
        tinymce.init({
            selector: '#description',
            @if(App::isLocale('es'))
            language:'es',
            @else
            language: 'en',
            @endif
        });
    </script>
@endsection
