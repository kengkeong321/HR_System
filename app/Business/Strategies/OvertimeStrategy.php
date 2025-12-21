<?php
//Dephnie Ong Yan Yee
namespace App\Business\Strategies;

class OvertimeStrategy
{
    
    public function calculate(float $hourlyRate, float $hours, string $dayType): float
    {
        $multiplier = 1.0;

        switch ($dayType) {
            case 'normal':
                $multiplier = 1.5;
                break;
            case 'rest_day':
                $multiplier = 2.0;
                break;
            case 'public_holiday':
                $multiplier = 3.0;
                break;
        }

        return ($hourlyRate * $hours) * $multiplier;
    }
}