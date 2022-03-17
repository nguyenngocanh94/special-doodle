<?php
namespace App\Collection;

class Page
{
    public function __construct($page, $itemPerPages)
    {
        $this->page = $page;
        $this->itemPerPages = $itemPerPages;
    }

    public int $page;
    public int $itemPerPages;

    public function getOffset(){
        return ($this->page-1) * $this->itemPerPages;
    }

    public static function unLimit()
    {
        return new Page(1,-1);
    }

    public static function defaultPage()
    {
        return new Page(1,10);
    }

    public static function from($page, $limit)
    {
        return new Page($page,$limit);
    }

    public function isUnlimit()
    {
        return $this->itemPerPages == -1;
    }
}