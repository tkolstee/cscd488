@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
    <p>This is the red team learning page. Much Wow.</p>
    @if (empty($attacks))
    <h2>There are no attacks available to learn about </h2>
    @else
        @foreach ($attacks as $attack)
            <a href={{ "/learn/".strtolower($attack->class_name) }}>{{$attack->name}}</a>
            <br>
        @endforeach
    @endif
@endsection
