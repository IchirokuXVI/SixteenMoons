@extends('courses.header')

@section('body')
    <div class="h4">
        {{ $course->followedBy->count() }} {{ __('Followers') }}
    </div>
    <div class="row" id="followers">
        @foreach($followers as $user)
            <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-6">
                <a class="text-secondary text-decoration-none" href="{{ route('users.show', $user) }}">
                    <img src="{{ asset('storage/'.$user->avatar) }}" class="img-fluid rounded-circle">
                    <p class="text-muted text-center text-truncate" title="{{ $user->username }}#{{ $user->suffixId }}">{{ $user->username }}#{{ $user->suffixId }}</p>
                </a>
            </div>
        @endforeach
    </div>
    @if($followers->lastPage() != 1)
        <div class="col-12 text-center">
            <button type="button" class="btn alert-success" data-page="2" id="showMore">{{ __('Show more') }}...</button>
        </div>
    @endif
@endsection
@section('scripts')
    <script>
        var indexFollowersJson = '{{ route('courses.indexFollowersJson', $course) }}';
        var usersShow = '{{ route('users.show', ':id') }}';
    </script>
    <script>
        // On DOM loaded
        $(() => {
            $('#showMore').click(function() {
                $('#showMore').prop('disabled', true);
                $.ajax({
                    url: indexFollowersJson,
                    dataType: 'json',
                    data: {
                        page: $(this).attr('data-page')
                    },
                    success: function(data) {
                        $('#showMore').prop('disabled', false);
                        if (data.followers.last_page == $('#showMore').attr('data-page')) {
                            $('#showMore').hide();
                        } else {
                            //Increment attribute by 1 to see the next page
                            $('#showMore').attr('data-page', parseInt($('#showMore').attr('data-page'))+1);
                        }
                        for (let user of data.followers.data) {
                            $('#followers').append(`
                                <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-6">
                                    <a class="text-secondary text-decoration-none" href="${usersShow.replace(':id', user.id)}">
                                        <img src="{{ asset('storage') }}/${user.avatar}" class="img-fluid rounded-circle">
                                        <p class="text-muted text-center text-truncate" title="${user.username}#${user.suffixId}">${user.username}#${user.suffixId}</p>
                                    </a>
                                </div>
                            `)
                        }
                    }
                });
            });
        });
    </script>
@endsection
