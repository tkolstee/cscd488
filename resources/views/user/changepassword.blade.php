@extends('layouts.base')
@section('basecontent')
<div class="settingsPage">
    <br>
    <h4>Change Password:</h4>
    <form method="POST" action="/user/changepassword">
    @csrf
        <label for="oldPassword">Current Password</label>
        <input name="oldPassword" id="oldPassword" type="password" required autocomplete="oldPassword" autofocus><br>
        <label for="newPassword">New Password</label>
        <input name="newPassword" id="newPassword" type="password" required autocomplete="newPassword" autofocus><br>
        <label for="newPasswordConfirm">Confirm Password</label>
        <input name="newPasswordConfirm" id="newPasswordConfirm" type="password" required autocomplete="newPasswordConfirm" autofocus>
        <br>
        <div class="changePass">
            <button type="submit">
                Change Password
            </button>
        </div>
    </form>
    <br><br><br>
</div>
@endsection