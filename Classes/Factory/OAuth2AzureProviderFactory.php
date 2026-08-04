<?php

declare(strict_types=1);

namespace FSG\OidcAzure\Factory;

use Causal\Oidc\Factory\OAuthProviderFactoryInterface;
use Causal\Oidc\OidcConfiguration;
use League\OAuth2\Client\Provider\AbstractProvider;
use TheNetworg\OAuth2\Client\Provider\Azure;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class OAuth2AzureProviderFactory implements OAuthProviderFactoryInterface
{
    public function create(OidcConfiguration $settings): AbstractProvider
    {
        $options = [
            'clientId' => $settings->clientKey,
            'redirectUri' => $settings->redirectUri,
            'urlAuthorize' => $settings->endpointAuthorize,
            'urlAccessToken' => $settings->endpointToken,
            'urlResourceOwnerDetails' => $settings->endpointUserInfo,
            'scopes' => GeneralUtility::trimExplode(',', $settings->clientScopes, true),
            'defaultEndPointVersion' => getenv('TYPO3_OIDC_AZURE_DEFAULT_END_POINT_TENANT'),
            'tenant' => getenv('TYPO3_OIDC_AZURE_OAUTH_CLIENT_TENANT'),
        ];
        if ($settings->clientSecret) {
            $options['clientSecret'] = $settings->clientSecret;
        } else {
            // https://learn.microsoft.com/en-us/entra/identity-platform/certificate-credentials
            // PEM certificate (newline potentially encoded as '\n'
            $options['clientCertificatePrivateKey'] = getenv('AZURE_OAUTH_CLIENT_CERTIFICATE');
            // SHA-1 thumbprint of the X.509 certificate's DER encoding.
            $options['clientCertificateThumbprint'] = getenv('AZURE_OAUTH_CLIENT_CERTIFICATE_THUMBPRINT');
        }
        return new Azure($options);
    }
}
