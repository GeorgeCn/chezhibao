<?php

namespace AppBundle\Model\MetadataType;

interface MetadataTypeInterface
{
    public function makeValue($input, $meta = null);

    public function makeDom($meta, $reportdata = []);

    // 返回array
    // diff: true false 表示是否有差异
    // old: 旧值，用于前台显示
    // new: 新值，用于前台显示
    public function diffValue($oldVal, $newVal);
}