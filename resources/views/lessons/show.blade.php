@extends('courses.header')
@section('settings')
    @if(auth()->check() && (auth()->user()->hasPrivilege($course, App\Privilege::where('name', 'editLessons')->first())))
        <a class="dropdown-item" href="{{ route('lessons.edit', ['course' => $course, 'lesson' => $lesson]) }}">
            {{ __('Edit lesson') }}
        </a>
    @endif
@endsection
@section('body')
    <div class="row">
        <div class="col-lg-10 offset-lg-1 col-12">
            <div class="text-center font-weight-bold h2">{{ $lesson->title }}</div>
            <div class="row">
                <div class="col-12">{{ $lesson->description }}</div>
            </div>
            <div class="row">
                <div class="col-12">
                    {!! $lesson->body !!}
                </div>
            </div>
            @if((auth()->check() && auth()->user()->hasPrivilege($course, App\Privilege::find(App\Privilege::DOWNLOAD_FILES))) || $course->unloggedHasPrivilege(App\Privilege::find(App\Privilege::DOWNLOAD_FILES)))
                @if($lesson->files->count() > 0)
                    <div class="row mb-4">
                        <div class="col-12 text-secondary text-center">{{ __('Attached files') }}</div>
                        <div class="col-8 mx-auto border pt-2 pb-2">
                            @foreach($lesson->files as $file)
                                <a class="text-decoration-none" href="{{ route('files.download', ['course' => $course, 'lesson' => $lesson, 'file' => $file]) }}" target="_blank" rel="noopener noreferrer">
                                    <div class="row">
                                        <div class="col-lg-8 col-9 text-truncate">{{ $file->original_name }}</div>
                                        <div class="col-lg-2 col-3 text-center">{{ $file->format->name }}</div>
                                        @php($fileSize = Storage::disk('courses')->size($file->path))
                                        <div class="col-2 d-md-block d-none text-center text-truncate">{{ $fileSize/1024 > 1024 ? round(($fileSize/1024/1024), 2).'MB' : round(($fileSize/1024), 2).'KB' }}</div>
                                    </div>
                                </a>
                                @if(!$loop->last)
                                    <hr class="w-75 m-2 ml-auto mr-auto">
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
            <div class="row">
                <div class="col-lg-10 col-12 mx-auto bg-white bordered rounded px-lg-5 px-md-4 px-3 pb-4">
                    <div class="text-secondary mb-3 mt-3"><span id="commentsCount">{{ $lesson->comments->count() }}</span> {{ __('comments') }}</div>
                    @if(auth()->check() && auth()->user()->hasPrivilege($course, App\Privilege::find(App\Privilege::COMMENT_LESSONS)))
                        <div class="row mb-3">
                            <div class="col-12">
                                <div>{{ __('New comment') }}</div>
                                <textarea class="form-control" id="commentBody"></textarea>
                                <button id="addComment" class="btn btn-primary mt-2">{{ __('Add comment') }}</button>
                            </div>
                        </div>
                    @endif
                    <div id="commentsContainer">
                        @forelse($paginated = $lesson->comments()->with('user')->orderBy('created_at', 'DESC')->paginate(10) as $comment)
                            <div class="row bg-light bordered rounded mb-3 pt-2">
                                <div class="col-2 col-lg-1">
                                    <div class="row">
                                        <div class="col-12">
                                            <img class="img-fluid rounded-circle" src="{{ asset('storage/'.$comment->user->avatar) }}">
                                        </div>
                                    </div>
                                    @foreach($comment->user->customRoles()->wherePivot('supporter', '1')->get() as $customRole)
                                        <div class="row text-center my-3">
                                            <div class="col-12">
                                                <span class="border rounded-pill pl-2 pr-2 pt-1 pb-1" data-roleid="{{ $customRole->id }}">
                                                    {{ $customRole->name }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-10 col-lg-11">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="row border-bottom">
                                                <div class="col-lg-8 col-md-10 col-9">
                                                    <span class="text-info d-sm-inline d-block text-truncate cursor-pointer" onclick="location.href='{{ route('users.show', $comment->user) }}'">{{ $comment->user->username }}#{{ $comment->user->suffixId }}</span>
                                                    <span class="d-sm-inline d-block text-secondary ml-sm-4">{{ \Carbon\Carbon::create($comment->created_at->toDateTimeString())->format('Y-m-d h:m:s') }}</span>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <p>
                                                {{ $comment->body }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                                <div class="col-12" id="noComments">{{ __('This lesson does not have comments yet') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let page = 1;
        let morePages = {{ $paginated->lastPage() !== 1 ? 'true' : 'false' }};
        $(() => {
            if (morePages) {
                $(window).on('scroll', loadOnScroll);
                if ($("body").width() > $(window).width() && $(window).scrollTop() >= $(document).height() - $(window).height() - 60) {
                    loadOnScroll();
                }
            }

            $('#addComment').click(function() {
                $('#addComment').prop('disabled', true);
                $.ajax({
                    url: '{{ route('comments.store', ['course' => $course, 'lesson' => $lesson]) }}',
                    dataType: 'json',
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: {
                        body: $('#commentBody').val()
                    },
                    success: function(data) {
                        $('#noComments').remove();
                        $('#addComment').prop('disabled', false);
                        $('#commentsCount').text(parseInt($('#commentsCount').text()) + 1);
                        $('#commentBody').val('');
                        addNewComment(data.comment);
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        let response = XMLHttpRequest.responseJSON;
                        console.log(response.errors);
                        if (response) {
                            if (response.errors) {
                                if (response.errors.body) {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.errors.body,
                                        icon: 'warning'
                                    });
                                    $('#addComment').prop('disabled', false);
                                }
                            }
                        }
                    }
                });
            });
        });

        function loadOnScroll() {
            if ($(window).scrollTop() >= $(document).height() - $(window).height() - 60) {
                loadingComments();
                $(window).off('scroll');
                $.ajax({
                    url: '{{ route('comments.index', ['course' => $course, 'lesson' => $lesson]) }}',
                    data: {
                        //Increment before assign so if variable page is 1 then it will be incremented to 2 and the ajax will send 2
                        page: ++page
                    },
                    success: function(data) {
                        stopLoadingComments();
                        addComments(data.comments.data);
                        $(window).on('scroll', loadOnScroll);
                        // If there aren't more comments then disable the event
                        if (!data.comments.to || data.comments.to == data.comments.total) $(window).off('scroll');
                    }
                });
            }
        }

        function addNewComment(comment) {
            $('#commentsContainer').prepend(`
                <div class="row bg-light bordered rounded mb-3 pt-2">
                    <div class="col-2 col-lg-1">
                        <img class="img-fluid rounded-circle" src="{{ asset('storage/') }}/${comment.user.avatar}">
                    </div>
                    <div class="col-10 col-lg-11">
                        <div class="row">
                            <div class="col-12">
                                <div class="row border-bottom">
                                    <div class="col-lg-11 col-md-10 col-9">
                                        <span class="text-info d-sm-inline d-block text-truncate cursor-pointer" onclick="location.href='${"{{ route('users.show', ':id') }}".replace(':id', comment.user.id)}'">${comment.user.username}#${comment.user.suffixId}</span>
                                        <span class="d-sm-inline d-block text-secondary ml-sm-4">${Cookies.get('lang') == 'en' ? 'Just now' : 'Ahora mismo'}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <p>
                                    ${comment.body}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }

        function addComments(comments) {
            for(let comment of comments) {
                $('#commentsContainer').append(`
                    <div class="row bg-light bordered rounded mb-3 pt-2">
                        <div class="col-2 col-lg-1">
                            <img class="img-fluid rounded-circle" src="{{ asset('storage/') }}/${comment.user.avatar}">
                        </div>
                        <div class="col-10 col-lg-11">
                            <div class="row">
                                <div class="col-12">
                                    <div class="row border-bottom">
                                        <div class="col-lg-11 col-md-10 col-9">
                                            <span class="text-info d-sm-inline d-block text-truncate cursor-pointer" onclick="location.href='${"{{ route('users.show', ':id') }}".replace(':id', comment.user.id)}'">${comment.user.username}#${comment.user.suffixId}</span>
                                            <span class="d-sm-inline d-block text-secondary ml-sm-4">${new Date(comment.created_at).toLocaleString()}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <p>
                                        ${comment.body}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }
        }

        function loadingComments() {
            $('#commentsContainer').append(`
                <div class="col-12 mt-4 text-center" id="loadingCommentsContainer">
                    <i class="fas fa-circle-notch fa-spin loading fa-sm text-center p-0 m-0 ml-2"></i>
                </div>
            `);
        }

        function stopLoadingComments() {
            $('#loadingCommentsContainer').remove();
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
    </script>
@endsection
