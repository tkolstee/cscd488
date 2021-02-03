@extends('redteam.base')

@section('title', 'Drive-by Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/driveby">
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
            <h4>What is a Drive-By attack?</h4>
            <p>A Drive-By attack is when an attacker compromises a website, so when you visit it, you are forced
                to download malware. Generally the type of malware forcibly downloaded is an exploit kit. Normally
                designed to search for vulnerabilities in your computer's security, an exploit kit attacks and takes
                control of your system. 
            </p>

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