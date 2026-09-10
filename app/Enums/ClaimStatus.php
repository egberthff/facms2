<?php

namespace App\Enums;

enum ClaimStatus: string
{
    case Pending = 'Pending';
    case Processing = 'Processing';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
    case Disbursed = 'Disbursed';
    case ForDisbursement = 'For Disbursement';
    case ForReviewFp = 'For Review FP';
}

