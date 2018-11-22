<?php

namespace AppBundle\Model\MetadataType;

class Text2Type implements MetadataTypeInterface
{
    public function makeValue($input, $meta = null)
    {
        return $input;
    }

    public function makeDom($meta, $reportdata = [])
    {
        if (!isset($reportdata[$meta->key])) {
            return;
        }

        $label = $meta->display;
        $value = $reportdata[$meta->key];
        $value = explode("_",$value);
        $result = "<b>".$value[0]."</b>";

        if(isset($value[1])){
            $result = $result.'<br/><span class="ft-gray1">'.$value[1].'</span>';
        }

        return <<<EOD
        <tr>
            <td class="w1">$label</td>
            <td class="w2">$result</td>
        </tr>
EOD;
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
