<?php

namespace AppBundle\BusinessExtend\General;

use AppBundle\Model\MetadataManager;
use AppBundle\Model\Metadata;

/**
 * 一般公司的metadataManager
 */
class GeneralMetadataManager extends MetadataManager
{
	public function __construct($video)
    {
    	parent::__construct($video);
    }
}