<?php

namespace SimpleSAML\Module\attrauthrestvo\Auth\Process;

use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Logger;
use SimpleSAML\Error\Exception;
use SimpleSAML\Database;
use SimpleSAML\Configuration;
use SimpleSAML\XHTML\Template;

/**
 * COmanage DB authproc filter.
 *
 * Example configuration:
 *
 *    authproc = array(
 *       ...
 *       '60' => array(
 *            'class' => 'attrauthrestvo:COmanageDbClient',
 *            'userIdAttribute' => 'eduPersonUniqueId',
 *            'attributeName' => 'eduPersonEntitlement',
 *            'defaultRoles' => array(
 *               'member',
 *               'vm_operator'
 *           ),
 *           'roleUrnNamespace' => 'urn:mace:example.org',
 *           'roleAuthority' => 'www.example.org',
 *           'legacyEntitlementSyntax' => false,
 *           'legacyRoleUrnNamespace' => 'urn:mace:example.org',
 *           'legacyRoleAuthority' => 'www.example.org',
 *           'voWhitelist' => array(
 *               'vo.example01.org',
 *               'vo.example02.org',
 *           ),
 *       ),
 *
 * @author Nicolas Liampotis <nliam@grnet.gr>
 */
class COmanageDbClient extends ProcessingFilter
{
    // List of SP entity IDs that should be excluded from this filter.
    private $spBlacklist = array();

    private $userIdAttribute = 'eduPersonUniqueId';

    private $attributeName = 'eduPersonEntitlement';

    private $defaultRoles = array();

    private $roleUrnNamespace;

    private $roleAuthority;

    private $legacyEntitlementSyntax = false;

    private $legacyRoleUrnNamespace;

    private $legacyRoleAuthority;

    private $voWhitelist = [];

    private $voQuery = 'SELECT'
        . ' vo_id'
        . ' FROM vo_members'
        . ' WHERE'
        . ' epuid = :epuid'
        . ' AND status = \'Active\'';

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
        assert('is_array($config)');

