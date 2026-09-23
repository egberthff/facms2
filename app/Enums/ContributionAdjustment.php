<?php

namespace App\Enums;

enum ContributionAdjustment: string
{
    case Overdeduction = 'Overdeduction';
    case Underdeduction = 'Underdeduction';
    case Correction = 'Correction';
    case Other = 'Other';
}