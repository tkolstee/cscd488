@if (Auth::user()->isAdmin())
    <p>Admins are not permitted to participate in a game.</p>
@else
    <p>Non-admins are not permitted to view this page.</p>
@endif
