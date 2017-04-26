<?php

require_once 'mafsepa.civix.php';

/**
 * Method to check if the extension org.project60.sepa is installed
 *
 * @return bool
 */
function _is_sepa_installed() {
  $sql = "SELECT COUNT(*) FROM civicrm_extension WHERE full_name = %1 AND is_active = %2";
  $countSepa = CRM_Core_DAO::singleValueQuery($sql, array(
    1 => array('org.project60.sepa', 'String'),
    2 => array(1, 'Integer')
  ));
  if ($countSepa == 1) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Implementation of civicrm_hook pageRun
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 26 April 2017
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pageRun
 * @param $wrappers
 * @param $apiRequest
 */

function mafsepa_civicrm_pageRun($page) {
  $pageName = $page->getVar('_name');
  if ($pageName == 'CRM_Contribute_Page_Tab') {
    // add jQuery to replace Sepa button with Avtale Giro button
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/Mafsepa/AvtaleGiroButton.tpl'));
  }
}

/**
 * Implementation of civicrm_hook apiWrappers
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 8 Feb 2017
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_apiWrappers
 * @param $wrappers
 * @param $apiRequest
 */
function mafsepa_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  $validEntities = array('SepaMandate','SepaTransactionGroup');
  if (in_array($apiRequest['entity'], $validEntities)) {
    $wrappers[] = new CRM_Mafsepa_APIWrapper();
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mafsepa_civicrm_config(&$config) {
  _mafsepa_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mafsepa_civicrm_xmlMenu(&$files) {
  _mafsepa_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mafsepa_civicrm_install() {
  if (_is_sepa_installed() == FALSE) {
    throw new Exception(ts('Required extension SEPA Direct Debit (org.project60.sepa) is not installed, can not enable extension no.maf.mafsepa'));
  }
  _mafsepa_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function mafsepa_civicrm_postInstall() {
  _mafsepa_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mafsepa_civicrm_uninstall() {
  _mafsepa_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mafsepa_civicrm_enable() {
  if (_is_sepa_installed() == FALSE) {
    throw new Exception(ts('Required extension SEPA Direct Debit (org.project60.sepa) is not installed, can not enable extension no.maf.mafsepa'));
  }
  _mafsepa_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mafsepa_civicrm_disable() {
  _mafsepa_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mafsepa_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mafsepa_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mafsepa_civicrm_managed(&$entities) {
  _mafsepa_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mafsepa_civicrm_caseTypes(&$caseTypes) {
  _mafsepa_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mafsepa_civicrm_angularModules(&$angularModules) {
  _mafsepa_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mafsepa_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mafsepa_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function mafsepa_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function mafsepa_civicrm_navigationMenu(&$menu) {
  _mafsepa_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'no.maf.mafsepa')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _mafsepa_civix_navigationMenu($menu);
} // */
