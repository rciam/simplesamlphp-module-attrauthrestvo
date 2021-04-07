# simplesamlphp-module-attrauthrestvo

A SimpleSAMLphp module for creating entitlements using the user's VO membership
information adding them to the list of attributes received from the identity provider.

In a nutshell, this module provides a set of SimpleSAMLphp authentication
processing filters allowing to use VOMS as an Attribute Authority. Specifically,
the module supports retrieving user's VO membership and role information and
create entitlements, which are encapsulated in `eduPersonEntitlement` attribute
values following the [AARC-G002](https://aarc-community.org/guidelines/aarc-g002/)
specification

The VO membership information must be stored into a SQL table, in order to eliminate
any delay in the login process. The table should contain the following columns:

```sql
CREATE TABLE vo_membership (
    id integer NOT NULL,
    epuid character varying(256) NOT NULL,
    vo_id character varying(256) NOT NULL,
    valid_from timestamp without time zone,
    valid_through timestamp without time zone,
    status character varying(64)
);
```

## COmanage Database Client

The `attrauthrestvo:COmanageDbClient` authentication processing filter is
implemented as a SQL client. This module uses the SimpleSAML\Database library to
connect to the database. To configure the database connection edit the following
attributes in the `config.php`:

```php
    /*
     * Database connection string.
     * Ensure that you have the required PDO database driver installed
     * for your connection string.
     */
    'database.dsn' => 'mysql:host=localhost;dbname=saml',
    /*
     * SQL database credentials
     */
    'database.username' => 'simplesamlphp',
    'database.password' => 'secret',
```

Optionally, you can configure a database slave by editing the `database.slaves`
attribute.

### SimpleSAMLphp configuration

The following authproc filter configuration options are supported:

- `userIdAttribute`: Optional, a string containing the name of the attribute
  whose value to use for querying the COmanage Registry. Defaults to
  `eduPersonUniqueId`.
- `attributeName`: Optional, a string containing the name of the attribute
  whose value to use for storing the generated entitlement. Defaults to
  `eduPersonEntitlement`.
- `defaultRoles`: Optional, an array of strings that contains the roles to
  add to the entitlements.
- `roleUrnNamespace`: Required, a string containing the name of the namespace
  to add to the entitlement in URN format.
- `roleAuthority`: Required, a string containing the name of the group-authority
  to add to the entitlement.
- `legacyEntitlementSyntax`: Optional, a boolean that allows the creation of
  entitlements with alternative syntax. Example entitlement:
  `urn:mace:rciam.org:aai.rciam.org:member@vo.example.org`.
- `legacyRoleUrnNamespace`: Optional, a string containing the name of the alternative
  namespace to add to the entitlement in URN format.
- `legacyRoleAuthority`: Optional, a string containing the name of the alternative
  group-authority to add to the entitlement.
- `spBlacklist`: Optional, an array of strings that contains the SPs that the
  module will skip to process.
- `voWhitelist`: Optional, an array of strings that contains VOs (COUs) for
  which the module will generate entitlements.

Note: In case you need to change the format of the entitlements you need to
modify the source code.

### Example authproc filter configuration

```php
    authproc = array(
        ...
        '60' => array(
            'class' => 'attrauthrestvo:COmanageDbClient',
            'userIdAttribute' => 'eduPersonUniqueId',
            'attributeName' => 'eduPersonEntitlement',
            'defaultRoles' => array(
                'member',
                'vm_operator'
            ),
            'roleUrnNamespace' => 'urn:mace:example.org',
            'roleAuthority' => 'www.example.org',
            'legacyEntitlementSyntax' => false,
            'legacyRoleUrnNamespace' => 'urn:mace:example.org',
            'legacyRoleAuthority' => 'www.example.org',
            'spBlacklist' => array(
                'https://sp1.example.org/entityid',
                'https://sp2.example.org/entityid',
            ),
            'voWhitelist' => array(
                'vo.example01.org',
                'vo.example02.org',
            ),
        ),
```

## Compatibility matrix

This table matches the module version with the supported SimpleSAMLphp version.

| Module | SimpleSAMLphp |
|:------:|:-------------:|
|  v1.0  |     v1.14     |

## License

Licensed under the Apache 2.0 license, for details see `LICENSE`.
