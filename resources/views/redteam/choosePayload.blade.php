@extends('redteam.base')

@section('title', 'Choose Payload')

@section('pagecontent')
    <form method="POST" action="/redteam/savePayload">
        @csrf
        @foreach($payloads as $payload)
            <input type="radio" name="result" id="{{$payload->class_name}}" value="{{$payload->class_name}}">
            <label class="chooseTeamRadioButtons" for="{{$payload->class_name}}">{{$payload->name}}</label>
        @endforeach
        <input type="hidden" name="attID" value="{{ $attack->id }}">
        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    Choose Payload
                </button>
            </div>
        </div>
    </form>
@endsection
