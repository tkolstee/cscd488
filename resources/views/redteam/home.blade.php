@extends('redteam.base')

@section('title', 'Red Team Home')

@section('pagecontent')
    @if (Auth::user()->redteam == "")
        <a href="/redteam/create"><button class="btn btn-primary">Create Red Team</button></a>
     @else
     @if (!empty($attMsg))
     <div class="popup">
        <span class="popuptextattack" id="attackPopup" onclick="displayPopup()">{{$attMsg}}</span>
    </div>
    <script>
        // When the user clicks on <div>, open the popup
        function displayPopup() {
            var popup = document.getElementById("attackPopup");
            popup.classList.toggle("show");
        }
        displayPopup();
    </script>
     @endif
     <h4>Red Team Home Page</h4>
    <p>You are now on the red team home page. Much Wow.</p>
    <a href="/redteam/startattack"><button class="btn btn-primary" >Start Attack</button></a>
    @endif
@endsection
