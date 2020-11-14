<?php

namespace App\Http\Controllers;
use App\Http\Controllers\SettingController;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller {

    private $sc;

    public function __construct() {
        $this->sc = new SettingController();
    }

    public function page (Request $request) {
        $sc = $this->sc;
        $this->process_form_data($request);
        if ( $sc->get('setup_admin_created') != 'true') {
            return view('setup/account');
        } elseif ( $sc->get('setup_settings_edited') != 'true' ) {
            return view('setup/settings', ['settings' => Setting::all()]);
        } else {
            return redirect('/');
        }
    }

    public function process_form_data($request) {
        $sc = $this->sc;
        $btn = $request->btn;
        switch($btn) {
            case 'edit-setting':
            case 'add-setting':   // Intentional fall-through
                $sc->set($request->key, $request->value);
            break;
            case 'delete-setting':
                $s = $sc->find($request->id);
                if ($s) { $s->delete(); }
            break;
            case 'done-settings':
                $sc->set('setup_settings_edited', 'true');
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
                $sc->set('setup_admin_created', 'true');
            break;
        }

    }



}
