<?php

namespace AppBundle\BusinessExtend\Jgqc;

use AppBundle\Model\MetadataManager;
use AppBundle\Model\Metadata;
use AppBundle\Entity\Config;

/**
 * 成都建国汽车的metadataManager
 */
class JgqcMetadataManager extends MetadataManager
{
	public function __construct($video)
    {
    	parent::__construct($video);
    }
}