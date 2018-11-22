<?php

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('json_decode', array($this, 'jsonDecodeFilter')),
            new \Twig_SimpleFilter('cast_to_array', array($this, 'objectFilter')),
        );
    }

    public function jsonDecodeFilter($string)
    {
        return json_decode($string);
    }

    public function objectFilter($stdClassObject)
    {
        return (array)$stdClassObject;
    }

    public function getName()
    {
        return 'app_extension';
    }
}