<?php

namespace AppBundle\util;

/**
 * http://type.so/php/php-wildcard.html
 */
class Str
{
    public function is($pattern, $value)
    {
        if ($pattern == $value) return true;

        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern) . '\z';
        return (bool) preg_match('#^' . $pattern . '#', $value);
    }
}