<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

/**
 * Who may do what with ONE purchase request.
 * Laravel finds this class automatically because of its name
 * (PurchaseRequest model -> PurchaseRequestPolicy).
 */
class PurchaseRequestPolicy
{
    /** A child may cancel or complete only their OWN requests. */
    public function manageOwn(User $user, PurchaseRequest $purchase): bool
    {
        return $user->isChild() && $purchase->requested_by === $user->id;
    }

    /** A parent may approve or reject only their OWN children's requests. */
    public function decide(User $user, PurchaseRequest $purchase): bool
    {
        return $user->isParent() && $user->isParentOf($purchase->requester);
    }
}