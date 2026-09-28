<?php

namespace App\Enums;

enum ClaimTypes: string
{
    case Claims = 'Claims';
    case Annual = 'Annual Due';
    case CNAShare = 'CNA Share';
    // case AnnualDue = 'Annual Due';
}

