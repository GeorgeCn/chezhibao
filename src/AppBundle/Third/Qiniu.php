<?php

namespace AppBundle\Third;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class Qiniu {
    const accessKey = 'Uee0-ojbDeNQFbTxPsWfTjQBRQhVSXKvhz5zbxZv';
    const secretKey = 'We0JSRwm_qpJZmtdZ87zQbzni5Dys1HSIBxXCVlU';

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getUpToken($bucket, $prefix, $ttl=36000)
    {
        $auth = new Auth(self::accessKey, self::secretKey);
        $token = $auth->uploadToken($bucket, null, $ttl, ["saveKey"=>"$prefix/$(etag)"]);
        return $token;
    }
}
