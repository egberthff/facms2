<?php

namespace App\Services;

use App\Mail\WelcomeEMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Exception;

class EmailService
{
    /**
     * Send welcome emails to one or multiple registered members.
     *
     * @param mixed $members Can be a single Member model, an array, or a Collection.
     * @return void
     */
    public function sendWelcomeEmails($members): void
    {
        // 1. Normalize input into a standard Laravel collection wrapper
        $memberCollection = wrap_into_collection($members);

        foreach ($memberCollection as $member) {
            try {
                // 2. Traverse the relationship to fetch user data
                $user = $member->user;

                if ($user && $user->email) {
                    Mail::to($user->email)->send(new WelcomeEMail($user->name, $user->email));
                }
            } catch (Exception $e) {
                // 3. Log failures individually so one bad email address doesn't halt the rest
                Log::error("Failed to send welcome email to Member ID {$member->id}: " . $e->getMessage());
            }
        }
    }
}

/**
 * Helper utility to normalize mixed types into a singular Collection instance.
 */
function wrap_into_collection($items): Collection 
{
    if ($items instanceof Collection) {
        return $items;
    }
    if (is_array($items)) {
        return collect($items);
    }
    return collect([$items]);
}
