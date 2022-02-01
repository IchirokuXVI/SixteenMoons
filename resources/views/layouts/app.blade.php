<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @yield('head')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm position-sticky sticky-top" id="mainNavbar">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('logo.png') }}">
                    {{ config('app.name') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('courses.index') }}">{{ __('Courses') }}</a>
                        </li>
                    </ul>

                    <!-- Middle -->
                    <ul class="navbar-nav d-inline w-50 mr-auto ml-auto">
                        <form action="{{ route('courses.index') }}" method="GET">
                            <li class="nav-item input-group">
                                <input type="text" name="searchTerm" id="searchCourse" class="form-control border-right-0" placeholder="{{ __('Search courses') }}...">
                                <span class="input-group-append alert-success border-left-0 rounded-right">
                                    <button type="submit" class="btn btn-block">{{ __('Search') }}</button>
                                </span>
                            </li>
                        </form>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        @if(auth()->check())
                            <li class="nav-item dropdown mr-lg-5">
                                {{-- Removed dropdown-toggle class as it doesn't seem to do anything but add the dropdown arrow icon --}}
                                <a id="navbarDropdownNotifications" class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-bell" id="notificationsBell"></i>
                                    @if(auth()->user()->unseenNotifications > 0)
                                        <span class="text-secondary" id="unseenNotifications">{{ auth()->user()->unseenNotifications }}</span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" id="notifications" aria-labelledby="navbarDropdownNotifications">
                                    <div class="container-fluid">
                                        @forelse(auth()->user()->notContentNotifications() as $notification)
                                                <div class="row notification" data-id="{{ $notification->id }}">
                                                    <div class="col-1 my-auto">
                                                        @if($notification->type->is(App\NotificationType::where('name', 'newRole')->first()))
                                                            <i class="fas fa-user-plus text-primary"></i>
                                                        @elseif($notification->type->is(App\NotificationType::where('name', 'removedRole')->first()))
                                                            <i class="fas fa-user-times text-danger"></i>
                                                        @elseif($notification->type->is(App\NotificationType::where('name', 'removedCourse')->first()))
                                                            <i class="fas fa-times text-danger"></i>
                                                        @endif
                                                    </div>
                                                    <div class="col-10 btn-hover rounded cursor-pointer" onclick="location.href='{{ asset($notification->url) }}'">
                                                        {{ App::isLocale('en') ? $notification->translations->message_en : $notification->translations->message_es }} - <span class="text-secondary">{{ $notification->created_at }}</span>
                                                    </div>
                                                    <div class="col-1 my-auto">
                                                        <i class="fas fa-times text-danger deleteNotification"></i>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-secondary text-center">{{ __("You don't have notifications right now") }}</div>
                                        @endforelse
                                    </div>
                                </div>
                            </li>
                        @endif
                        <li class="nav-item dropdown mr-lg-5">
                            <a id="navbarDropdownLang" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <span class="caret">
                                    @if (App::isLocale('es'))
                                        {{ __('Spanish') }}
                                    @else
                                        {{ __('English') }}
                                    @endif
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownLang">
                                <a class="dropdown-item" href="{{ route('changeLocale', 'en') }}">
                                    {{ __('English') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('changeLocale', 'es') }}">
                                    {{ __('Spanish') }}
                                </a>
                            </div>
                        </li>
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <span class="caret">
                                        {{ auth()->user()->username }}
                                    </span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('users.show', auth()->user()) }}">
                                        {{ __('Profile') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container-fluid">
            <div class="row">
                @yield('sidebar')
                <div class="col">
                    <main class="py-4">
                        @yield('content')
                    </main>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @if(Session::has('swal'))
        <script>
            // If there is something with key swal in the session then display a swal with its content
            // When a controller redirects you with an error or something like that it usually returns a swal to notify the user
            Swal.fire({
                title: "{{ Session::get('swal.title', 'Error') }}",
                html: "{{ Session::get('swal.text') }}"
            });
        </script>
    @endif
    @yield('scripts')
</body>
</html>
