<?php

namespace App\Policies;

use App\Enums\FeaturePermission;
use App\Models\IssueAttachment;
use App\Models\User;

class IssueAttachmentPolicy
{
    public function view(User $user, IssueAttachment $attachment): bool
    {
        $issue = $attachment->issue;

        if ($issue === null) {
            return false;
        }

        return $user->hasPermission(FeaturePermission::ManageIssues)
            || $issue->reporter_id === $user->id;
    }
}
