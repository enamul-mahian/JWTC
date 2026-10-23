<?php
namespace App\Support;
class FormFacade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(){return 'native.form';}
}
