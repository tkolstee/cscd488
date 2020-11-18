<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public static function get($key) {
        $s = Setting::all()->where('key', '=', $key)->first();
        if ( $s == null ) { return null; } else { return $s->value; }
    }

    public static function set($key, $value) {
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

    public static function id_to_key($id) {
        $s = Setting::find($id);
        return $s ? $s->key : null;
    }

    public static function key_to_id($key) {
        $s = Setting::all()->where('key', '=', $key)->first();
        return $s ? $s->id : null;
    }


}
