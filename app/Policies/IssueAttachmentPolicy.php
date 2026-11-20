<?php

namespace App\Policies;

use App\Models\IssueAttachment;
use App\Models\User;

class IssueAttachmentPolicy
{
    public function view(User $user, IssueAttachment $attachment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $issue = $attachment->issue;

        return $issue !== null && $issue->reporter_id === $user->id;
    }
}
