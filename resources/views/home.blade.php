@extends('layouts.sidebar')

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-10 col-12 mx-auto">
        <h3>{{ __('Trending courses') }}</h3>
        <div id="carouselTrending" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                @foreach($trendingCourses as $course)
                    <li data-target="#carouselTrending" data-slide-to="{{ $loop->iteration-1 }}" @if($loop->first) class="active" @endif></li>
                @endforeach
            </ol>
            <div class="carousel-inner">
                @foreach($trendingCourses as $course)
                    <div class="carousel-item @if($loop->first) active @endif">
                        <img class="img-fluid rounded" src="{{ asset('storage/'.$course->image) }}" alt="{{ $course->title }}">
                        <div class="carousel-caption rounded" style="background-color: #aaaaaaaa;">
                            <a class="text-decoration-none" href="{{ route('courses.show', $course) }}"><h4 class="text-light text-truncate">{{ $course->title }}</h4></a>
                        </div>
                    </div>
                @endforeach
            </div>
            <a class="carousel-control-prev" href="#carouselTrending" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselTrending" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
        <h3 class="mt-3">{{ __('Latest created courses') }}</h3>
        <div class="row">
            @foreach($newCourses as $course)
                <div class="col-lg-3 col-6">
                    <a class="text-decoration-none text-secondary" href="{{ route('courses.show', $course) }}">
                        <img src="{{ asset('storage/'.$course->image) }}" class="img-fluid rounded">
                        <p class="text-center">{{ $course->title }}</p>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
