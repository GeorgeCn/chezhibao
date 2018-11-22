<?php

namespace AppBundle\Model\MetadataType;

class VideoType implements MetadataTypeInterface
{
    public function makeValue($input, $meta = null)
    {
        // input是image url的数组，去掉可能空的内容
        return array_filter($input);
    }

    public function makeDom($meta, $reportdata = [])
    {
        return ;
    }

    public function diffValue($oldVal, $newVal)
    {
        return [
            "diff" => false,
            "old" => "",
            "new" => ""
        ];
    }
}
