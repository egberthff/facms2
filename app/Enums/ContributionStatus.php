<?php 

namespace App\Enums;

enum ContributionStatus: string
{
    case Pending = 'Pending';
    case Paid = 'Paid';
    case Unpaid = 'Unpaid';
    case Verified = 'Verified';
    case Failed = 'Failed';
}
