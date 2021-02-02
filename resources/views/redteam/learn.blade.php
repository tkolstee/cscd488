@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
    <h4>Red Team Learning Page.</h4>
    @if (empty($attacks))
    <h2>There are no attacks available to learn about </h2>
    @else
        @foreach ($attacks as $attack)
        <div class="sqlInjectionsList">
            <ul>
                <li class="sqlInjectionsHover"><a class="sqlInjections" href={{ "/learn/".strtolower($attack->class_name) }}>{{$attack->name}}</a></li>
                <br>
            </ul>
        </div>
        @endforeach
        @include('partials.pagination', ['paginator' => $attacks])
    @endif
@endsection
