<?php

namespace AppBundle\util;

class SessionApiLimiter
{

    public static function check($session, $apiName, $duration = 1, $limits = 1)
    {
        $now_tick = time();
        $start_tick = $session->get("start_tick_{$apiName}", time());
        $accumulator = $session->get("accumulator_{$apiName}", 0) + 1;

        if ($now_tick - $start_tick > $duration) {
            $start_tick = $now_tick;
            $accumulator = 1;
        }

        $session->set("start_tick_{$apiName}", $start_tick);
        $session->set("accumulator_{$apiName}", $accumulator);


        if ($accumulator <= $limits) {
            return true;
        }
        return false;
    }
}