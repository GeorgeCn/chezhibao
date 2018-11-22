<?php

namespace AppBundle\Model\MetadataType;

class CheckboxType implements MetadataTypeInterface
{
    public function makeValue($input, $meta = null)
    {
        $output['value'] = !empty($input['value']) ? $input['value'] : null;
        if (array_key_exists('required', $meta->options) && $meta->options['required'] == false) {
            $output['options']['non-required'] = true;
        }
        return $output;
    }

    public function makeDom($meta, $reportdata = [])
    {
        $checkbox = '<ul class="list-inline">';

        foreach ($meta->hint as $h) {
            $checked = !empty($reportdata[$meta->key]['value']) ? array_search($h, $reportdata[$meta->key]['value']) : false;
            $checked = $checked !== false ? 'checked' : null;
            $tmp[] = '<li title="'.$h.'"><label><input type="checkbox" autocomplete="off" name="form['.$meta->key.'][value][]" value="'.$h.'" '.$checked.' /><span>'.$h.'</span></label></li>';
        }
        $checkbox .= implode('', $tmp).'</ul>';
        return <<<EOT
<dt><span>$meta->display</span></dt>
<dd class="checkType">
    $checkbox
</dd>
EOT;
    }

    public function diffValue($oldVal, $newVal, $meta = null)
    {
        $output['value'] = !empty($newVal['value']) ? $newVal['value'] : null;
        if (array_key_exists('required', $meta->options) && $meta->options['required'] == false) {
            $output['options']['non-required'] = true;
        }
        $diff = $oldVal["value"] !== $output["value"];

        return [
            "diff" => $diff,
            "old" => $oldVal,
            "new" => $output,
        ];
    }
}
