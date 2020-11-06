@extends('blueteam.base')

@section('title', 'Blue Team Create')

@section('pagecontent')

<h2>Create New Blue Team</h2>
<form method="POST" action="/blueteam/create">
    @csrf

    <div class="form-group row">
        <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Team Name') }}</label>

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
