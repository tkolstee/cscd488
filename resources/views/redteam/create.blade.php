@extends('redteam.base')

@section('title', 'Red Team Create')

@section('pagecontent')

<h3>Create New Red Team</h3>
<form method="POST" action="/redteam/create">
    @csrf

    <div class="form-group row">
        <label for="name" class="blueNameLabel">{{ __('Team Name') }}</label>

        <div class="col-md-6">
            <input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>

    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                {{ __('Create Team') }}
            </button>

            
        </div>
    </div>
</form>

@endsection
