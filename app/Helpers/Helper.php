<?php

use App\Models\Asset;
use App\Models\Team;

if (!function_exists('attack_broadcastable')) {
    /**
     * Returns true if an attack is allowed to be broadcasted.
     * Examples of restrictions could be if its already in the news
     * Or if it is too old.
     * */
    function attack_broadcastable($attack) {
        return (!$attack->isNews && $attack->created_at->diffInDays() <= 3);
    }
}

if (!function_exists('generateRandomString')) {
    /**
     * Generates a 'random' alphanumeric string of length 10
     * Intended for use in sql injection minigame to generate fake passwords in plain text.
     */
    function generateRandomString($length = 10) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charsLength = strlen($chars);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $chars[rand(0, $charsLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('isValidTargetedAsset')) {
    /**
     * Checks if an asset with Targeted tag can be applied the attack passed in
     */
    function isValidTargetedAsset($inv, $attack) {
        $asset = Asset::get($inv->asset_name);
        $redteam = Team::find($attack->redteam);
        $blueteam = Team::find($attack->blueteam);
        
        if($asset->blue == 1)
            $expectedInfo = $redteam->name;
        else   
            $expectedInfo = $blueteam->name;

        return ($expectedInfo == $inv->info);
    }
}
