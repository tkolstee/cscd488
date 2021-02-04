@extends('redteam.base')

@section('title', 'Fuzzing Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/fuzzing">
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
            <h4>What is a fuzzing attack?</h4>
            <p>
                Fuzzing, sometimes called "fuzz testing", is when randomized or <br>
                malformed data is sent to an application with the intent of finding <br>
                weaknesses. This tactic can be used by both attackers and targets of attacks <br>
                to find exploits in their system. Often times, fuzzing is automated, sometimes <br>
                using fuzzer software. Data doesn't always have to be random! Some fuzz testing <br>
                involves inserting known "dangerous" values into an application.
            </p>

        @elseif ($step == 2)
            <h4>How does fuzzing work?</h4>
            <p>
                Fuzzing attacks can try a variety of combinations for numbers, characters, <br>
                any sort of input, and even urls and pure binary. <br>
                As mentioned earlier, there are known dangerous values for some data types.
            </p>
            <ul>
                <li>Integers: 0, negative numbers, and very large numbers may have not been expected by programmers</li>
                <li>Chars: Escape characters, uninterpretable characters, and commands (SQL Injection is an example)</li>
                <li>Binary: Typically a random binary sequence</li>
                <li>Null can also be dangerous and unexpected for applications</li>
            </ul>

        @elseif ($step == 3)
            <h4>What other ways can fuzzing be used?</h4>
            <p>
                Fuzzing can be used in a variety of contexts.
            </p>
            <ul>
                <li>Application Fuzzing: Pressing random buttons, filling forms, command line options</li>
                <li>Protocol Fuzzing: Sending forged packets to an application</li>
                <li>File Format Fuzzing: Generating malformed files and opening them with an application</li>
            </ul>

        @elseif ($step == 4)
            <h4>Possible results of a fuzzing attack</h4>
            <p>
                Fuzzing can be a way to find simple bugs in an application. It's simple to understand, <br>
                and can find bugs that humans might not notice as easily. Fuzzing is really for finding <br>
                new vulnerabilities to exploit in a system. For example, if a bug is found involving integers, <br>
                it may be possible to cause integer overflow, and that could lead to a denial-of-service attack.
            </p>

        @endif
        <!-- Reference: https://owasp.org/www-community/Fuzzing  -->
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