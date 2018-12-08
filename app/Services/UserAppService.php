<?php

namespace Caronae\Services;

use Caronae\Models\User;
use Illuminate\Support\Facades\DB;

class UserAppService
{
    public function getActiveUsersWithOldAppVersions()
    {
        return User
            ::whereDate('updated_at', '>=', DB::raw("current_date - interval '15 days'"))
            ->where(function ($query) {
                $query
                    ->where(function ($query) {
                        $query->where('app_platform', 'iOS')->where('app_version', '~', '^1\.[0-4]\.\d+$');
                    })
                    ->orWhere(function ($query) {
                        $query->where('app_platform', 'Android')->where('app_version', '~', '^(3\.0\.[0-4])|([0-2]\.\d+\.\d+)$');
                    });
            })->get();
    }
}
