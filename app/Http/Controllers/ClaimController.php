<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\StoreClaimRequest;
use App\Models\ClaimType;
use App\Models\PaymentMethod;
use App\Models\Claim;
use App\Models\ClaimDocument;
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
                'annual_limit' => null,
                'status' => 'Pending',
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
            'redirect' => route('claims.index')
        ]);
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
               'error' => $e->getMessage() // You can remove this in production for security
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
}
