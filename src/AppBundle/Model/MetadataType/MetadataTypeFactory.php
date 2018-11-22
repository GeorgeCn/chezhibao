<?php

namespace AppBundle\Model\MetadataType;

class MetadataTypeFactory
{
    private static $types = null;

    public static function getMetadataType($type)
    {
        if (empty(self::$types)) {
            self::$types = [
                'imagelist' => new ImageListType(),
                'video' => new VideoType(),
                'text' => new TextType(),
                'radio' => new RadioType(),
                'date' => new DateType(),
                'checkbox' => new CheckboxType(),
                'textarea' => new TextareaType(),
                'text2' => new Text2Type(),
                'textArray2' => new TextArray2Type(),
            ];
        }
        if (isset(self::$types[$type])) {
            return self::$types[$type];
        }
        return null;
    }
}
