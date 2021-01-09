<!---
    This will be the base layout, which is a generic page template
    (header, footer, navigation links, etc.) that don't specifically
    apply to red or blue team activities.

    It should be assumed that the user is logged in, and offer things
    like links to messages or user profiles.

    Red and blue-specific layouts will extend these with things that
    are specific to those types of activities
-->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<!-- Styles -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

<html>
    <head>
        <title>@yield('title')</title>
    </head>
    <body class="body_home">
            <div class="header"> 
                <div class="headerContainer">
                    <div class="logo">
                        <a href="/home/home"><img src="../images/LOGOcscd488.png" class="img-fluid" alt="Responsive image"/></a>
                    </div><!-- end logo class-->
                    <div class=loginAndRegister>
                        <div class="login">
                            <div class="lLogin" style="font-size:.85vw; ">
                                <p id="loggedinas" >You are logged in as :</p>
                            </div>
                            <div class="rLogin">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="/home" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                     {{ Auth::user()->name }}
                                </a> 
                                 <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </div>                   
                        </div>
                    </div>     
                </div><!--end header-->
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
                
            <div class="container_form" style="align: center; vertical-align: center;">
                @if(! empty($error))
                <p>{{ $error }}</p>
                @endif
                @yield('basecontent')
            </div>
            <div class="footer">
                <p>©copyright 2020 - Terrance Cunningham | Robin Deskins | Tony Kolste | Steven Zuelke  - Web Designers</p>
            </div><!--End footer class-->
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    </body>
    
</html>
