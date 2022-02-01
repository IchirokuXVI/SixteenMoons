@extends('courses.header')

@section('body')
    <div class="row">
        <div class="col-12 mb-2">
            @if (auth()->check() && auth()->user()->hasPrivilege($course,App\Privilege::find(App\Privilege::CREATE_LESSONS)))
                <a href="{{ route('lessons.create', $course) }}" class="text-decoration-none text-white">
                    <button class="btn btn-success">{{ __('Add new lesson') }}</button>
                </a>
            @endif
        </div>
        <div class="col-xl-10 col-12">
            <div class="row text-secondary pl-3">
                <div class="col-1 text-center">
                    #
                </div>
                <div class="col-xl-7 col-8">
                    {{ __('Title') }}
                </div>
                <div class="col-xl-2 col-3 d-none d-md-block text-center">
                    {{ __('Date') }}
                </div>
                @if(auth()->check() && auth()->user()->hasPrivilege($course, App\Privilege::find(App\Privilege::EDIT_LESSONS)))
                    <div class="col-2">
                        {{ __('Actions') }}
                    </div>
                @endif
            </div>
            @forelse($lessons as $lesson)
                <div class="row btn-hover p-3">
                    <div class="col-1 text-center">
                            {{ $loop->iteration + ($lessons->currentPage() - 1) * $lessons->perPage() }}
                    </div>
                    <div class="col-xl-7 col-8 text-truncate">
                        <a class="text-decoration-none text-dark" href="{{ route('lessons.show', ['course' => $course, 'lesson' => $lesson]) }}">
                            {{ $lesson->title }}
                        </a>
                    </div>
                    <div class="col-xl-2 col-3 d-none d-md-block text-center">
                        {{ $lesson->created_at->toDateString() }}
                    </div>
                    @if(auth()->check() && auth()->user()->hasPrivilege($course, App\Privilege::find(App\Privilege::EDIT_LESSONS)))
                        <div class="col-1 text-center text-primary">
                            <a href="{{ route('lessons.edit', ['course' => $course, 'lesson' => $lesson]) }}">
                                <i class="fas fa-edit cursor-pointer"></i>
                            </a>
                        </div>
                        <div class="col-1 text-center text-danger">
                            <form action="{{ route('lessons.destroy', ['course' => $course, 'lesson' => $lesson]) }}" method="POST" id="formDeleteLesson">
                                @csrf
                                @method('DELETE')
                                <i class="fas fa-trash cursor-pointer trashDeleteLesson"></i>
                            </form>
                        </div>
                    @endif
                </div>
                <hr class="my-2 mx-4">

                @empty
                    <div class="h3 text-secondary text-center">{{ __('This course does not have any lessons yet') }}</div>
            @endforelse
            {{ $lessons->links() }}
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(() => {
            $('.trashDeleteLesson').click(function () {
                let form = $(this).parent();
                Swal.fire({
                    title: '{{ __('Are you sure ?') }}',
                    text: '{{ __('This lesson and all of its content will be removed. This cannot be undone') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ __('Yes') }}'
                }).then((result) => {
                    if (result.value) {
                        $(form).trigger('submit');
                    }
                });
            });
        });
    </script>
@endsection
