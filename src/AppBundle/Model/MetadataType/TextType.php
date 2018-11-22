<?php

namespace AppBundle\Model\MetadataType;

class TextType implements MetadataTypeInterface
{
    public function makeValue($input, $meta = null)
    {
        return ['value' => $input];
    }

    public function makeDom($meta, $reportdata = [])
    {
        $readonly = array_key_exists('readonly', $meta->options) ? 'readonly' : null;
        $type = array_key_exists('html5input', $meta->options) ? $meta->options['html5input'] : 'text';
        $class = array_key_exists('class', $meta->options) ? $meta->options['class'] : null;
        $placeholder = array_key_exists('placeholder', $meta->options) ? $meta->options['placeholder'] : null;
        $value = $unit = null;
        if (!empty($reportdata[$meta->key])) {
            $value = $reportdata[$meta->key]['value'];
        } elseif (array_key_exists('defaultValue', $meta->options)) {
            $value = $meta->options['defaultValue'];
        }
        if (array_key_exists('unit', $meta->options)) {
            $value = str_replace($meta->options['unit'], '', $value);
            $unit = $meta->options['unit'];
        }
        return <<<EOT
<dt><span>$meta->display</span></dt>
<dd class="textType">
    <input type="{$type}" autocomplete="off" name="form[$meta->key]" value="{$value}{$unit}" class="form-control {$class}" placeholder="$placeholder" $readonly />
</dd>
EOT;
    }

    public function diffValue($oldVal, $newVal, $meta = null)
    {
        $diff = $oldVal["value"] !== $newVal;
        return [
            "diff" => $diff,
            "old" => $oldVal,
            "new" => ['value' => $newVal],
        ];
    }
}
