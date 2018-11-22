<?php

namespace AppBundle\Model\MetadataType;

class TextArray2Type implements MetadataTypeInterface
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
        $newValue = [];

        if (isset($meta->options['choices'])) {
            $choices = $meta->options['choices'];

            foreach ($value as $v) {
                if (isset($choices[$v])) {
                    $newValue[] = $choices[$v];
                }
            }
        }

        if ($newValue) {
            $result = $this->arrayLineFeed($newValue);
        } else {
            $result = $this->arrayLineFeed($value);
        }

        if (!$result) {
            return;
        }

        return <<<EOD
        <tr>
            <td class="w1">$label</td>
            <td class="w2">$result</td>
        </tr>
EOD;
    }

    /**
     * 每8个换行
     */
    public function arrayLineFeed($value = array())
    {
        if (!$value) {
            return null;
        }

        foreach ($value as $v) {
            $newValue[] = '<b>'.$v.'</b>';
        }

        return implode('', $newValue);
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
