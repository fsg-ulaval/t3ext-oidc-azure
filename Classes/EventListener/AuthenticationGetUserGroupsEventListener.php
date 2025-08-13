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

            $roles = is_array($event->getResource()['roles']) ? $event->getResource()['roles'] : GeneralUtility::trimExplode(',', $event->getResource()['roles'], true);
            $roles = ',' . implode(',', $roles) . ',';

            $newUserGroups = $event->getUserGroups();
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
