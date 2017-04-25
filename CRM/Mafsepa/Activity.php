<?php

/**
 * Class to process Activity from MAF Sepa perspective
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 14 March 2017
 * @license AGPL-3.0
 */
class CRM_Mafsepa_Activity {

  private $_avtaleIssueActivityTypeId = NULL;

  /**
   * CRM_Mafsepa_Activity constructor.
   */
  function __construct() {
    // get or create the special activity type for avtale giro import and export errors and warnings
    $this->setAvtaleIssueActivityType();
  }

  /**
   * Method to either get or create the activity type for Avtale Issues
   */
  private function setAvtaleIssueActivityType() {
    try {
      $this->_avtaleIssueActivityTypeId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_type',
        'name' => 'maf_avtale_issue',
        'return' => 'value'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      $activity = civicrm_api3('OptionValue', 'create', array(
        'option_group_id' => 'activity_type',
        'label' => ts('Avtale Issue'),
        'name' => 'maf_avtale_issue',
        'description' => 'Special Activity Type for Avtale issues in Import and Export',
        'is_reserved' => 1,
        'is_active' => 1,
        'filter' => 1
      ));
      $this->_avtaleIssueActivityTypeId = $activity['values'][$activity['id']]['value'];
    }
  }

  /**
   * Method to create the Avtale Issue activity
   *
   * @param array $data
   * @return array $activity
   */
  public function create($data) {
    $activity = array();
    $config = CRM_Mafsepa_Config::singleton();
    $params = array(
      'activity_type_id' => $this->_avtaleIssueActivityTypeId,
      'subject' => $data['subject'],
      'status_id' => $config->getScheduledActivityStatusId(),
      'details' => $this->renderTemplate(array(
        'message' => $data['message'],
        'details' => $data['details']
      ))
    );
    try {
      $activity = civicrm_api3('Activity', 'create', $params);
    } catch (CiviCRM_API3_Exception $ex) {}
    return $activity;
  }

  /**
   * Method uses SMARTY to render a template
   *
   * @param $vars
   * @return string
   */
  private function renderTemplate($vars) {
    $smarty = CRM_Core_Smarty::singleton();
    // first backup original variables, since smarty instance is a singleton
    $oldVars = $smarty->get_template_vars();
    $backupFrame = array();
    foreach ($vars as $key => $value) {
      $key = str_replace(' ', '_', $key);
      $backupFrame[$key] = isset($oldVars[$key]) ? $oldVars[$key] : NULL;
    }
    // then assign new variables
    foreach ($vars as $key => $value) {
      $key = str_replace(' ', '_', $key);
      $smarty->assign($key, $value);
    }
    // create result
    $result =  $smarty->fetch('CRM/Mafsepa/ActivityDetails.tpl');
    // reset smarty variables
    foreach ($backupFrame as $key => $value) {
      $key = str_replace(' ', '_', $key);
      $smarty->assign($key, $value);
    }
    return $result;
  }
}