<?php

namespace App\Collection;

class Op
{
    public $type;
    public $function;

    public function __construct($type, $function)
    {
        $this->type = $type;
        $this->function = $function;
    }
}