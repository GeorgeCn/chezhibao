<?php

namespace AppBundle\Model\MetadataType;

class DateType implements MetadataTypeInterface
{
    public function makeValue($input, $meta = null)
    {
        $output = [];
        $output['value'] = $input['value'];
        if (!empty($input['append']['radio'])) {
            if (0 != array_search($input['append']['radio'], $meta->options)) {
                $output['value'] = $input['append']['radio'];
            }
        }
        return $output;
    }

    public function makeDom($meta, $reportdata = [])
    {
        $value = null;
        if (!empty($reportdata[$meta->key]['value']) && false === array_search($reportdata[$meta->key]['value'], $meta->options)) {
            $value = $reportdata[$meta->key]['value'];
        }
        $date = <<<EOT
<div class="input-group date">
    <input type="text" autocomplete="off" name="form[$meta->key][value]" value="{$value}" class="form-control">
    <span class="input-group-addon">
        <i class="glyphicon glyphicon-th"></i>
    </span>
</div>
EOT;
        $tmp = [];
        foreach ($meta->options as $k => $o) {
            $checked = null;
            if (!empty($reportdata[$meta->key]) && $reportdata[$meta->key]['value'] == $o || $k == 0) {
                $checked = 'checked';
            }
            $tmp[] = '<label><input type="radio" autocomplete="off" name="form['.$meta->key.'][append][radio]" value="'.$o.'" '.$checked.' />'.$o.'</label>';
        }
        $date .= implode('', $tmp);
        return <<<EOT
<dt><span>$meta->display</span></dt>
<dd class="textType">
    $date
</dd>
EOT;
    }

    public function diffValue($oldVal, $newVal, $meta = null)
    {
        $output = [];
        $output['value'] = $newVal['value'];
        if (!empty($newVal['append']['radio'])) {
            if (0 != array_search($newVal['append']['radio'], $meta->options)) {
                $output['value'] = $newVal['append']['radio'];
            }
        }
        $diff = $oldVal["value"] !== $output["value"];

        return [
            "diff" => $diff,
            "old" => $oldVal,
            "new" => $output,
        ];
    }
}
