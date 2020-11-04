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
        <h1>Generic page template</h1>
        <div style="align: center; vertical-align: center; padding: 80px;">
            @yield('basecontent')
        </div>
    </body>
</html>
