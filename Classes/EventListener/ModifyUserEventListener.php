<?php

declare(strict_types=1);

namespace FSG\OidcAzure\EventListener;

use Causal\Oidc\Event\AuthenticationGetUserGroupsEvent;
use Causal\Oidc\Event\ModifyUserEvent;
use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Customize user group mapping
 */
final class ModifyUserEventListener
{
    public function __invoke(ModifyUserEvent $event): void
    {
        if ($event->getAuthenticationService()->authInfo['loginType'] === 'FE'
            || empty($event->getOidcResourceOwner()['roles'])
            || empty($event->getAuthenticationService()->getConfig()['adminRole'])) {
            return;
        }

        $roles = is_array($event->getOidcResourceOwner()['roles']) ? $event->getOidcResourceOwner()['roles'] : GeneralUtility::trimExplode(',', $event->getOidcResourceOwner()['roles'], true);

        $user = $event->getUser();
        if (!in_array($event->getAuthenticationService()->getConfig()['adminRole'], $roles, true)) {
            // User is not an admin, set the value to zero.
            $user['admin'] = 0;
            $event->setUser($user);
            $event->setIsSystemMaintainer(false);
            return;
        }

        // User is an admin
        $user['admin'] = 1;
        $event->setUser($user);

        if (empty($event->getAuthenticationService()->getConfig()['maintainerRole'])) {
            // Service is not responsible
            return;
        }

        if (!in_array($event->getAuthenticationService()->getConfig()['maintainerRole'], $roles, true)) {
            $event->setIsSystemMaintainer(false);
            return;
        }

        // User is an admin and has "System Maintainer" rights
        $event->setIsSystemMaintainer(true);
    }
}
