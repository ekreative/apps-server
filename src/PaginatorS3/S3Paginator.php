<?php

namespace App\PaginatorS3;

use Doctrine\Common\Collections\ArrayCollection;

class S3Paginator
{
    /**
     * @var ArrayCollection
     */
    private $data;

    /**
     * @var int
     */
    private $nextPage;

    /**
     * @var int
     */
    private $prevPage;

    /**
     * @var int
     */
    private $numberOfPages;

    /**
     * S3Paginator constructor.
     */
    public function __construct()
    {
        $this->data = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param ArrayCollection $data
     * @return ArrayCollection
     */
    public function setData(ArrayCollection $data)
    {
        return $this->data = $data;
    }

    /**
     * @param string $data
     * @return S3Paginator
     */
    public function addToData($data)
    {
        $this->data->add($data);

        return $this;
    }

    /**
     * @param  string $data
     * @return S3Paginator
     */
    public function removeFromData($data)
    {
        $this->data->removeElement($data);

        return $this;
    }

    /**
     * @return int
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    /**
     * @param int $nextPage
     * @return S3Paginator
     */
    public function setNextPage($nextPage)
    {
        $this->nextPage = $nextPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrevPage()
    {
        return $this->prevPage;
    }

    /**
     * @param int $prevPage
     * @return S3Paginator
     */
    public function setPrevPage($prevPage)
    {
        $this->prevPage = $prevPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * @param int $numberOfPages
     * @return S3Paginator
     */
    public function setNumberOfPages($numberOfPages)
    {
        $this->numberOfPages = $numberOfPages;

        return $this;
    }
}
