<?php

use AppBundle\Deploy\Deployer;

return new class extends Deployer
{
    public function configure()
    {
        return $this->getConfigBuilder()
            // SSH connection string to connect to the remote server (format: user@host-or-IP:port-number)
            ->server('yuyong:@10.100.10.125')
            ->server('yuyong:@10.100.10.126')
            // the absolute path of the remote server directory where the project is deployed
            ->deployDir('/opt/site/')
            ->webDir('web/')
            // the URL of the Git repository where the project code is hosted
            ->repositoryUrl('git@git.coding.net:linghuyong/HPL-ERP.git')
            // the repository branch to deploy
            ->repositoryBranch('master')
            ->installWebAssets(false)
            ->keepReleases(2)
            ->sharedFilesAndDirs(['app/logs', 'app/sessions', 'app/config/parameters.yml', 'java/imguploader.jar', 'app/cert/cjd_dist_rsa_private_key.pem'])
            ->writableDirs(['app/cache/', 'app/logs/', 'app/sessions/'])
            ->resetOpCacheFor("http://127.0.0.1:8081/reset_opcache.php")
        ;
    }
};
