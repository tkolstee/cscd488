<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="{{ url('/css/app.css') }}" />
        <!--Bootstrap link-->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <title>Janus</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style> 
            /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */
        </style>

        
    </head>
    <body class= "body_home">
        <div class="wrapper">
            <div class="header"> 
                <div class="headerContainer">
                        <div class="logo">
                            <a href="/home/home"><img src="/images/LOGOcscd488.png" class="img-fluid" alt="Responsive image"/></a>
                        </div><!-- end logo class-->
                        @if (Route::has('login'))
                        <div class=loginAndRegister>
                            <div class="login">
                                @auth
                                <div class="login">
                            <div class="lLogin" style="font-size:.85vw; ">
                                <p id="loggedinas" >You are logged in as :</p>
                            </div>
                            <div class="rLogin">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="/home/chooseteam" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                     {{ Auth::user()->name }}
                                </a> 
                                @if (Auth::user()->isAdmin())
                                    <br>
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="/admin/home" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        Admin Home
                                    </a>
                                @endif
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
                        <li><a href="/home/chooseteam">Home</a></li>
                        <li><a href="/home/about">About</a></li>
                        <li><a href="gamePlay.php">Rules</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div><!--End navbarSelection class-->
            </div><!--End navbar class-->
            
            <div class="container_frontpage" >
                <img src="/images/FPslideshow_new1.jpg" />
            </div><!--End container class-->
            
            <div class="footer">
                <p>©copyright 2020 - Terrance Cunningham | Robin Deskins | Tony Kolste | Steven Zuelke  - Web Designers</p>
            </div><!--End footer class-->
        </div><!--End Wrapper class-->
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    </body><!--End body_home class-->
</html>
