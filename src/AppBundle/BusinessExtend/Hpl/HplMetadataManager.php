<?php

namespace AppBundle\BusinessExtend\Hpl;

use AppBundle\Model\MetadataManager;
use AppBundle\Model\Metadata;
use AppBundle\Entity\Config;

/**
 * 先锋太盟metadataManager
 */
class HplMetadataManager extends MetadataManager
{
	public function __construct($video)
    {
    	parent::__construct($video);
    }
}