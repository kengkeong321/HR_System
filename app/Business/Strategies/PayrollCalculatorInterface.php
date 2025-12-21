<?php
//Dephnie Ong Yan Yee
namespace App\Business\Strategies;

interface PayrollCalculatorInterface
{
    public function calculate(float $basisAmount, string $type): float;
}