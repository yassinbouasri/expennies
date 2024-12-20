<?php

declare(strict_types=1);


namespace App\RequestValidators;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\UserProfileData;
use App\Entity\User;

class UserProfileService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManagerService)
    {
    }

    public function update(User $user, UserProfileData $data): void
    {
        $user->setEmail($data->email);
        $user->setTwoFactor($data->twoFactor);

        $this->entityManagerService->sync($user);
    }

    public function get(int $userId): UserProfileData
    {
        $user = $this->entityManagerService->find(User::class, $userId);

        return new UserProfileData($user->getEmail(), $user->getEmail(), $user->hasTwoFactorAuthEnabled());
    }
}