@extends('redteam.base')

@section('title', 'Malvertise Learning Page')

@section('pagecontent')
    <form method="POST" action="/learn/malvertise">
        @csrf
        <input type="hidden" name="step" value="{{ $step }}">
    @if ($step != 1)
        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary" name="stepChange" value="-1">
                    Previous
                </button>
            </div>
        </div>
    @endif
    @if ($step == 1)
        <h2>What is a Malvertise attack?</h2>
        <p></p>
    @elseif ($step == 2)
        <h2>How to test for SQL Injection?</h2>
        <p></p>
    @elseif ($step == 3)
        <h2>How to manipulate queries with SQL Injection?</h2>
        <p></p>
    @elseif ($step == 4)
        <h2>Further Applications</h2>
        <p></p>
    @endif
    @if ($step != 4)
        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary" name="stepChange" value="1">
                    Next
                </button>
            </div>
        </div>
    @endif
    </form>
@endsection
