<?php

namespace App\Business\Strategies;

interface PayrollCalculatorInterface
{
    public function calculate(float $basisAmount, string $type): float;
}