<?php

require_once 'mafsepa.civix.php';

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
  $validEntities = array('SepaMandate', 'SepaTransactionGroup');
  if (in_array($apiRequest['entity'], $validEntities)) {
    $wrappers[] = new CRM_Mafsepa_APIWrapper();
  }
}
/**
 * Add a bit of Javascript Code to the Create Mandate page to hide the Reference Field.
 * @param $page
 */
function mafsepa_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
  if ($pageName == 'CRM_Sepa_Page_CreateMandate') {
    // Add some javascript to the create mandate screen to hide mandate
    // reference and to change the text EUR to NOK as we will force the
    // recurring contributions or contributions to be in the NOK currency.
    // (See for this functionality the pre hook).
    $resources = CRM_Core_Resources::singleton();
    $resources->addScript("
      cj(function() {
        cj('input[name=\"reference\"]').parent().parent().hide();
        var amountCurrencyNode = cj('input[name=\"total_amount\"]').parent().contents().filter(function() {
          if (this.nodeType == Node.TEXT_NODE) {
            this.nodeValue = ' NOK';
          }
          return this.nodeType == Node.TEXT_NODE;
        });
      });
    ");
  }
}

function mafsepa_civicrm_pre($op, $objectName, $id, &$params) {
  if ($op == 'create' && empty($id)) {
    // We only want to force the currency to NOK when the contribution recur or
    // the contribution is created by the user through the create mandate screen.
    // In all other scenario's there is no need to change the currency.
    //
    // The only we to detect we are creating contribution recur or contributions
    // from within the create mandate screen is to check which url we are on.
    $requestValues = CRM_Utils_Request::exportValues();
    if (isset($requestValues['q']) && $requestValues['q'] == 'civicrm/sepa/cmandate') {
      if ($objectName == 'ContributionRecur' && !empty($params['currency']) && $params['currency'] == 'EUR') {
        $params['currency'] = 'NOK';
      }
      elseif ($objectName == 'Contribution' && !empty($params['currency']) && $params['currency'] == 'EUR') {
        $params['currency'] = 'NOK';
      }
    }
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
