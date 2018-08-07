<?php

namespace Service;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
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

    /** @var array */
    private $grantRules;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        UserManager $userManager,
        Session $session,
        array $grantRules = [],
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
        $this->session = $session;
        $this->grantRules = $grantRules;
        $this->logger = $logger;
    }

    public function signUp(int $providerId, ResourceOwnerInterface $owner): ?array
    {
        $loginUser = null;
        if ($userId = $this->session->get('user_id')) {
            $loginUser = $this->userManager->getUserByUserId($userId);
            if ($loginUser === null) {
                $this->logger->warning(sprintf('USER NOT FOUND(user_id=%s)', $userId));
                $this->signOut();
                return null;
            }
        }

        $pdo = $this->userManager->getUserDao()->getPdo();
        $pdo->beginTransaction();
        $user = null;
        try {
            $userProvider = $this->userManager->getUserProviderDao()
                ->findOneByProviderIdAndOwnerId($providerId, $owner->getId());

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

            $ownerName = null;
            $obj = new \ReflectionObject($owner);
            try {
                $ownerName = $obj->getMethod("getName")->invoke($owner);
            } catch (\ReflectionException $ignore) {
            }

            if (!$user) {
                $user = $this->userManager->createUser($ownerName);
            } elseif ($user['name'] === null and $ownerName) {
                $user['name'] = $ownerName;
                $this->userManager->updateUser($user);
            }

            if (!$userProvider) {
                $this->userManager->getUserProviderDao()->create([
                    'user_id' => $user['user_id'],
                    'provider_id' => $providerId,
                    'owner_id' => $owner->getId(),
                ]);
            }

            $ownerArray = $owner->toArray();
            $rolesToAdd = [];
            if (isset($this->grantRules[$providerId])) {
                $grantRule = $this->grantRules[$providerId];
                $this->logger->notice(sprintf('count($grantRule) = %d', count($grantRule)));
                foreach ($grantRule as $key => $rules) {
                    if (!isset($ownerArray[$key])) {
                        $this->logger->warning(sprintf('Wrong key: %s[%s]', get_class($owner), $key));
                        continue;
                    }

                    foreach ($rules as $pattern => $roles) {
                        $this->logger->notice(sprintf(
                            'pattern = %s, gettype($pattern) = %s, $ownerArray[%s] = %s',
                            $pattern,
                            gettype($pattern),
                            $key,
                            $ownerArray[$key]
                        ));

                        if ($pattern == $ownerArray[$key] or preg_match($pattern, $ownerArray[$key])) {
                            foreach ($roles as $role) {
                                if (!in_array($role, $rolesToAdd)) {
                                    $rolesToAdd[] = $role;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($rolesToAdd as $role) {
                $this->userManager->addRole($user, $role);
            }

            $pdo->commit();

            return $this->signIn($user['user_id']);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $pdo->rollBack();
            return null;
        }
    }

    public function signIn($userId): ?array
    {
        if (!$user = $this->userManager->getUserByUserId($userId)) {
            return null;
        }

        $this->session['user_id'] = $user['user_id'];
        $this->session['name'] = $user['name'];
        $this->session['roles'] = $user['roles'];
        $this->userManager->getUserSessionDao()->update([
            'user_id' => $userId,
            'session_id' => session_id(),
        ]);

        return $user;
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
