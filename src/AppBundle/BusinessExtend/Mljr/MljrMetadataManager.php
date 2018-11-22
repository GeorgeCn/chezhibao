<?php

namespace AppBundle\BusinessExtend\Mljr;

use AppBundle\Model\MetadataManager;
use AppBundle\Model\Metadata;
use AppBundle\Entity\Config;

/**
 * 美丽金融metadataManager
 */
class MljrMetadataManager extends MetadataManager
{
	public function __construct($video)
    {
    	parent::__construct($video);
    }
}