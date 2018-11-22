<?php

namespace AppBundle\BusinessExtend\Pingan;

use AppBundle\Model\MetadataManager;
use AppBundle\Model\Metadata;
use AppBundle\Entity\Config;

/**
 * 平安租赁metadataManager
 */
class PinganMetadataManager extends MetadataManager
{
	public function __construct($video)
    {
    	parent::__construct($video);
    }
}