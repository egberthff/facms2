<?php

namespace App\Enums;

enum ClaimStatus: string
{
    case Pending = 'Pending';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
    case Paid = 'Paid';
}
