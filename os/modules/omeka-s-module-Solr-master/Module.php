<?php

/*
 * Copyright BibLibre, 2016
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software.  You can use, modify and/ or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software's author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user's attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software's suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */

namespace Solr;

use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Omeka\Module\AbstractModule;
use Omeka\Module\Exception\ModuleCannotInstallException;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, 'Solr\Api\Adapter\SolrFieldAdapter');
        $acl->allow(null, 'Solr\Api\Adapter\SolrNodeAdapter');
        $acl->allow(null, 'Solr\Api\Adapter\SolrProfileAdapter');
        $acl->allow(null, 'Solr\Api\Adapter\SolrProfileRuleAdapter');
        $acl->allow(null, 'Solr\Entity\SolrNode', 'read');
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $api = $serviceLocator->get('Omeka\ApiManager');

        if (!extension_loaded('solr')) {
            $translator = $serviceLocator->get('MvcTranslator');
            $message = $translator->translate("Solr module requires PHP Solr extension, which is not loaded.");
            throw new ModuleCannotInstallException($message);
        }

        $connection->exec('
            CREATE TABLE IF NOT EXISTS `solr_node` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `settings` text,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');
        $sql = '
            INSERT INTO `solr_node` (`name`, `settings`)
            VALUES ("default", ?)
        ';
        $defaultSettings = $this->getSolrNodeDefaultSettings();
        $connection->executeQuery($sql, [json_encode($defaultSettings)]);
        $solrNodeId = $connection->lastInsertId();

        $connection->exec('
            CREATE TABLE IF NOT EXISTS `solr_field` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `solr_node_id` int(11) unsigned NOT NULL,
                `name` varchar(255) NOT NULL,
                `description` varchar(255) NULL DEFAULT NULL,
                `is_indexed` tinyint(1) NOT NULL DEFAULT 1,
                `is_multivalued` tinyint(1) NOT NULL DEFAULT 1,
                `created` datetime NOT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `solr_field_fk_solr_node_id`
                    FOREIGN KEY (`solr_node_id`) REFERENCES `solr_node` (`id`)
                    ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $connection->exec('
            CREATE TABLE IF NOT EXISTS `solr_profile` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `solr_node_id` int(11) unsigned NOT NULL,
                `resource_name` varchar(255) NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `solr_profile_fk_solr_node_id`
                    FOREIGN KEY (`solr_node_id`) REFERENCES `solr_node` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $connection->exec('
            CREATE TABLE IF NOT EXISTS `solr_profile_rule` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `solr_profile_id` int(11) unsigned NOT NULL,
                `solr_field_id` int(11) unsigned NOT NULL,
                `source` varchar(255) NOT NULL,
                `settings` text,
                PRIMARY KEY (`id`),
                CONSTRAINT `solr_profile_rule_fk_solr_profile_id`
                    FOREIGN KEY (`solr_profile_id`) REFERENCES `solr_profile` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `solr_profile_rule_fk_solr_field_id`
                    FOREIGN KEY (`solr_field_id`) REFERENCES `solr_field` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $titleProperties = $api->search('properties', [
            'term' => 'dcterms:title',
            'limit' => 1,
        ])->getContent();
        $titlePropertyId = $titleProperties[0]->id();

        $solrFields = [
            [
                'name' => 'title_t',
                'description' => 'Title',
                'is_multivalued' => 1,
            ],
            [
                'name' => 'title_s',
                'description' => 'Title',
                'is_multivalued' => 0,
            ],
        ];
        $stmt = $connection->prepare('
            INSERT INTO `solr_field`
            (`solr_node_id`, `name`, `description`, `is_multivalued`, `created`)
            VALUES (?, ?, ?, ?, NOW());
        ');
        foreach ($solrFields as $solrField) {
            $stmt->execute([
                $solrNodeId,
                $solrField['name'],
                $solrField['description'],
                $solrField['is_multivalued'],
            ]);
        }
    }

    public function upgrade($oldVersion, $newVersion,
        ServiceLocatorInterface $serviceLocator)
    {
        $translator = $serviceLocator->get('MvcTranslator');
        $connection = $serviceLocator->get('Omeka\Connection');

        if (version_compare($oldVersion, '0.1.1', '<')) {
            $sql = '
                CREATE TABLE IF NOT EXISTS `solr_node` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `settings` text,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            ';
            $connection->exec($sql);
            $sql = '
                INSERT INTO `solr_node` (`name`, `settings`)
                VALUES ("default", ?)
            ';
            $defaultSettings = $this->getSolrNodeDefaultSettings();
            $connection->executeQuery($sql, [json_encode($defaultSettings)]);
            $solrNodeId = $connection->lastInsertId();

            $sql = '
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = ?
                  AND CONSTRAINT_TYPE = ?
            ';
            $constraintName = $connection->fetchColumn($sql,
                [$connection->getDatabase(), 'solr_field', 'FOREIGN KEY']);

            $connection->exec('
                ALTER TABLE `solr_field`
                CHANGE COLUMN `label` `description` varchar(255) NULL DEFAULT NULL
            ');
            $connection->exec("
                ALTER TABLE `solr_field`
                DROP FOREIGN KEY `$constraintName`
            ");
            $connection->exec('
                ALTER TABLE `solr_field`
                DROP COLUMN `property_id`
            ');

            $connection->exec('
                ALTER TABLE `solr_field`
                ADD COLUMN `solr_node_id` int(11) unsigned NULL AFTER `id`
            ');
            $connection->executeQuery('
                UPDATE `solr_field`
                SET `solr_node_id` = ?
            ', [$solrNodeId]);
            $connection->exec('
                ALTER TABLE `solr_field`
                MODIFY `solr_node_id` int(11) unsigned NOT NULL
            ');

            $connection->exec('
                ALTER TABLE `solr_field`
                ADD CONSTRAINT `solr_field_fk_solr_node_id`
                    FOREIGN KEY (`solr_node_id`) REFERENCES `solr_node` (`id`)
                    ON DELETE RESTRICT ON UPDATE CASCADE
            ');

            $connection->exec('
                CREATE TABLE IF NOT EXISTS `solr_profile` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `solr_node_id` int(11) unsigned NOT NULL,
                    `resource_name` varchar(255) NOT NULL,
                    PRIMARY KEY (`id`),
                    CONSTRAINT `solr_profile_fk_solr_node_id`
                        FOREIGN KEY (`solr_node_id`) REFERENCES `solr_node` (`id`)
                        ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            ');

            $connection->exec('
                CREATE TABLE IF NOT EXISTS `solr_profile_rule` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `solr_profile_id` int(11) unsigned NOT NULL,
                    `solr_field_id` int(11) unsigned NOT NULL,
                    `source` varchar(255) NOT NULL,
                    `settings` text,
                    PRIMARY KEY (`id`),
                    CONSTRAINT `solr_profile_rule_fk_solr_profile_id`
                        FOREIGN KEY (`solr_profile_id`) REFERENCES `solr_profile` (`id`)
                        ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT `solr_profile_rule_fk_solr_field_id`
                        FOREIGN KEY (`solr_field_id`) REFERENCES `solr_field` (`id`)
                        ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            ');
        }
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec('DROP TABLE IF EXISTS `solr_profile_rule`');
        $connection->exec('DROP TABLE IF EXISTS `solr_profile`');
        $connection->exec('DROP TABLE IF EXISTS `solr_field`');
        $connection->exec('DROP TABLE IF EXISTS `solr_node`');
    }

    protected function getSolrNodeDefaultSettings()
    {
        return [
            'client' => [
                'hostname' => 'localhost',
                'port' => 8983,
                'path' => 'solr/default',
            ],
            'resource_name_field' => 'resource_name_s',
        ];
    }
}