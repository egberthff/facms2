<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Support\Str;
use App\Http\Requests\StoreClaimRequest;
use App\Enums\ClaimStatus;
use App\Models\ClaimType;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\SystemSetting;
class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = 'Submit Claim';
        $data['claimTypes'] = ClaimType::all();
        $data['paymentMethods'] = PaymentMethod::all();
        $data['paymentMethodBanks'] = ['BDO', 'BPI', 'PNB', 'LandBank', 'Mentrobank'];

        return view('claims.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClaimRequest $request)
    {
           
        DB::beginTransaction();
    
        try {
    
            /*
            |--------------------------------------------------------------------------
            | Get IDs
            |--------------------------------------------------------------------------
            */
    
            // $claimType = ClaimType::where('id', $request->claimType)->firstOrFail();
            // $paymentMethod = PaymentMethod::where('id', $request->paymentMethod)->firstOrFail();
    
            /*
            |--------------------------------------------------------------------------
            | Generate Claim Number
            |--------------------------------------------------------------------------
            */
    
            $claimNumber = 'CLM-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(6));
    
             /*
            |--------------------------------------------------------------------------
            | Get Annual Limit
            |--------------------------------------------------------------------------
            */
            $annualLimit = SystemSetting::annualLimit();

            /*
            |--------------------------------------------------------------------------
            | Create Claim
            |--------------------------------------------------------------------------
            */
    
            $claim = Claim::create([
                'claim_number' => $claimNumber,
                'member_id' => Auth::user()->member->id,
                'claim_type_id' => $request->claimType,
                'payment_method_id' => $request->paymentMethod,
                'requested_amount' => $request->amount,
                'approved_amount' => null,
                'annual_limit' =>  $this->toMysqlDecimal($annualLimit->setting_value),
                'status' => ClaimStatus::Pending->value,
                'description' => $request->description,
                'submitted_at' => now(),
            ]);
    
            /*
            |--------------------------------------------------------------------------
            | Save Uploaded Files
            |--------------------------------------------------------------------------
            */
    
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $storedFilename =
                        Str::uuid()
                        . '.'
                        . $file->getClientOriginalExtension();
                    $path = $file->storeAs(
                        'documents/claims',
                        $storedFilename,
                        'public'
                    );
    
                    ClaimDocument::create([
                        'claim_id' => $claim->id,
                        'document_type' => pathinfo(
                            $file->getClientOriginalName(),
                            PATHINFO_EXTENSION
                        ),
                        'original_filename' =>
                            $file->getClientOriginalName(),
                        'stored_filename' =>
                            $storedFilename,
                        'file_path' =>
                            $path,
                        'mime_type' =>
                            $file->getMimeType(),
                        'file_size' =>
                            $file->getSize(),
                    ]);
                }
            }
    
            DB::commit();
            return response()->json([
            'success' => true,
            'message' => 'Claim submitted successfully.',
            // 'redirect' => route('claims.index')
            ], 200);
            // return redirect()
            //     ->route('claims.index')
            //     ->with(
            //         'success',
            //         'Claim submitted successfully.'
            //     );
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
           // Return a JSON response with a 500 error code
           return response()->json([
               'success' => false,
               'message' => 'Something went wrong while processing your claim.',
            //    'error' => $e->getMessage() // You can remove this in production for security
           ], 500); 
            // return back()
            //     ->withInput()
            //     ->with(
            //         'error',
            //         $e->getMessage()
            //     );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function myClaims(User $user)
    {
        Gate::authorize('viewMyClaims', [Claim::class, $user]);

        // 2. Safely grab the user's member relation profile
        $member = $user->member;

        // 3. Fallback safely if this user account isn't hooked to a member profile
        if (!$member) {
            $data['claims'] = collect(); // returns an empty collection instead of crashing
            return view('claims.claims-list', $data);
        }

        // 4. Query using the direct member ID
        $data['claims'] = Claim::forMember($member->id)
        ->with(['claimType', 'paymentMethod', 'reviewer', 'documents'])
        ->latest('submitted_at')
        ->get();

        \Log::error(print_r($data['claims'][0], true));

        return view('claims.claims-list', $data);
    }
    
    private function toMysqlDecimal($number, int $decimals = 2): string 
    {
        // Clean localized number strings (e.g., convert "1.234,56" or "1,234.56" to a clean float)
        if (is_string($number)) {
            // Strip out anything that isn't a digit, dot, or minus sign
            $number = str_replace(',', '.', $number);
            $number = preg_replace('/[^0-9.-]/', '', $number);
        }

        // Format the number to match MySQL expectations
        return number_format((float)$number, $decimals, '.', '');
    }

}
