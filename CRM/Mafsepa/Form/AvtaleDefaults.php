<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Mafsepa_Form_AvtaleDefaults extends CRM_Core_Form {

  private $_frequencyUnitList = array();
  private $_campaignList = array();
  private $_collectionDaysList = array();
  private $_employeesList = array();
  private $_avtaleDefaultValues = array();

  /**
   * Method to set the list of employees
   */
  private function setEmployeesList() {
    try {
      $relationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Employee of',
        'name_b_a' => 'Employer of',
        'return' => 'id'
      ));
      $employees = civicrm_api3('Relationship', 'get', array(
        'relationship_type_id' => $relationshipTypeId,
        'contact_id_b' => 1,
        'is_active' => 1,
        'options' => array('limit' => 0,),
      ));
      foreach ($employees['values'] as $relationshipId => $relationship) {
        $contactName = civicrm_api3('Contact', 'getvalue', array(
          'id' => $relationship['contact_id_a'],
          'return' => 'display_name',
        ));
        $this->_employeesList[$relationship['contact_id_a']] = $contactName;
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
  }

  /**
   * Method to set the list of collection days (based on SEPA setting)
   */
  private function setCollectionDaysList() {
    try {
      $cycleDays = civicrm_api3('Setting', 'getvalue', array('name' => 'cycledays',));
      if (!empty($cycleDays)) {
        $values = explode(',', $cycleDays);
        foreach ($values as $value) {
          $this->_collectionDaysList[$value] = $value;
        }
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
  }

  /**
   * Method to set the list of frequency units
   */
  private function setFrequencyUnitList() {
    try {
      $optionValues = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'recur_frequency_units',
        'is_active' => 1,
        'options' > array('limit' => 0),
      ));
      foreach ($optionValues['values'] as $optionValue) {
        $this->_frequencyUnitList[$optionValue['value']] = $optionValue['label'];
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    asort($this->_frequencyUnitList);
  }

  /**
   * Method to set the list of fundraising campaigns
   */
  private function setCampaignList() {
    $config = CRM_Mafsepa_Config::singleton();
    try {
      $campaigns = civicrm_api3('Campaign', 'get', array(
        'campaign_type_id' => $config->getFundraisingCampaignType(),
        'is_active' => 1,
        'options' => array('limit' => 0,),
      ));
      foreach ($campaigns['values'] as $campaign) {
        $this->_campaignList[$campaign['id']] = $campaign['title'];
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    asort($this->_campaignList);
  }


  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    CRM_Utils_System::setTitle(ts('Default Values for new Avtale Giro and for OCR Export'));
    $this->setFrequencyUnitList();
    $this->setCampaignList();
    $this->setCollectionDaysList();
    $this->setEmployeesList();
    $this->_avtaleDefaultValues = array(
      'default_campaign_id' => 'campaign_id',
      'default_max_amount' => 'max_amount',
      'default_amount' => 'amount',
      'default_cycle_day' => 'cycle_day',
      'default_frequency_interval' => 'frequency_interval',
      'default_frequency_unit_id' => 'frequency_unit',
      'default_notification' => 'notification',
    );
  }

  /**
   * Overridden parent method to build form
   */
  public function buildQuickForm() {
    // add form elements for avtale giro defaults
    $this->add('select', 'default_campaign_id', ts('Default Campaign'), $this->_campaignList, TRUE);
    $this->addMoney('default_max_amount', ts('Default Maximum Amount (NOK)'), TRUE, array(), FALSE);
    $this->addMoney('default_amount', ts('Default Avtale Giro Collection Amount (NOK)'), TRUE, array(), FALSE);
    $this->add('select', 'default_cycle_day', ts('Default Collection Day'), $this->_collectionDaysList, TRUE);
    $this->add('text', 'default_frequency_interval', ts('Default Every'), array(), TRUE);
    $this->add('select', 'default_frequency_unit_id', ts('Default Frequency'), $this->_frequencyUnitList, TRUE);
    $this->addCheckBox('default_notification', ts('Default Notification to Bank'), array('' => '0'), NULL , NULL, FALSE);
    // add form elements for ocr export defaults
    $this->add('text', 'nets_customer_id', ts('NETS Customer ID'), array(), TRUE);
    $this->add('text', 'nets_id', ts('NETS ID'), array(), TRUE);
    $this->add('text', 'format_code', ts('Format Code'), array(), TRUE);
    $this->add('text', 'start_service_code', ts('Start Service Code'), array(), TRUE);
    $this->add('text', 'start_transmission_type', ts('Start Transmission Type'), array(), TRUE);
    $this->add('text', 'start_record_type', ts('Start Record Type'), array(), TRUE);
    $this->add('text', 'end_service_code', ts('End Service Code'), array(), TRUE);
    $this->add('text', 'end_transmission_type', ts('End Transmission Type'), array(), TRUE);
    $this->add('text', 'end_record_type', ts('End Record Type'), array(), TRUE);
    $this->add('text', 'assignment_account', ts('Assignment Account'), array(), TRUE);
    $this->add('text', 'avtale_giro_service_code', ts('AvtaleGiro Service Code'), array(), TRUE);
    $this->add('text', 'assignment_record_type', ts('Assignment Record Type'), array(), TRUE);
    $this->add('text', 'with_notification_transaction_type', ts('Notificiation On Transaction Type'), array(), TRUE);
    $this->add('text', 'without_notification_transaction_type', ts('Notificiation Off Transaction Type'), array(), TRUE);
    $this->add('text', 'first_contribution_line_record_type', ts('1st Transaction Line Record Type'), array(), TRUE);
    $this->add('text', 'second_contribution_line_record_type', ts('2nd Transaction Line Record Type'), array(), TRUE);
    $this->add('text', 'end_assignment_line_record_type', ts('End Assignment Line Record Type'), array(), TRUE);
    $this->add('text', 'default_external_ref', ts('Default External Reference'), array(), TRUE);
    $this->add('text', 'membership_external_ref', ts('Membership External Reference'), array(), TRUE);
    $this->add('select', 'activity_assignee_id', ts('Assign Error Activity To'), $this->_employeesList, TRUE);

    // add buttons
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel')),
    ));
    parent::buildQuickForm();
  }

  /**
   * Overridden parent method to set validation rules
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Mafsepa_Form_AvtaleDefaults', 'validateAmount'));
  }

  /**
   * Method to validate amount (can not be greater than max amount)
   *
   * @param $fields
   * @return array|bool
   */
  public static function validateAmount($fields) {
    $errors = array();
    if (isset($fields['default_amount']) && !empty($fields['default_amount'])) {
      if ($fields['default_amount'] > $fields['default_max_amount']) {
        $errors['default_amount'] = ts('Default amount can not be greater than default maximum amount');
        return $errors;
      }
    }
    return TRUE;
  }

  /**
   * Overridden parent method to process submitted form
   */
  public function postProcess() {
    $this->saveAvtaleDefaults();
    $this->saveOcrExportDefaults();
    CRM_Core_Session::setStatus(ts('Avtale and OCR Export Defaults saved to JSON files in extension folder').' resources.',
      'Defaults saved', 'success');
    parent::postProcess();
  }

  /**
   * Method to save the ocr export defaults
   */
  private function saveOcrExportDefaults() {
    $data = array();
    $ignores = array('entryURL', 'qfKey');
    foreach ($this->_submitValues as $submitKey => $submitValue) {
      // if key does not exists in ignores or in avtale defaults
      if (!in_array($submitKey, $ignores) && !array_key_exists($submitKey, $this->_avtaleDefaultValues)) {
        // if key does not start with '_qf'
        if (substr($submitKey, 0, 3) != '_qf') {
          $data[$submitKey] = $submitValue;
        }
      }
    }
    if (!empty($data)) {
      $this->saveJsonFile($data, 'ocr_export_defaults');
    }
  }

  /**
   * Method to save avtale defaults
   *
   */
  private function saveAvtaleDefaults() {
    $data = array();
    foreach ($this->_avtaleDefaultValues as $key => $value) {
      if (isset($this->_submitValues[$key])) {
        if ($key == 'default_notification') {
          $data[$value] = $this->_submitValues[$key][0];
        } else {
          $data[$value] = $this->_submitValues[$key];
        }
      }
    }
    if (!isset($data['notification'])) {
      $data['notification'] = "0";
    }
    $this->saveJsonFile($data, 'avtale_defaults');
  }

  /**
   * Method to save json file
   *
   * @param $data
   * @param $fileName
   * @throws Exception when file can not be opened for write
   */
  private function saveJsonFile($data, $fileName) {
    if (!empty($data)) {
      $container = CRM_Extension_System::singleton()->getFullContainer();
      $fileName = $container->getPath('no.maf.mafsepa').'/resources/'.$fileName.'.json';
      try {
        $fh = fopen($fileName, 'w');
        fwrite($fh, json_encode($data, JSON_PRETTY_PRINT));
        fclose($fh);
      } catch (Exception $ex) {
        throw new Exception('Could not open '.$fileName.', contact your system administrator. Error reported: '
          . $ex->getMessage());
      }
    }

  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  public function setDefaultValues() {
    $defaults = array();
    $this->getAvtaleDefaults($defaults);
    $this->getOcrExportDefaults($defaults);
    return $defaults;
  }

  /**
   * Method to get the ocr export defaults
   *
   * @param $defaults
   */
  private function getOcrExportDefaults(&$defaults) {
    $ocrDefaults = CRM_Mafsepa_Utils::readDefaultsJson('ocr_export_defaults');
    foreach ($ocrDefaults as $ocrDefaultKey => $ocrDefaultValue) {
      $defaults[$ocrDefaultKey] = $ocrDefaultValue;
    }
  }

  /**
   * Method to get the defaults for avtale giro
   *
   * @param $defaults
   */
  private function getAvtaleDefaults(&$defaults) {
    $avtaleDefaults = CRM_Mafsepa_Utils::readDefaultsJson('avtale_defaults');
    foreach ($this->_avtaleDefaultValues as $key => $value) {
      if (isset($avtaleDefaults[$value])) {
        $defaults[$key] = $avtaleDefaults[$value];
      }
    }
  }

}
