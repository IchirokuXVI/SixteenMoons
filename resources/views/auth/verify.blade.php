@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address') }}.
                        </div>
                    @endif

                    <div>
                        {{ __('Before proceeding, please check your email for a verification link') }}.
                        {{ __('If you did not receive the email') }},
                        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                        </form>
                    </div>
                    {{ __('Please do not leave this page until you confirm the email or you will be automatically logged out and you must be logged in to verify your email') }}.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
