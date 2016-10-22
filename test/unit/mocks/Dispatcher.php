<?php

namespace BfwFastRoute\test\unit\mocks;

class Dispatcher extends \FastRoute\Dispatcher\GroupCountBased
{
    public function __get($name)
    {
        return $this->{$name};
    }
}
