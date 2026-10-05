<?php 
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ContributionDetailsController;

Route::middleware(['auth', 'active', 'verified', 'member.active'])
    ->group(function () {

        Route::resource('members', MemberController::class)->middleware('can:manage members,App\Models\Member');
        // Route::post('members/add-new-member', [MemberController::class, 'store'])->middleware('can:create,App\Models\Member')->name('members.store');
        Route::post('members/add-new-member-csv', [MemberController::class, 'storeCSV'])->middleware('can:create,App\Models\Member')->name('members.storeCSV');
        Route::put('members/{member}', [MemberController::class, 'update'])->middleware('can:update,member')->name('members.update');
        // Route::get('members/my-contributions/{user}', [ContributionDetailsController::class, 'myOwedContributions'])->middleware('can:view own contribution, member')->name('member.my-contributions');
        Route::get('members/my-contributions/{user}', [ContributionDetailsController::class, 'myOwedContributions'])->middleware('can:view own contribution')->name('member.my-contributions');
        Route::get('members/{member}/{type}', [MemberController::class, 'getHistory'])->name('members.history');
        Route::get('profile', [MemberController::class, 'getProfilePage'])->name('members.profile');
        Route::patch('profile', [MemberController::class, 'updateProfile'])->name('members.profile.update');
        
        Route::get('/notifications/{id}/read', function ($id) {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            return redirect($notification->data['url'] ?? '/dashboard');
            })->name('notifications.read');
            
        Route::post('/notifications/mark-all-read', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back();
        })->name('notifications.markAllRead');  
        
        Route::post('members/membership-fees', [MemberController::class, 'membershipFee'])->name('members.membership-fees-store');
        Route::get('members/membership-fees/members/search', [MemberController::class, 'searchMembers'])->name('members.membership-fees-search');
        });

      
