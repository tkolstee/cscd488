<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="body_home">
    <div class="wrapper">
            <div class="header"> 
                <div class="headerContainer">
                        <div class="logo">
                            <a href="{{ url('http://localhost:8000/') }}"><img src="images/LOGOcscd488.png" class="img-fluid" alt="Responsive image"/></a>
                        </div><!-- end logo class-->
                        @if (Route::has('login'))
                        <div class=loginAndRegister>
                            <div class="login">
                                @auth
                                <a href="{{ url('/home') }}" class="text-sm text-gray-700 underline">Home</a>
                                 @else
                                <a href="{{ route('login') }}"><input type="button" class="btn btn-primary" value="Login"></a>
                                @if (Route::has('register'))
                                <a href="{{ route('register') }}"><input type="button" class="btn btn-primary" value="Register"></a>
                                @endif
                                @endif
                            </div><!-- end login class-->
                         @endif
                        </div><!--end loginAndRegister class-->          
                </div><!--end headerContainer class-->        
            </div><!--End header class-->

            <div class="navbar">
                <div class="navbarSelection">
                    <ul>
                        <li><a href="{{ url('http://localhost:8000/') }}">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="gamePlay.php">Rules</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div><!--End navbarSelection class-->
            </div><!--End navbar class-->
            
            <div class="container_login_reg">
                <div class="lContainer">
                    <img class="img-fluid" alt="Responsive image" src="images/side_secondPage1.jpg" >
                </div><!--end lContainer -->
                <div class="rContainer">@yield('content') </div><!--end rContainer -->
            </div><!--end container -->
            
            <div class="footer">
                <p>©copyright 2020 - Terrance Cunningham | Robin Deskins | Tony Kolste | Steven Zuelke  - Web Designers</p>
            </div><!--End footer class-->
    </div><!--End Wrapper class-->
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>