@extends('redteam.base')

@section('title', 'Choose Payload')

@section('pagecontent')
    <p>{{$attMsg}}<p>
    <form method="POST" action="/redteam/executePayload">
        @csrf
        @foreach($attack->payloads as $payload)
            <input type="radio" name="result" id="{{$payload}}" value="{{$payload}}">
            <label for="{{$payload}}">{{$payload}}</label>
        @endforeach
        <input type="hidden" name="attID" value="{{ $attack->id }}">
        <input type="hidden" name="attMsg" value="{{ $attMsg }}">
        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    Choose Payload
                </button>
            </div>
        </div>
    </form>
@endsection
