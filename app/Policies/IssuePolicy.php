<?php

namespace App\Policies;

use App\Enums\FeaturePermission;
use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    public function view(User $user, Issue $issue): bool
    {
        return $user->hasPermission(FeaturePermission::ManageIssues)
            || $issue->reporter_id === $user->id;
    }

    public function update(User $user, Issue $issue): bool
    {
        return $user->hasPermission(FeaturePermission::ManageIssues);
    }
}
