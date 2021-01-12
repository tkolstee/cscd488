@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
    @if (!empty($attack))
        <h2>{{ $redteam->name }} attacking {{ $blueteam->name }} with {{ $attack->name }}</h2>
        <h3>Press a correct button:</h3>
        Each button has a {{ 5 - $attack->difficulty }}/4 chance.
        <form method="POST" action="/redteam/minigamecomplete">
        @csrf
            <input type="hidden" name="attackName" value="{{ $attack->class_name }}">
            <input type="hidden" name="blue" value="{{ $attack->blueteam }}">
            <input type="hidden" name="red" value="{{ $attack->redteam }}">
            @for ($i = 0; $i < 10; $i++)
                <?php 
                    $randInt = rand(1,4);
                    $val = 0;
                    if($randInt > $attack->difficulty - 1){
                        $val = 1;
                    }
                ?>
                <input type="radio" name="result" id="{{ $val }}" value="{{ $val }}">
                <label for="{{ $val }}">{{ $i + 1 }}</label>
                <br>
            @endfor
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        Submit Choice
                    </button>
                </div>
            </div>
        </form>
    @endif
@endsection
