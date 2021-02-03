@extends('redteam.base')

@section('title', 'Denial of Service Learning Page')

@section('pagecontent')
<div>
    <form method="POST" action="/learn/dos">
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
            <h4>Work In Progress</h4>
            <p></p>

        @elseif ($step == 2)
            <h4></h4>
            <p></p>

        @elseif ($step == 3)
            <h4></h4>
            <p></p>

        @elseif ($step == 4)
            <h4></h4>
            <p></p>

        @endif
        <!-- Reference:   -->
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
</div>
@endsection