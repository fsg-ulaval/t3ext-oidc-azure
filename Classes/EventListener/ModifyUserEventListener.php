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
        if (empty($event->getOidcResourceOwner()['roles']) || $event->getAuthenticationService()->authInfo['loginType'] === 'FE' || empty($event->getAuthenticationService()->getConfig()['adminRole'])) {
            return;
        }

        $roles = is_array($event->getOidcResourceOwner()['roles']) ? $event->getOidcResourceOwner()['roles'] : GeneralUtility::trimExplode(',', $event->getOidcResourceOwner()['roles'], true);

        $user = $event->getUser();
        $user['admin'] = 0;
        if (in_array($event->getAuthenticationService()->getConfig()['adminRole'], $roles, true)) {
            $user['admin'] = 1;
        }

        $event->setUser($user);
    }
}
