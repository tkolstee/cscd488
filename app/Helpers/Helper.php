<?php

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
