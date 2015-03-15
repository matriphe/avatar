<?php namespace Matriphe\Avatar;

use Illuminate\Support\Facades\Facade;

class MatripheFacade extends Facade {

  protected static function getFacadeAccessor()
  {
    return 'avatar';
  }

}