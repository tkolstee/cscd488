@extends('minigame.base')

@section('title', 'Syn Flood Attack')

@section('pagecontent')

@if(!empty($attack))
Difficulty: {{$attack->difficulty }}/5 <br>
<form method="POST" action="/attack/synflood">
        @csrf
            <input type="hidden" name="attackName" value="{{ $attack->class_name }}">
            <input type="hidden" name="blue" value="{{ $attack->blueteam }}">
            <input type="hidden" name="red" value="{{ $attack->redteam }}">
            <?php function random($attack){
                $randInt = rand(1,4);
                $val = 0;
                if($randInt > $attack->difficulty - 1){
                    $val = 1;
                }
                return $val;
            }
            ?>
            Select a rate at which to send SYN packets:<br>
            <?php $val = random($attack); ?>
            <input type="radio" name="result1" id="{{ $val }}" value="{{ $val }}">
            <label for="{{ $val }}">60 microseconds</label><br>
            <?php $val = random($attack); ?>
            <input type="radio" name="result1" id="{{ $val }}" value="{{ $val }}">
            <label for="{{ $val }}">120 microseconds</label><br>
            <?php $val = random($attack); ?>
            <input type="radio" name="result1" id="{{ $val }}" value="{{ $val }}">
            <label for="{{ $val }}">175 microseconds</label><br>
            <?php $val = random($attack); ?>
            <input type="radio" name="result1" id="{{ $val }}" value="{{ $val }}">
            <label for="{{ $val }}">200 microseconds</label><br>

            Select amount of SYN packets to send:<br>
            <?php $val = random($attack); ?>
            <input type="radio" name="result2" id="{{ $val }}" value="{{ $val }}">
            <label for="{{ $val }}">25,000 packets</label><br>
            <?php $val = random($attack); ?>
            <input type="radio" name="result2" id="{{ $val }}" value="{{ $val }}">
            <label for="{{ $val }}">50,000 packets</label><br>
            <?php $val = random($attack); ?>
            <input type="radio" name="result2" id="{{ $val }}" value="{{ $val }}">
            <label for="{{ $val }}">75,000 packets</label><br>
            <?php $val = random($attack); ?>
            <input type="radio" name="result2" id="{{ $val }}" value="{{ $val }}">
            <label for="{{ $val }}">100,000 packets</label><br>
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