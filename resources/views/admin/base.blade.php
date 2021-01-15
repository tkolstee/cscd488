<html>
    <head>
        <title>@yield('title')</title>
    </head>
    <body bgcolor="#FFF">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="/home/chooseteam" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            User Home
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
        <h1 style="color: red;">ADMIN PAGE</h1>
        <div style="align: center; vertical-align: center; padding: 80px;">
            @if(! empty($error))
            <p>{{ $error }}</p>
            @endif
            @yield('content')
        </div>
    </body>
</html>
