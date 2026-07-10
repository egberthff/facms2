<?php
namespace App\Services;

use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\SystemSetting;
use App\Models\User;
use App\Model\Member;
use App\Models\ClaimType;
use App\Models\PaymentMethod;
use App\Models\ClaimPayment;
use App\Enums\ClaimStatus; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class DisbursementService
{

    public function getDisbursmentAll(): Collection
    {
            return ClaimPayment::with([PaymentMethod, ClaimType, Claim, Member]);
    }

}