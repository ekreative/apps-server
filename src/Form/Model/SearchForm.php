<?php

namespace App\Form\Model;

class SearchForm
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var int
     */
    private $page;

    /**
     * SearchForm constructor.
     */
    public function __construct()
    {
        $this->page = 1;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return array
     */
    public function getQueryArray()
    {
        return [
            'page=' . $this->getPage(),
            'name=' . $this->getQuery()
        ];
    }
}
