@extends('layouts.base')

@section('basecontent')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div>
<div>
    <a href="/blueteam/home"><button type="submit" style="background-color: blue; border: none; color: white; padding: 15px 32px; text-align: center;">Blue Team</button></a>
    <a href="/redteam/home"><button style="background-color: red; border: none; color: white; padding: 15px 32px; text-align: center;">Red Team</button></a>
</div>
@endsection
