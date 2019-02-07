<?php

namespace App\PaginatorS3;

use App\AWS\S3;
use Doctrine\Common\Collections\ArrayCollection;

class Paginator
{
    const LIMIT_PAGINATION = 2;

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
     * @var S3
     */
    private $s3;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var array
     */
    private $param;

    /**
     * S3Paginator constructor.
     * @param S3 $s3
     * @param int $currentPage
     * @param array $param
     */
    public function __construct(S3 $s3, int $currentPage, array $param)
    {
        $this->s3 = $s3;
        $this->currentPage = $currentPage;
        $this->param = $param;
        $this->data = new ArrayCollection();
        $this->process();
    }

    /**
     * @return ArrayCollection
     */
    public function getData()
    {
        return new ArrayCollection($this->data->getValues());
    }

    /**
     * @param ArrayCollection $data
     */
    public function setData(ArrayCollection $data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    /**
     * @return int
     */
    public function getPrevPage()
    {
        return $this->prevPage;
    }

    /**
     * @return int
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    private function process()
    {
        $count = 0;

        $iterator = $this->s3->getIterator($this->param);

        foreach ($iterator as $item) {
            $key = $item['Key'];
            $count++;

            if ($count <= ($this->currentPage * self::LIMIT_PAGINATION) && (($this->currentPage - 1) * self::LIMIT_PAGINATION) < $count) {
                $this->data->add($this->s3->getObjectByKey($key));
            }
        }

        $this->numberOfPages = ceil($count / self::LIMIT_PAGINATION);
        $this->prevPage = $this->currentPage - 1;
        $this->nextPage = $this->currentPage + 1;
    }
}
