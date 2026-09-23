<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Perpetual = 'Perpetual';
}
