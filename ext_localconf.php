<?php

declare(strict_types=1);

use FSG\OidcAzure\Factory\OAuth2AzureProviderFactory;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['oidc']['oauthProviderFactory'] = OAuth2AzureProviderFactory::class;
