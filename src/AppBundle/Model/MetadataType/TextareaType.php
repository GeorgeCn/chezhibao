<?php

namespace AppBundle\Model\MetadataType;

class TextareaType implements MetadataTypeInterface
{
    public function makeValue($input, $meta = null)
    {
        return ['value' => $input];
    }

    public function makeDom($meta, $reportdata = [])
    {
        $placeholder = !empty($meta->options['placeholder']) ? $meta->options['placeholder'] : null;
        $value = !empty($reportdata[$meta->key]) ? $reportdata[$meta->key]['value'] : null;
        return <<<EOT
<dt><span>$meta->display</span></dt>
<dd class="textareaType">
    <textarea name="form[$meta->key]" placeholder="{$placeholder}">{$value}</textarea>
</dd>
EOT;
    }

    public function diffValue($oldVal, $newVal)
    {
        $diff = $oldVal["value"] !== $newVal;
        return [
            "diff" => $diff,
            "old" => $oldVal,
            "new" => ['value' => $newVal],
        ];
    }
}
