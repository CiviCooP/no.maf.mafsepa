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
  private $_avtaleDefaultValues = array();

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
    CRM_Utils_System::setTitle(ts('Default Values for new Avtale Giro'));
    $this->setFrequencyUnitList();
    $this->setCampaignList();
    $this->setCollectionDaysList();
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
    $this->add('select', 'default_campaign_id', ts('Default Campaign'), $this->_campaignList, TRUE);
    $this->addMoney('default_max_amount', ts('Default Maximum Amount (NOK)'), TRUE, array(), FALSE);
    $this->addMoney('default_amount', ts('Default Avtale Giro Collection Amount (NOK)'), TRUE, array(), FALSE);
    $this->add('select', 'default_cycle_day', ts('Default Collection Day'), $this->_collectionDaysList, TRUE);
    $this->add('text', 'default_frequency_interval', ts('Default Every'), array(), TRUE);
    $this->add('select', 'default_frequency_unit_id', ts('Default Frequency'), $this->_frequencyUnitList, TRUE);
    $this->addCheckBox('default_notification', ts('Default Notification to Bank'), array('' => '0'), NULL , NULL, FALSE);

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
    CRM_Core_Session::setStatus(ts('Avtale Defaults saved to JSON file avtale_defaults.json in extension folder').' resources.',
      'Defaults saved', 'success');
    parent::postProcess();
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
    $this->saveJsonFile($data);
  }

  /**
   * Method to save json file
   *
   * @param $data
   * @throws Exception when file can not be opened for write
   */
  private function saveJsonFile($data) {
    if (!empty($data)) {
      $container = CRM_Extension_System::singleton()->getFullContainer();
      $fileName = $container->getPath('no.maf.mafsepa').'/resources/avtale_defaults.json';
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
    $avtaleDefaults = CRM_Mafsepa_Utils::readDefaultsJson('avtale_defaults');
    foreach ($this->_avtaleDefaultValues as $key => $value) {
      if (isset($avtaleDefaults[$value])) {
        $defaults[$key] = $avtaleDefaults[$value];
      }
    }
    return $defaults;
  }

}
