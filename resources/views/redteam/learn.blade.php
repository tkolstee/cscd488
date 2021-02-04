@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
    <h4>Red Team Learning Page.</h4>
    @if (empty($attacks))
    <h2>There are no attacks available to learn about </h2>
    @else
        @foreach ($attacks as $attack)
        <div class="learnHome">
            <ul>
                <li title="{{$attack->help_text ?? "" }}"><a href={{ "/learn/".strtolower($attack->class_name) }}><button>{{$attack->name}}</button></a></li>
            </ul>
        </div>
        @endforeach
        @include('partials.pagination', ['paginator' => $attacks])
    @endif
@endsection
