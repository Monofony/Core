<?php

/*
 * This file is part of mz_067_s_ccpa_thermotool.
 *
 * (c) Mobizel
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\RegisterAppUser;
use App\Provider\CustomerProviderInterface;
use Monofony\Contracts\Core\Model\User\AppUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RegisterAppUserHandler implements MessageHandlerInterface
{
    private $appUserFactory;
    private $appUserRepository;
    private $customerProvider;

    public function __construct(
        FactoryInterface $appUserFactory,
        RepositoryInterface $appUserRepository,
        CustomerProviderInterface $customerProvider
    ) {
        $this->appUserFactory = $appUserFactory;
        $this->appUserRepository = $appUserRepository;
        $this->customerProvider = $customerProvider;
    }

    public function __invoke(RegisterAppUser $command): void
    {
        /** @var AppUserInterface $user */
        $user = $this->appUserFactory->createNew();
        $user->setPlainPassword($command->password);

        $customer = $this->customerProvider->provide($command->email);
        if ($customer->getUser() !== null) {
            throw new \DomainException(sprintf('User with email "%s" is already registered.', $command->email));
        }

        $customer->setFirstName($command->firstName);
        $customer->setLastName($command->lastName);
        $customer->setPhoneNumber($command->phoneNumber);
        $customer->setUser($user);

        $this->appUserRepository->add($user);
    }
}
