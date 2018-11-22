<?php

namespace AppBundle\Model;

class Metadata
{
    public $key;

    public $type;

    public $display;

    public $hint;

    public $options;

    public $enable;

    public $value;

    public function __construct($key, $display, $type, $hint = [], $options = [], $enable = true)
    {
        $this->key = $key;
        $this->display = $display;
        $this->type = $type;
        $this->hint = $hint;
        $this->options = $options;
        $this->enable = $enable;
        $this->value = null;
    }

    public function makeValue($input)
    {
        $metadataType = MetadataType\MetadataTypeFactory::getMetadataType($this->type);
        if (empty($metadataType)) {
            throw new \Exception("can't find {$this->type} metadataType.");
        }
        $this->value = $metadataType->makeValue($input, $this);
        
        return $this->value;
    }

    public function makeDom($reportdata = [])
    {
        $metadataType = MetadataType\MetadataTypeFactory::getMetadataType($this->type);
        if (empty($metadataType)) {
            throw new \Exception("can't find {$this->type} metadataType.");
        }
        return $metadataType->makeDom($this, $reportdata);
    }

    // 返回array
    // diff: true false 表示是否有差异
    // old: 旧值，用于前台显示
    // new: 新值，用于前台显示
    public function diffValue($oldReport, $newReport)
    {
        $metadataType = MetadataType\MetadataTypeFactory::getMetadataType($this->type);
        if (empty($metadataType)) {
            throw new \Exception("can't find {$this->type} metadataType.");
        }

        $this->value = $metadataType->diffValue($oldReport, $newReport, $this);
        return $this->value;
    }
}
