<?php

namespace App\Http\Controllers;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller {

    public function get($key) {
        $s = Setting::all()->where('key', '=', $key)->first();
        if ( $s == null ) { return null; } else { return $s->value; }
    }

    public function set($key, $value) {
        $s = Setting::all()->where('key', '=', $key)->first();
        if ($s == null) {
            $s = new Setting();
            $s->key = $key;
            $s->value = $value;
            $s->save();
        } else {
            $s->value = $value;
            $s->update();
        }
    }

    public function id_to_key($id) {
        $s = Setting::find($id);
        return $s ? $s->key : null;
    }

    public function key_to_id($key) {
        $s = Setting::all()->where('key', '=', $key)->first();
        return $s ? $s->id : null;
    }

}
