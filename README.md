## Configuration

In order to use this Microsoft EntraID (Azure) oauth provider you need to add the following configuration to your oidc settings: `config/system/oidc.yaml`

```yaml
providers:
  default:
    oauthProviderFactory: FSG\OidcAzure\Factory\OAuth2AzureProviderFactory
```
