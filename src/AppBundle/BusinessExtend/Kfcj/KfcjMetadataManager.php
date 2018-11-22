<?php

namespace AppBundle\BusinessExtend\Kfcj;

use AppBundle\Model\MetadataManager;
use AppBundle\Model\Metadata;
use AppBundle\Entity\Config;

/**
 * 客服创建临时用户的metadataManager
 */
class KfcjMetadataManager extends MetadataManager
{
	public function __construct($video)
    {
    	parent::__construct($video);
    }
}