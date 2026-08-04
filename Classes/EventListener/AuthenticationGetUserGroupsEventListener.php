<?php

declare(strict_types=1);

namespace FSG\OidcAzure\EventListener;

use Causal\Oidc\Event\AuthenticationGetUserGroupsEvent;
use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Customize user group mapping
 */
final class AuthenticationGetUserGroupsEventListener
{
    /**
     * @throws Exception
     */
    public function __invoke(AuthenticationGetUserGroupsEvent $event): void
    {
        // Map Azure roles to TYPO3 user groups
        if (!empty($event->getResource()['roles'])) {
            $newUserGroups = $event->getUserGroups();
            $roles = is_array($event->getResource()['roles']) ? $event->getResource()['roles'] : GeneralUtility::trimExplode(',', $event->getResource()['roles'], true);

            // If no admin role is configured, authentication service doesn't manage that capability nor system maintainers.
            if (!empty($event->getAuthenticationService()->getConfig()->administratorRole)
                && in_array($event->getAuthenticationService()->getConfig()->administratorRole, $roles, true)) {
                $event->setIsAdministrator(true);
                if (!empty($event->getAuthenticationService()->getConfig()->maintainerRole)
                    && in_array($event->getAuthenticationService()->getConfig()->maintainerRole, $roles, true)) {
                   $event->setIsSystemMaintainer(true);
                }
            }

            if (!empty($event->getAuthenticationService()->getConfig()->administratorRole)
                && ($administratorRoleKey = array_search($event->getAuthenticationService()->getConfig()->administratorRole, $roles, true)) !== false) {
                unset($roles[$administratorRoleKey]);
            }

            if (!empty($event->getAuthenticationService()->getConfig()->maintainerRole)
                && ($maintainerRoleKey = array_search($event->getAuthenticationService()->getConfig()->maintainerRole, $roles, true)) !== false) {
                unset($roles[$maintainerRoleKey]);
            }

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                                          ->getQueryBuilderForTable($event->getGroupTable());
            $typo3Roles = $queryBuilder
                ->select('uid', 'tx_oidc_pattern')
                ->from($event->getGroupTable())
                ->where(
                    $queryBuilder->expr()->neq('tx_oidc_pattern', $queryBuilder->quote(''))
                )
                ->executeQuery()
                ->fetchAllAssociative();

            $roles = ',' . implode(',', $roles) . ',';
            foreach ($typo3Roles as $typo3Role) {
                // Convert the pattern into a proper regular expression
                $subpatterns = GeneralUtility::trimExplode('|', $typo3Role['tx_oidc_pattern'], true);
                foreach ($subpatterns as $k => $subpattern) {
                    $pattern = preg_quote($subpattern, '/');
                    $pattern = str_replace('\\*', '[^,]*', $pattern);
                    $subpatterns[$k] = $pattern;
                }
                $pattern = '/,(' . implode('|', $subpatterns) . '),/i';
                if (preg_match($pattern, $roles)) {
                    $newUserGroups[] = (int)$typo3Role['uid'];
                }
            }
            $event->setUserGroups($newUserGroups);
        }
    }
}
