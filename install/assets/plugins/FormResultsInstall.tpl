/**
 * FormResults Installer
 *
 * FormResults installer
 *
 * @category    plugin
 * @author      mnoskov
 * @internal    @events OnWebPageInit,OnManagerPageInit
 */
//<?php

$modx->clearCache('full');
$table = $modx->getFullTablename('form_results');

$modx->db->query("
    CREATE TABLE IF NOT EXISTS $table (
      `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `form_id` varchar(255) NOT NULL,
      `fields` mediumtext NOT NULL,
      `files` text NOT NULL,
      `status_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) DEFAULT CHARSET=utf8mb4;
");

// remove installer
$tablePlugins = $modx->getFullTablename('site_plugins');
$tableEvents  = $modx->getFullTablename('site_plugin_events');

$query = $modx->db->select('id', $tablePlugins, "`name` = '" . $modx->event->activePlugin . "'");

if ($id = $modx->db->getValue($query)) {
   $modx->db->delete($tablePlugins, "`id` = '$id'");
   $modx->db->delete($tableEvents, "`pluginid` = '$id'");
}