        if (array_key_exists('userIdAttribute', $config)) {
            if (!is_string($config['userIdAttribute'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'userIdAttribute' not a string literal"
                );
                throw new Exception(
                    "attrauthrestvo configuration error: 'userIdAttribute' not a string literal"
                );
            }
            $this->userIdAttribute = $config['userIdAttribute'];
        }

        if (array_key_exists('attributeName', $config)) {
            if (!is_string($config['attributeName'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'attributeName' not a string literal"
                );
                throw new Error\Exception(
                    "attrauthrestvo configuration error: 'attributeName' not a string literal"
                );
            }
            $this->attributeName = $config['attributeName'];
        }

        if (array_key_exists('spBlacklist', $config)) {
            if (!is_array($config['spBlacklist'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'spBlacklist' not an array"
                );
                throw new Exception(
                    "attrauthrestvo configuration error: 'spBlacklist' not an array"
                );
            }
            $this->spBlacklist = $config['spBlacklist'];
        }

        if (array_key_exists('defaultRoles', $config)) {
            if (!is_array($config['defaultRoles'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'defaultRoles' not an array"
                );
                throw new Error\Exception(
                    "attrauthrestvo configuration error: 'defaultRoles' not an array"
                );
            }
            $this->defaultRoles = $config['defaultRoles'];
        }

        if (array_key_exists('roleUrnNamespace', $config)) {
            if (!is_string($config['roleUrnNamespace'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'roleUrnNamespace' not a string literal"
                );
                throw new Error\Exception(
                    "attrauthrestvo configuration error: 'roleUrnNamespace' not a string literal"
                );
            }
            $this->roleUrnNamespace = $config['roleUrnNamespace'];
        }

        if (array_key_exists('roleAuthority', $config)) {
            if (!is_string($config['roleAuthority'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'roleAuthority' not a string literal"
                );
                throw new Error\Exception(
                    "attrauthrestvo configuration error: 'roleAuthority' not a string literal"
                );
            }
            $this->roleAuthority = $config['roleAuthority'];
        }

        if (array_key_exists('legacyEntitlementSyntax', $config)) {
            if (!is_string($config['legacyEntitlementSyntax'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'legacyEntitlementSyntax' not a string literal"
                );
                throw new Error\Exception(
                    "attrauthrestvo configuration error: 'legacyEntitlementSyntax' not a string literal"
                );
            }
            $this->legacyEntitlementSyntax = $config['legacyEntitlementSyntax'];
        }

        if (array_key_exists('legacyRoleUrnNamespace', $config)) {
            if (!is_string($config['legacyRoleUrnNamespace'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'legacyRoleUrnNamespace' not a string literal"
                );
                throw new Error\Exception(
                    "attrauthrestvo configuration error: 'legacyRoleUrnNamespace' not a string literal"
                );
            }
            $this->legacyRoleUrnNamespace = $config['legacyRoleUrnNamespace'];
        }

        if (array_key_exists('legacyRoleAuthority', $config)) {
            if (!is_string($config['legacyRoleAuthority'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'legacyRoleAuthority' not a string literal"
                );
                throw new Error\Exception(
                    "attrauthrestvo configuration error: 'legacyRoleAuthority' not a string literal"
                );
            }
            $this->legacyRoleAuthority = $config['legacyRoleAuthority'];
        }

        if (array_key_exists('voWhitelist', $config)) {
            if (!is_array($config['voWhitelist'])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'voWhitelist' not an array"
                );
                throw new Error\Exception(
                    "attrauthrestvo configuration error: 'voWhitelist' not an array"
                );
            }
            $this->voWhitelist = $config['voWhitelist'];
        }
    }

    public function process(&$state)
    {
        try {
            assert('is_array($state)');
            if (
                isset($state['SPMetadata']['entityid'])
                && in_array($state['SPMetadata']['entityid'], $this->spBlacklist, true)
            ) {
                Logger::debug(
                    "[attrauthrestvo] process: Skipping blacklisted SP "
                    . var_export($state['SPMetadata']['entityid'], true)
                );
                return;
            }
            if (empty($state['Attributes'][$this->userIdAttribute])) {
                Logger::error(
                    "[attrauthrestvo] Configuration error: 'userIdAttribute' not available"
                );
                throw new Exception(
                    "attrauthrestvo configuration error: 'userIdAttribute' not available"
                );
            }
            $epuid = $state['Attributes'][$this->userIdAttribute][0];
            $vos = $this->getVOs($epuid);
            foreach ($vos as $vo) {
                if (empty($vo['vo_id']) || !in_array($vo['vo_id'], $this->voWhitelist, true)) {
                    continue;
                }
                $voName = $vo['vo_id'];
                if (!array_key_exists($this->attributeName, $state['Attributes'])) {
                    $state['Attributes'][$this->attributeName] = array();
                }
                foreach ($this->defaultRoles as $role) {
                    if ($this->legacyEntitlementSyntax) {
                        $state['Attributes'][$this->attributeName][] =
                            $this->legacyRoleUrnNamespace   // URN namespace
                            . $this->legacyRoleAuthority    // AA FQDN
                            . $role                         // role
                            . "@"                           // AT
                            . urlencode($voName);           // VO
                    }
                    $state['Attributes'][$this->attributeName][] =
                        $this->roleUrnNamespace     // URN namespace
                        . ":group:"                // group
                        . urlencode($voName) . ":" // VO
                        . "role=" . $role . "#"    // role
                        . $this->roleAuthority;    // AA FQDN
                }
            }
        } catch (\Exception $e) {
            $this->showException($e);
        }
    }

    private function getVOs($epuid)
    {
        Logger::debug("[attrauthrestvo] getVOs: epuid="
            . var_export($epuid, true));

        $result = array();
        $db = Database::getInstance();
        $queryParams = array(
            'epuid' => array($epuid, PDO::PARAM_STR),
        );
        $stmt = $db->read($this->voQuery, $queryParams);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
            Logger::debug(
                "[attrauthrestvo] getVOs: result=" . var_export($result, true)
            );
            return $result;
        } else {
            throw new Exception(
                'Failed to communicate with COmanage Registry: ' . var_export($db->getLastError(), true)
            );
        }

        return $result;
    }

    private function showException($e)
    {
        $globalConfig = Configuration::getInstance();
        $t = new Template($globalConfig, 'attrauthrestvo:exception.tpl.php');
        $t->data['e'] = $e->getMessage();
        $t->show();
        exit();
    }
}
