@extends('courses.header')

@section('body')
<div class="row">
    <div class="col-12">
        {!! $course->description !!}
    </div>
</div>
@endsection
