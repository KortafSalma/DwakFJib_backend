<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MedicalCertificate;

class MedicalCertificatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MedicalCertificate $certificate): bool
    {
        return $user->id === $certificate->user_id || $user->role === User::ROLE_ADMIN;
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_USER;
    }

    public function update(User $user, MedicalCertificate $certificate): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function delete(User $user, MedicalCertificate $certificate): bool
    {
        return $user->id === $certificate->user_id || $user->role === User::ROLE_ADMIN;
    }
}
