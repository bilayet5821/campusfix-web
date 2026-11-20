<?php

namespace App\Policies;

use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    public function view(User $user, Issue $issue): bool
    {
        return $user->isAdmin() || $issue->reporter_id === $user->id;
    }

    public function update(User $user, Issue $issue): bool
    {
        return $user->isAdmin();
    }
}
