<?php

namespace App\Factory;

use App\Entity\App;

interface ReaderInterface
{
    /**
     * @param App $app
     * @return App
     */
    public function readData(App $app);

    /**
     * @return string
     */
    public function getExtension();
}