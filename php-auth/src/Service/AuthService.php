<?php

namespace Service;

use Manager\UserManager;
use Psr\Log\LoggerInterface;
use Util\Session;

/**
 * Class AuthService
 * @package Service
 */
class AuthService
{
    /** @var UserManager */
    private $userManager;

    /** @var Session */
    private $session;

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        UserManager $userManager,
        Session $session,
        string $clientId,
        string $clientSecret,
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
        $this->session = $session;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->logger = $logger;
    }

    public function signUp(int $providerId, string $ownerId, $name)
    {
        $loginUser = null;
        if ($userId = $this->session->get('user_id')) {
            $loginUser = $this->userManager->getUserByUserId($userId);
            if ($loginUser === null) {
                $this->logger->warning(sprintf('USER NOT FOUND(user_id=%s)', $userId));
                $this->signOut();
                return false;
            }
        }

        try {
            $this->userManager->getUserDao()->transaction(function () use ($loginUser, $providerId, $ownerId, $name) {
                $user = null;

                $userProvider = $this->userManager->getUserProviderDao()
                    ->findOneByProviderIdAndOwnerId($providerId, $ownerId);

                if ($userProvider) {
                    $user = $this->userManager->getUserByUserId($userProvider['user_id']);
                    if ($user) {
                        if ($loginUser && $loginUser['user_id'] !== $user['user_id']) {
                            $this->logger->error(sprintf(
                                '$loginUser(%s) != $user(%s)',
                                $loginUser['user_id'],
                                $user['user_id']
                            ));
                            $this->signOut();
                        }
                    } else {
                        $this->logger->warning(sprintf(
                            'User not found.(user_id:%s, provider_id:%s, owner_id:%s)',
                            $userProvider['user_id'],
                            $userProvider['provider_id'],
                            $userProvider['owner_id']
                        ));
                    }
                }
                if (!$user) {
                    $user = $loginUser;
                }

                if (!$user) {
                    $user = $this->userManager->createUser($name);
                }

                if ($user['name'] === null) {
                    $user['name'] = $name;
                    $this->userManager->updateUser($user);
                }

                if (!$userProvider) {
                    $this->userManager->getUserProviderDao()->create([
                        'user_id' => $user['user_id'],
                        'provider_id' => $providerId,
                        'owner_id' => $ownerId,
                    ]);
                }

                $this->signIn($user['user_id']);
            });

            return true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    public function signIn($userId): bool
    {
        if (!$user = $this->userManager->getUserByUserId($userId)) {
            return false;
        }

        $this->session['user_id'] = $user['user_id'];
        $this->session['name'] = $user['name'];
        $this->session['roles'] = $user['roles'];
        $this->userManager->getUserSessionDao()->update([
            'user_id' => $userId,
            'session_id' => session_id(),
        ]);

        return true;
    }

    public function signOut(): bool
    {
        unset(
            $this->session['user_id'],
            $this->session['name'],
            $this->session['roles']
        );

        return true;
    }
}
