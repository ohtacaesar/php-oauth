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
    )
    {
        $this->userManager = $userManager;
        $this->session = $session;
        $this->grantRules = $grantRules;
        $this->logger = $logger;
    }

    public function signUp(int $providerId, ResourceOwnerInterface $owner): ?array
    {
        $pdo = $this->userManager->getUserDao()->getPdo();
        $pdo->beginTransaction();
        try {
            $loginUser = null;
            if ($userId = $this->session->get('user_id')) {
                $loginUser = $this->userManager->getUserByUserId($userId);
                if ($loginUser === null) {
                    $this->logger->warning(sprintf('USER NOT FOUND(user_id=%s)', $userId));
                    $this->signOut();
                    return null;
                }
            }

            $user = $this->userManager->getUserByProviderIdAndOwnerId($providerId, $owner->getId());
            if ($user) {
                if ($loginUser && $loginUser['user_id'] !== $user['user_id']) {
                    $this->logger->error(sprintf(
                        '$loginUser(%s) != $user(%s)',
                        $loginUser['user_id'],
                        $user['user_id']
                    ));
                    $this->signOut();
                    return null;
                }
            } else {
                $user = $loginUser;
            }

            $user = $this->updateOrCreateUser($user, $owner);

            if (!in_array($providerId, $user['provider_ids'] ?? [], true)) {
                $this->userManager->getUserProviderDao()->create([
                    'user_id' => $user['user_id'],
                    'provider_id' => $providerId,
                    'owner_id' => $owner->getId(),
                ]);
            }

            $this->applyGrantRules($user, $providerId, $owner);
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

        return $user;
    }

    public function signOut(): bool
    {
        unset($this->session['user_id']);

        return true;
    }

    private function updateOrCreateUser(?array $user, ResourceOwnerInterface $owner): ?array
    {
        $ownerName = method_exists($owner, 'getName') ? $owner->getName() : null;

        if ($user === null) {
            $user = $this->userManager->createUser($ownerName);
        } elseif ($user['name'] === null and $ownerName) {
            $user['name'] = $ownerName;
            $this->userManager->updateUser($user);
        }

        return $user;
    }

    private function applyGrantRules(array $user, int $providerId, ResourceOwnerInterface $owner)
    {
        if (!isset($this->grantRules[$providerId])) {
            return;
        }

        $grantRule = $this->grantRules[$providerId];
        $this->logger->notice(sprintf('count($grantRule) = %d', count($grantRule)));
        $ownerArray = $owner->toArray();
        $rolesToAdd = [];
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
                        $rolesToAdd[] = $role;
                    }
                }
            }
        }

        foreach (array_unique($rolesToAdd) as $role) {
            $this->userManager->addRole($user, $role);
        }
    }

    public function signinWithToken($signinToken)
    {
        $user = $this->userManager->getUserBySigninToken($signinToken);
        if ($user === null) {
            return null;
        }

        $this->session['user_id'] = $user['user_id'];

        return $user;
    }
}
