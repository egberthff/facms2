<?php

namespace App\Enums;

enum MembershipFeeStatus: string
{
    case Pending = 'Pending';
    case Paid = 'Paid';
    case Waived = 'Waived';
    case Cancelled = 'Cancelled';
}