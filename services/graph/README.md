# Graph

The graph service provides the Graph API which is a RESTful web API used to access Infinite Scale resources. It is inspired by the [Microsoft Graph API](https://learn.microsoft.com/en-us/graph/use-the-api) and can be used by clients or other services or extensions.

## Manual Filters

Using the API, you can manually filter like for users. See the [Libre Graph API](https://owncloud.dev/libre-graph-api/#/users/ListUsers) for examples in the [developer documentation](https://owncloud.dev). Note that you can use `and` and `or` to refine results.

## Sequence Diagram

The following image gives an overview of the scenario when a client requests to list available spaces the user has access to. To do so, the client is directed with his request automatically via the proxy service to the graph service.

<!-- referencing: https://github.com/owncloud/ocis/pull/3816 ([docs-only] add client protocol overview) -->
<!-- The image source needs to be the raw source !! -->

<img src="https://raw.githubusercontent.com/owncloud/ocis/master/services/graph/images/mermaid-graph.svg" width="500" />

## Caching

The `graph` service can use a configured store via `GRAPH_CACHE_STORE`. Possible stores are:
  -   `memory`: Basic in-memory store and the default.
  -   `redis-sentinel`: Stores data in a configured Redis Sentinel cluster.
  -   `nats-js-kv`: Stores data using key-value-store feature of [nats jetstream](https://docs.nats.io/nats-concepts/jetstream/key-value-store)
  -   `noop`: Stores nothing. Useful for testing. Not recommended in production environments.
  -   `ocmem`: Advanced in-memory store allowing max size. (deprecated)
  -   `redis`: Stores data in a configured Redis cluster. (deprecated)
  -   `etcd`: Stores data in a configured etcd cluster. (deprecated)
  -   `nats-js`: Stores data using object-store feature of [nats jetstream](https://docs.nats.io/nats-concepts/jetstream/obj_store) (deprecated)

Other store types may work but are not supported currently.

Note: The service can only be scaled if not using `memory` store and the stores are configured identically over all instances!

Note that if you have used one of the deprecated stores, you should reconfigure to one of the supported ones as the deprecated stores will be removed in a later version.

Store specific notes:
  -   When using `redis-sentinel`, the Redis master to use is configured via e.g. `OCIS_CACHE_STORE_NODES` in the form of `<sentinel-host>:<sentinel-port>/<redis-master>` like `10.10.0.200:26379/mymaster`.
  -   When using `nats-js-kv` it is recommended to set `OCIS_CACHE_STORE_NODES` to the same value as `OCIS_EVENTS_ENDPOINT`. That way the cache uses the same nats instance as the event bus.
  -   When using the `nats-js-kv` store, it is possible to set `OCIS_CACHE_DISABLE_PERSISTENCE` to instruct nats to not persist cache data on disc.

## Keycloak Configuration For The Personal Data Export

If Keycloak is used for authentication, GDPR regulations require to add all personal identifiable information that Keycloak has about the user to the personal data export. To do this, the following environment variables must be set:

*   `OCIS_KEYCLOAK_BASE_PATH` - The URL to the keycloak instance.
*   `OCIS_KEYCLOAK_CLIENT_ID` - The client ID of the client that is used to authenticate with keycloak, this client has to be able to list users and get the credential data.
*   `OCIS_KEYCLOAK_CLIENT_SECRET` - The client secret of the client that is used to authenticate with keycloak.
*   `OCIS_KEYCLOAK_CLIENT_REALM` - The realm the client is defined in.
*   `OCIS_KEYCLOAK_USER_REALM` - The realm the oCIS users are defined in.
*   `OCIS_KEYCLOAK_INSECURE_SKIP_VERIFY` - If set to true, the TLS certificate of the keycloak instance is not verified.

For more details see the [User-Triggered GDPR Report](https://doc.owncloud.com/ocis/next/deployment/gdpr/gdpr.html) in the ocis admin documentation.

### Keycloak Client Configuration

The client that is used to authenticate with keycloak has to be able to list users and get the credential data. To do this, the following  roles have to be assigned to the client and they have to be about the realm that contains the oCIS users:

*   `view-users`
*   `view-identity-providers`
*   `view-realm`
*   `view-clients`
*   `view-events`
*   `view-authorization`

Note that these roles are only available to assign if the client is in the `master` realm.

## Translations

The `graph` service has embedded translations sourced via transifex to provide a basic set of translated languages. These embedded translations are available for all deployment scenarios. In addition, the service supports custom translations, though it is currently not possible to just add custom translations to embedded ones. If custom translations are configured, the embedded ones are not used. To configure custom translations, the `GRAPH_TRANSLATION_PATH` environment variable needs to point to a base folder that will contain the translation files. This path must be available from all instances of the graph service, a shared storage is recommended. Translation files must be of type  [.po](https://www.gnu.org/software/gettext/manual/html_node/PO-Files.html#PO-Files) or [.mo](https://www.gnu.org/software/gettext/manual/html_node/Binaries.html). For each language, the filename needs to be `graph.po` (or `graph.mo`) and stored in a folder structure defining the language code. In general the path/name pattern for a translation file needs to be:

```text
{GRAPH_TRANSLATION_PATH}/{language-code}/LC_MESSAGES/graph.po
```

The language code pattern is composed of `language[_territory]` where  `language` is the base language and `_territory` is optional and defines a country.

For example, for the language `de`, one needs to place the corresponding translation files to `{GRAPH_TRANSLATION_PATH}/de_DE/LC_MESSAGES/graph.po`.

<!-- also see the notifications readme -->

Important: For the time being, the embedded ownCloud Web frontend only supports the main language code but does not handle any territory. When strings are available in the language code `language_territory`, the web frontend does not see it as it only requests `language`. In consequence, any translations made must exist in the requested `language` to avoid a fallback to the default.

### Translation Rules

*   If a requested language code is not available, the service tries to fall back to the base language if available. For example, if the requested language-code `de_DE` is not available, the service tries to fall back to translations in the `de` folder.
*   If the base language `de` is also not available, the service falls back to the system's default English (`en`),
which is the source of the texts provided by the code.

## Default Language

The default language can be defined via the `OCIS_DEFAULT_LANGUAGE` environment variable. See the `settings` service for a detailed description.
