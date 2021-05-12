<?php

namespace Phobrv\BrvUpgrade\Facades;

use Illuminate\Support\Facades\Facade;

class BrvUpgrade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'brvupgrade';
    }
}
