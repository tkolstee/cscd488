<?php

namespace App\Http\Controllers;
use App\Models\Setting;
use App\Models\User;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller {

    public function page (Request $request) {
        $this->process_form_data($request);
        if ( Setting::get('setup_admin_created') != 'true') {
            return view('setup/account');
        } elseif ( Setting::get('setup_settings_edited') != 'true' ) {
            return view('setup/settings', ['settings' => Setting::all()]);
        } else {
            return redirect('/admin/home');
        }
    }

    public function prefill_settings() {
        $g = new Game();
        $g->turn = 0;
        $g->save();

        Setting::set('setup_admin_created', 'true');
        Setting::set('turn_end_time',       '01:00');

    }

    public function process_form_data($request) {
        $btn = $request->btn;
        switch($btn) {
            case 'edit-setting':
            case 'add-setting':   // Intentional fall-through
                Setting::set($request->key, $request->value);
            break;
            case 'delete-setting':
                $s = Setting::find($request->id);
                if ($s) { $s->delete(); }
            break;
            case 'done-settings':
                Setting::set('setup_settings_edited', 'true');
            case 'create-admin':
                $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'username' => ['required', 'string', 'max:255', 'unique:users'],
                    'email' => ['required', 'string', 'email', 'max:255'],
                    'password' => ['required', 'string', 'min:8', 'confirmed'],
                ]);
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->username = $request->username;
                $user->password = Hash::make($request->password);
                $user->is_admin = 1;
                $user->save();
                $this->prefill_settings();
            break;
        }

    }



}
