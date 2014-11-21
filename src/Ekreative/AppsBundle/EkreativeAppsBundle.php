<?php

namespace Ekreative\AppsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class EkreativeAppsBundle extends Bundle
{

    const S3_BUCKET = 'ek-appshare';
    const S3_ENDPOINT = 'https://ek-appshare.s3-website-eu-west-1.amazonaws.com';
    const S3_WEB_PATH = 'https://s3-eu-west-1.amazonaws.com/ek-appshare';

    public function boot()
    {
        $this->container->get('ekreative_apps.s3')->registerStreamWrapper();
    }
}
