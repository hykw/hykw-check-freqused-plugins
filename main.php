<?php
/**
 * @package HYKW check frequently used plugins
 * @version 0.1
 */
/*
Plugin Name: HYKW check frequently used plugins
Plugin URI: https://github.com/hykw/hykw-check-freqused-plugins
Description: 良く使うプラグインのインストール状況をチェックするプラグイン
Author: hitoshi-hayakawa
Version: 0.1
*/

function _hykw_cfp_check_manuallyEdit_files()
{
  $ret = array();

  $plugins = array(
                 array(
                       'WPLite',
                       'wplite/wplite.php',
                       array(
                             179 => 'if (!isset($disabled)) $disabled = false;',
                             ),
                       ),

                 array(
                       'Easy Pie Maintenance Mode',
                       'easy-pie-maintenance-mode/mini-themes/temporarily-closed/css/style.css',
                       array(
                             80 => '# -webkit-transform: rotate(3deg);',
                             81 => '# -moz-transform: rotate(3deg);',
                             ),
                       ),
                 );

  foreach ($plugins as $plugin) {
    $pluginName = $plugin[0];
    $file = sprintf('%s/%s', WP_PLUGIN_DIR, $plugin[1]);
    $patterns = $plugin[2];

    if (!_hykw_cfp_check_Line($file, $patterns)) {
      array_push($ret, $pluginName);
    }
  }

  return $ret;  
}


function _hykw_cfp_check_Line($file, $patterns)
{
  $phps = explode("\n", file_get_contents($file));
  foreach ($patterns as $at => $pattern) {
    $aLine = trim($phps[$at-1]);
    if ($pattern != $aLine) {

      return false;
    }
  }

  return true;
}


function _hykw_cfp_listsArray($array)
{
  $ret = '';
  foreach ($array as $plugin) {
    $ret .= sprintf("<li>%s<br /></li>\n", $plugin);
  }

  return $ret;
}


function hykw_cfp_list()
{
  
  $cfp_plugins = array(
                       # 'プラグイン名', 'ファイル名'
                       array('SMS Security', 'sms-security/main.php'),
                       array('Custom Post Type UI', 'custom-post-type-ui/custom-post-type-ui.php'),
                       array('WP Multibyte Patch', 'wp-multibyte-patch/wp-multibyte-patch.php'),
                       );

  $plugins = get_plugins();
  $names = array();
  foreach ($plugins as $plugin) {
    array_push($names, $plugin['Name']);
  }

  $missingPlugins = array();
  $inactivePlugins = array();

  foreach ($cfp_plugins as $plugins) {
    $pluginname = $plugins[0];
    $pluginfile = $plugins[1];

    # インストールチェック
    if (in_array($pluginname, $names) == false) {
      array_push($missingPlugins, $pluginname);
    }

    # active チェック
    if (!is_plugin_active($pluginfile))
      array_push($inactivePlugins, $pluginname);
  }

  # 手動修正した項目の確認
  $manualPlugins = _hykw_cfp_check_manuallyEdit_files();

  echo "\n<div class='wrap'>\n";

  echo "<h2>Not installed</h2>\n";
  echo "<ul>\n";
  echo _hykw_cfp_listsArray($missingPlugins);
  echo "</ul>\n";

  echo "<h2>Not activated</h2>\n";
  echo "<ul>\n";
  echo _hykw_cfp_listsArray($inactivePlugins);
  echo "</ul>\n";

  echo "<h2>Regression manually customized files</h2>\n";
  echo "<ul>\n";
  echo _hykw_cfp_listsArray($manualPlugins);
  echo "</ul>\n";

  echo "<div>\n";
}


function hykw_cfp_addmenu()
{
  $title = '[hykwcfp] プラグインチェック';

  add_submenu_page('plugins.php', $title, $title, 'administrator', __FILE__, 'hykw_cfp_list');
}

add_action('admin_menu', 'hykw_cfp_addmenu');
