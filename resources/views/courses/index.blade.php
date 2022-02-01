@extends('layouts.sidebar')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-4 col-12 border-right text-lg-left" id="filtersContainer">
                <div class="col-lg-8 col-md-10 offset-md-2 col-12 offset-lg-4">
                    <form id="filters" action="{{ route('courses.index') }}" method="GET">
                        @if (isset($searchTerm))
                            <input type="hidden" name="searchTerm" value="{{ $searchTerm }}">
                        @endif
                        <span class="font-weight-bold">{{ __('Topic') }}</span>
                        @foreach($topics as $topic)
                            <div class="custom-control custom-checkbox mt-1">
                                <input type="checkbox" class="custom-control-input" id="topic_{{ $topic }}" name="topic[]" value="{{ $topic }}" @if(in_array($topic, $selectedTopics)) checked @endif>
                                <label class="custom-control-label unselectable" for="topic_{{ $topic }}">{{ $topic }}</label>
                            </div>
                        @endforeach
                        <span class="font-weight-bold">{{ __('Custom filters') }}</span>
                        <div id="custom_filters">
                            @foreach($selectedTopics as $topic)
                                @if(!in_array($topic, $topics) && !in_array($topic, isset($_COOKIE['custom_filters']) ? json_decode($_COOKIE['custom_filters']) : []))
                                    <div class="custom-control custom-checkbox mt-1">
                                        <input type="checkbox" class="custom-control-input" id="topic_{{ $topic }}" name="topic[]" value="{{ $topic }}" checked>
                                        <label class="custom-control-label unselectable" for="topic_{{ $topic }}">{{ $topic }}</label>
                                    </div>
                                @endif
                            @endforeach
                            @if(isset($_COOKIE['custom_filters']))
                                @foreach(json_decode($_COOKIE['custom_filters']) as $topic)
                                    @if(!in_array($topic, $topics))
                                        <div class="col-11 custom-control custom-checkbox mt-1" id="custom_filters">
                                            <input type="checkbox" class="custom-control-input" id="custom_topic_{{ $topic }}" name="topic[]" value="{{ $topic }}" @if(in_array($topic, $selectedTopics)) checked @endif>
                                            <label class="custom-control-label unselectable" for="custom_topic_{{ $topic }}">{{ $topic }}</label>
                                            <i class="fas fa-trash custom_filter_remove" data-topic="{{ $topic }}"></i>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="input-group mt-2">
                            <input type="text" class="form-control border-right-0" placeholder="{{ __('Your topic filter') }}" id="custom_filter_input" aria-describedby="custom_filter_add">
                            <div class="input-group-append">
                                <span class="input-group-text bg-white" id="custom_filter_add"><i class="fas fa-plus"></i></span>
                            </div>
                        </div>

{{--                        <hr class="w-50">--}}
{{--                        <span class="font-weight-bold">{{ __('Difficulty') }}</span>--}}
{{--                        @foreach($difficulties as $difficulty)--}}
{{--                            <div class="custom-control custom-checkbox mt-1">--}}
{{--                                <input type="checkbox" class="custom-control-input" id="topic_{{ $difficulty }}" name="difficulty[]" value="{{ $difficulty }}" @if(in_array($difficulty, $selectedDifficulties)) checked @endif>--}}
{{--                                <label class="custom-control-label unselectable" for="topic_{{ $difficulty }}">{{ ucfirst($difficulty) }}</label>--}}
{{--                            </div>--}}
{{--                        @endforeach--}}

                        <button type="submit" class="col-6 mt-3 mb-4 btn btn-block btn-success ml-auto">{{ __('Filter') }}</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-6 col-md-8">
                <div class="row">
                    <div class="col-lg-3 col-md-4 col-6">
                    @auth
                        <a href="{{ route('courses.create') }}" class="text-decoration-none"><button class="btn btn-primary btn-block">{{ __('Create new course') }}</button></a>
                    @endauth
                    </div>
                    <div class="col-lg-3 col-md-4 col-6 ml-auto">
                        {{ $courses->appends(request()->input())->links() }}
                    </div>
                </div>
                <div class="text-center font-weight-bold">@if (isset($searchTerm)){{ __('Results for') }} "{{ $searchTerm }}" @else {{ __('All courses') }} @endif</div>
                <div class="row">
                    @forelse($courses as $course)
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="row">
                                <div class="col-6 text-truncate">
                                    <a class="text-decoration-none" href="{{ route('users.show', $course->createdBy) }}"><span class="text-left text-truncate text-muted">{{ $course->createdBy->username }}#{{ $course->createdBy->suffixId }}</span></a>
                                </div>
                                <div class="col-6 text-truncate">
                                    <a class="text-decoration-none" href="{{ route('courses.index') }}?topic[]={{ $course->topic }}"><span class="text-left text-truncate text-muted">{{ $course->topic }}</span></a>
                                </div>
                            </div>
                            <div class="border rounded">
                                <a class="text-decoration-none" href="{{ route('courses.show', $course) }}">
                                    <img class="img-fluid rounded-top" src="{{ asset('storage/'.$course->image) }}">
                                    <div class="text-center text-truncate">{{ $course->title }}</div>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center h3">{{ __("There aren't courses with that filter") }}</div>
                    @endforelse
                </div>
                <div class="row mt-4">
                    <div class="col-lg-3 col-md-4 col-6 ml-auto">
                        {{ $courses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var assetUrl = "{{ asset('') }}";
    </script>
    <script src="{{ asset('js/courses_index.js') }}"></script>
@endsection
