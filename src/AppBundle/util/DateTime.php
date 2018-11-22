<?php

namespace AppBundle\util;

class DateTime
{
    public function calculateDiffTime($start, $end)
    {
        $interval = $start->diff($end);
        if (0 === $interval->invert) {
            //算出相差的分钟数
            $minutes = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;

            return $minutes;
        } else {
            return 0;
        }
    }
}