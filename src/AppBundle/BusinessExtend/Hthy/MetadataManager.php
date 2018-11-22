<?php

namespace AppBundle\BusinessExtend\Hthy;

use AppBundle\Model\MetadataManager as BaseMetadataManager;
use AppBundle\Model\Metadata;
use AppBundle\Entity\Config;

/**
 * 海通恒运metadataManager
 */
class MetadataManager extends BaseMetadataManager
{
	public function __construct($video)
    {
    	parent::__construct($video);
    }
}