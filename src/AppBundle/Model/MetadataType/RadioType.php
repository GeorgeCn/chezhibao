<?php

namespace AppBundle\Model\MetadataType;

class RadioType implements MetadataTypeInterface
{
    public function makeValue($input, $meta = null)
    {
        $output = [];
        $output['value'] = @$input['value'];
        if (!empty($input['append'])) {
            if (!empty($input['append']['text'])) {
                if (end($meta->hint) == $input['value']) {
                    $output['value'] = $input['append']['text'];
                }
            }
            if (!empty($input['append']['textarea'])) {
                $title = !empty($meta->options['appendTextarea']['title']) ? $meta->options['appendTextarea']['title'] : null;
                $output['options']['textarea_title'] = $title;
                $output['options']['textarea'] = $input['append']['textarea'];
            }
        }
        return $output;
    }

    public function makeDom($meta, $reportdata = [])
    {
        if (isset($meta->options['numRows'])) {
            $numRows = $meta->options['numRows'];
            $radio = '<ul class="list-inline"'. 'data-numrows="'.$numRows.'">';
        } else {
            $radio = '<ul class="list-inline">';
        }

        $hasChecked = false;


        //退回理由页面特殊处理
        $backReason = isset($meta->options['sample']);
        $itemClass = $backReason ? 'radio' : '';
        $inputTextClass = $backReason ? 'form-control' : '';

        foreach ($meta->hint as $k => $h) {
            if (!empty($reportdata[$meta->key]) && $h == $reportdata[$meta->key]['value']) {
                $checked = $hasChecked = 'checked';
            } elseif (!$hasChecked && empty($meta->hint[$k+1]) && !empty($reportdata[$meta->key])) {
                $checked = array_key_exists('appendText', $meta->options)?'checked':null;
            } else {
                $checked = null;
            }
            if(isset($meta->options['remark'])) {
                $tmp[] = '<li class="component-li" title="'.$meta->options['remark'][$k].'"><label><input type="radio" class="component" autocomplete="off" name="form['.$meta->key.'][value]" value="'.$h.'" '.$checked.' data-group="'.$meta->options['groups'].'" data-ratio="'.$meta->options['ratio'][$k].'" /><span>'.$h.'</span></label></li>';
            } else {
                $tmp[] = '<li class="'.$itemClass.'" title="'.$h.'"><label><input type="radio" autocomplete="off" name="form['.$meta->key.'][value]" value="'.$h.'" '.$checked.' /><span>'.$h.'</span></label></li>';
            }
        }
        $radio .= implode('', $tmp);
        if (array_key_exists('appendText', $meta->options)) {
            $value = !$hasChecked && !empty($reportdata[$meta->key]) ? $reportdata[$meta->key]['value'] : null;
            $unit = null;
            if (array_key_exists('unit', $meta->options)) {
                $value = str_replace($meta->options['unit'], '', $value);
                $unit = $meta->options['unit'];
            }
            $radio .= '<li class="attached"><input class="form-control '.$inputTextClass.'" type="text" name="form['.$meta->key.'][append][text]" value="'.$value.$unit.'" placeholder="'.$meta->options['appendText'].'" /></li>';
        }
        $radio .= '</ul>';
        if (array_key_exists('appendTextarea', $meta->options)) {
            $appendTextarea = $meta->options['appendTextarea'];
            $placeholder = !empty($appendTextarea['placeholder']) ? $appendTextarea['placeholder'] : null;
            $title = !empty($appendTextarea['title']) ? $appendTextarea['title'] : null;
            $textarea = !empty($reportdata[$meta->key]['options']) ? $reportdata[$meta->key]['options']['textarea'] : null;
            $radio .= '<div>'.$title.'</div><textarea name="form['.$meta->key.'][append][textarea]" placeholder="'.$placeholder.'">'.$textarea.'</textarea>';
        }
        // 有些地方需要显示样本图片如（退回页面），把图片url传过去
        if (isset($meta->options['sample'])) {
            $imgUrl = $meta->options['sample'];
        } else {
            $imgUrl = null;
        }

        if ($imgUrl) {
            if(strpos($imgUrl, '.mp4')) {
                return <<<EOT
<dt>$meta->display</dt>
<dd class="thumbnail-box">
    <video src="" alt="$meta->display" data-original="{$imgUrl}"></video>
</dd>
<dd class="radioType">
    $radio
</dd>
EOT;
} else {
                return <<<EOT
<dt>$meta->display</dt>
<dd class="thumbnail-box">
    <img src="" alt="$meta->display" data-original="{$imgUrl}">
</dd>
<dd class="radioType">
    $radio
</dd>
EOT;
}
        }

        return <<<EOT
<dt><span>$meta->display</span></dt>
<dd class="radioType">
    $radio
</dd>
EOT;
    }

    public function diffValue($oldVal, $newVal, $meta = null)
    {
        $outputOld = $outputNew = [];
        $diff = false;//默认相同 
        $outputOld['value'] = @$oldVal['value'];//根据$oldVal重组outputOld
        $outputOld['options']['textarea'] = @$oldVal['append']['textarea'];
        $outputNew['value'] = @$newVal['value'];
        if (!empty($newVal['append'])) {
            if (!empty($newVal['append']['text'])) {
                if (end($meta->hint) == $newVal['value']) {
                    $outputNew['value'] = $newVal['append']['text'];
                }
            }
            if (!empty($newVal['append']['textarea'])) {
                $title = !empty($meta->options['appendTextarea']['title']) ? $meta->options['appendTextarea']['title'] : null;
                $outputNew['options']['textarea_title'] = $title;
                $outputNew['options']['textarea'] = $newVal['append']['textarea'];
            }
        }

        $diff = $outputOld['value'] !== $outputNew['value'];
        //相同继续判断次级内容
        if(!$diff) {
            if (!empty($outputNew['append']['textarea']) && !empty($outputOld['options']['textarea'])) {
                $diff = $outputOld['options']['textarea'] !== $outputNew['options']['textarea'];
                return [
                    "diff" => $diff,
                    "old" => $oldVal,
                    "new" => $outputNew,
                ]; 
            } else if(empty($outputNew['append']['textarea']) && empty($outputOld['options']['textarea'])) {
                return [
                    "diff" => false,
                    "old" => $oldVal,
                    "new" => $outputNew,
                ];
            } else {
                return [
                    "diff" => true,
                    "old" => $oldVal,
                    "new" => $outputNew,
                ];
            }
        } else {
            return [
                "diff" => $diff,
                "old" => $oldVal,
                "new" => $outputNew,
            ];     
        } 
    }
}
