@extends('blueteam.base')

@section('title', 'Blue Team Create')

@section('pagecontent')

    <h2>Join A Team</h2>
    <form method="POST" action="{{ route('blueteam/join') }}">
    @csrf
    @foreach ($blueteams as $blueteam)

    <input type="radio" name="result" value="{{ $blueteam->name }}"><br>
    
    @endforeach

    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                Join Team
            </button>
        </div>
    </div>
</form>
    
@endsection
