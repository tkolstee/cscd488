<!---
    This will be the base layout, which is a generic page template
    (header, footer, navigation links, etc.) that don't specifically
    apply to red or blue team activities.

    It should be assumed that the user is logged in, and offer things
    like links to messages or user profiles.

    Red and blue-specific layouts will extend these with things that
    are specific to those types of activities
-->

<html>
    <head>
        <title>@yield('title')</title>
    </head>
    <body bgcolor="#FFF">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="/home/chooseteam" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
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
        <h1>Generic page template</h1>
        <div style="align: center; vertical-align: center; padding: 80px;">
            @if(! empty($error))
            <p>{{ $error }}</p>
            @endif
            @yield('basecontent')
        </div>
    </body>
</html>
