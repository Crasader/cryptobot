<?php
namespace App\Library\Services\Facades;
use Illuminate\Support\Facades\Facade;
class Bittrex extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'bittrex'; }
}