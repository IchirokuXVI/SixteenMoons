@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Payment success') }}</div>

                    <div class="card-body">
                        <p>
                            {{ __('The payment was successful') }}
                        </p>
                        <p>
                            {{ __('The role have been granted to your user') }},
                        </p>
                        <p>
                            {{ __('Enjoy !') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
