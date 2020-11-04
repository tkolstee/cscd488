@extends('setup.base')
@section('title', 'Edit Settings')
@section('content')

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p style="color: red;">{{$error}}</p>
            @endforeach
        </div>
    @endif

    <table>
        <thead><tr><td>Key</td><td>Value</td><td>&nbsp;</td></tr></thead>
        <tbody>
            @foreach($settings as $setting)
                <form method="post">
                    @csrf
                    <input type="hidden" name="id" value="{{ $setting->id }}">
                    <tr>
                        <td>{{ $setting->key }}<input type="hidden" name="key" value="{{$setting->key}}"></td>
                        <td><input type="text" name="value" value="{{ $setting->value }}"></td>
                        <td>
                            <button type="submit" name="btn" value="edit-setting">Save</button>
                            <button type="submit" name="btn" value="delete-setting">Delete</button>
                        </td>
                    </tr>
                </form>
            @endforeach
            <tr>
                <form method="post">
                    @csrf
                    <td><input type="text" name="key" placeholder="Key"></td>
                    <td><input type="text" name="value" placeholder="Value"></td>
                    <td><button type="submit" name="btn" value="add-setting">Add</button></td>
                </form>
            </tr>
            <tr><td colspan=3><form method="post">
                @csrf
                <button type="submit" name="btn" value="done-settings">Done</button>
            </form></td></tr>
        </tbody>
    </table>
@endsection
