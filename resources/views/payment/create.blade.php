@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 text-center h3">
                {{ __('Support options for the course') }} "{{ $course->title }}"
            </div>
            @foreach($course->customRoles()->where('binded_to', App\BindRoles::where('name', 'supporters')->first()->id)->orderBy('price', 'ASC')->get() as $role)
                <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                    <div class="card">
                        <div class="card-header">{{ $role->name }}</div>
                        <div class="card-body">
                            <div class="col-10">
                                {{ $role->description }}
                            </div>
                            <div class="col-12">{{ __('Privileges') }}:</div>
                            <ul class="text-secondary">
{{--                                <li>{{ __('Access to exclusive lessons') }}</li>--}}
                                <li>{{ $role->name }} {{ __('tag') }}</li>
                                @foreach($role->privileges->where('type', 'users_limitations') as $privilege)
                                    <li>
                                        @if(App::isLocale('es'))
                                            {{ $privilege->translations->description_es }}
                                        @else
                                            {{ $privilege->translations->description_en }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                                @if (!auth()->user()->customRoles->contains($role->id))
                                    @if ($course->createdBy->is(auth()->user()))
                                        <div class="col-12 text-center">
                                            <span class="text-secondary">{{ __("You can't buy a rank for your own course") }}</span>
                                        </div>
                                        @else
                                            <div class="col-xl-5 col-lg-8 col-12 mx-auto">
                                                <form method="POST" action="{{ route('payment.store', ['course' => $course, 'customRole' => $role]) }}">
                                                    @csrf
                                                    <button class="btn btn-block alert-success">{{ __('Pay') }} {{ $role->price }}€</button>
                                                </form>
                                            </div>
                                    @endif
                                    @else
                                        <div class="col-12 text-center">
                                            <span class="text-secondary">{{ __('You already have this role') }}</span>
                                        </div>
                                @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-12">
                @if(Session::has('error'))
                    <div class="alert alert-danger">
                        {{ Session::get('error') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
