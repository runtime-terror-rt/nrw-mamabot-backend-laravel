<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Vendor;
use App\Notifications\VendorRequestNotification;

class VendorObserver
{
    public function created(Vendor $vendor): void
    {
        $admins = User::role('Admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new VendorRequestNotification($vendor));
        }
    }
}
