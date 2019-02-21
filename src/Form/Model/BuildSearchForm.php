<?php

namespace App\Form\Model;

class BuildSearchForm
{
    /**
     * @var string
     */
    private $type;

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type)
    {
        $this->type = $type;
    }
}
