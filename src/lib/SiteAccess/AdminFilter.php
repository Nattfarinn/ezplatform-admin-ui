<?php

namespace EzPlatformAdminUi\SiteAccess;

use eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessConfigurationFilter;

class AdminFilter implements SiteAccessConfigurationFilter
{
    const ADMIN_GROUP_NAME = 'admin_group';

    /**
     * Receives the siteaccess configuration array and returns it.
     *
     * @param array $siteAccessConfiguration
     *        The SiteAccess configuration array before it gets normalized and processed.
     *        Keys: groups, list, default_siteaccess.
     *        Example:
     *        ```
     *        [
     *            'list' => ['site'],
     *            'groups' => ['site_group' => ['site']],
     *            'default_siteaccess' => 'site',
     *            'match' => ['URIElement' => 1]
     *        ]
     *        ```
     *
     * @return array The modified siteaccess configuration array
     */
    public function filter(array $configuration)
    {
        $isMultisite = count($configuration['groups']) > 1;

        foreach (array_keys($configuration['groups']) as $groupName) {
            $adminSiteAccessName = $isMultisite
                ? str_replace('_group', '', $groupName) . '_admin'
                : 'admin';
            $configuration['list'][] = $adminSiteAccessName;
            $configuration['groups'][$groupName][] = $adminSiteAccessName;
            $configuration['groups'][self::ADMIN_GROUP_NAME][] = $adminSiteAccessName;
        }

        return $configuration;
    }
}