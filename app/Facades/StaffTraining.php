<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class StaffTraining extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'staff_training_engine';
    }
}