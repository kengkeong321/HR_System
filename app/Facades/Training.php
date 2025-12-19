<?php
namespace App\Facades;
use Illuminate\Support\Facades\Facade;

class Training extends Facade {
    protected static function getFacadeAccessor() { return 'training_service'; }
}