<?php

namespace App\Http\Controllers;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\SettingController;
use App\Models\User;


class AdminController extends Controller {

    public function page(Request $request, $page) {
        switch ($page) {

        }
        abort(404);
    }

}

