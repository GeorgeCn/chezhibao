<?php

namespace AppBundle\BusinessExtend\Ztr;

use AppBundle\Model\MetadataManager;
use AppBundle\Model\Metadata;
use AppBundle\Entity\Config;

/**
 * 浙江中投融汽车metadataManager
 */
class ZtrMetadataManager extends MetadataManager
{
	public function __construct($video)
    {
    	parent::__construct($video);
    }
}