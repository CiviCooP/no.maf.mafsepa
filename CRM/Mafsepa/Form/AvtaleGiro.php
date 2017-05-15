<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Mafsepa_Form_AvtaleGiro extends CRM_Core_Form {
  private $_bankAccountList = array();
  private $_contactId = NULL;
  private $_frequencyUnitList = array();
  private $_monthFrequencyUnitId = NULL;
  private $_campaignList = array();
  private $_collectionDaysList = array();
  private $_defaultAmount = NULL;
  private $_recurId = NULL;
  private $_avtaleGiro = array();

  /**
   * Method to set the list of bank accounts for the contact
   */
  private function setBankAccountList() {
    $this->_bankAccountList[0] = '- unknown -';
    // first get BankingAccounts for contact
    try {
      $bankingAccounts = civicrm_api3('BankingAccount', 'get' , array(
        'contact_id' => $this->_contactId,
        'options' => array('limit' => 0),
      ));
      foreach ($bankingAccounts['values'] as $bankingAccountId => $bankingAccount) {
        // now get reference to find bank account and add to list if not exist yet
        if (!empty($bankingAccountId)) {
          $bankingAccountReferences = civicrm_api3('BankingAccountReference', 'get', array(
            'ba_id' => $bankingAccountId,
            'options' => array('limit' => 0),
          ));
          foreach ($bankingAccountReferences['values'] as $bankingAccountReferenceId => $bankingAccountReference) {
            if (!in_array($bankingAccountReference['reference'], $this->_bankAccountList)) {
              $this->_bankAccountList[] = $bankingAccountReference['reference'];
            }
          }
        }
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
    $session = CRM_Core_Session::singleton();
    $requestValues = CRM_Utils_Request::exportValues();
    // if recurId is passed in request as rid, save and retrieve current avtaleGiro
    if (isset($requestValues['rid'])) {
      $this->_recurId = $requestValues['rid'];
      $avtaleGiro = new CRM_Mafsepa_AvtaleGiro();
      $this->_avtaleGiro = $avtaleGiro->getAvtaleGiroForRecur($this->_recurId);
      if (isset($this->_avtaleGiro['contact_id'])) {
        $this->_contactId = $this->_avtaleGiro['contact_id'];
      }
      // if action is delete, delete immediately and return to summary
      if ($this->_action == CRM_Core_Action::DELETE) {
        $this->processDelete($avtaleGiro);
      }
    } else {
      if (isset($requestValues['cid'])) {
        $this->_contactId = $requestValues['cid'];
      }
    }
    if (!empty($this->_contactId)) {
      $session->pushUserContext(CRM_Utils_System::url('civicrm/contact/view', 'reset=1&selectedChild=contribute&cid=' . $this->_contactId, true));
    }
    $this->_monthFrequencyUnitId = 'month';
    $this->_defaultAmount = 250;
    $contactName = NULL;
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $actionTitle = 'Add';
        break;
      case CRM_Core_Action::UPDATE:
        $actionTitle = 'Edit';
        break;
      default:
        $actionTitle = '';
        break;
    }
    try {
      $contactName = civicrm_api3('Contact', 'getvalue', array('id' => $this->_contactId, 'return' => 'display_name',));
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    if ($contactName) {
      CRM_Utils_System::setTitle(ts(trim($actionTitle. ' Avtale Giro for ' . $contactName)));
    } else {
      CRM_Utils_System::setTitle(ts(trim($actionTitle. ' Avtale Giro')));
    }
    // not yet, only with issue <https://civicoop.plan.io/issues/1476>
    //$this->setBankAccountList();

    $this->setFrequencyUnitList();
    $this->setCampaignList();
    $this->setCollectionDaysList();
  }

  /**
   * Overridden parent method to build form
   */
  public function buildQuickForm() {
    // add form elements
    $this->add('hidden', 'contact_id');
    $this->add('hidden', 'recur_id');
    $this->add('select', 'campaign_id', ts('Campaign'), $this->_campaignList, TRUE);
    $this->addMoney('max_amount', ts('Maximum Amount (NOK)'), TRUE, array(), FALSE);
    $this->addMoney('amount', ts('Avtale Giro Collection Amount (NOK)'), TRUE, array(), FALSE);
    $this->add('select', 'cycle_day', ts('Collection Day'), $this->_collectionDaysList, TRUE);
    $now = new DateTime();
    $minDate = new DateTime($now->format('Y').'-01-01');
    // start date only if add
    if ($this->_action == CRM_Core_Action::ADD) {
      $this->add('datepicker', 'start_date', ts('Start Date'), array(), TRUE,
        array('time' => FALSE, 'date' => 'dd-mm-yy', 'minDate' => $minDate->format('Y-m-d')));
    }
    // end date only for edit
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $this->add('datepicker', 'end_date', ts('End Date'), array(), FALSE,
        array('time' => FALSE, 'date' => 'dd-mm-yy', 'minDate' => $minDate->format('Y-m-d')));
    }
    $this->add('text', 'frequency_interval', ts('Every'), array('maxlength' => 1), TRUE);
    $this->add('select', 'frequency_unit_id', ts('Frequency'), $this->_frequencyUnitList, TRUE);
    $this->addCheckBox('notification', ts('Notification to Bank'), array('' => '0'), NULL , NULL, FALSE);
    // not yet, only with issue <https://civicoop.plan.io/issues/1476>
    //$this->add('select', 'avtale_giro_account', ts('Bank Account'), $this->_bankAccountList, FALSE);

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
    $this->addFormRule(array('CRM_Mafsepa_Form_AvtaleGiro', 'validateAmount'));
  }

  /**
   * Method to validate amount (can not be greater than max amount)
   *
   * @param $fields
   * @return array|bool
   */
  public static function validateAmount($fields) {
    $errors = array();
    if (isset($fields['amount']) && !empty($fields['amount'])) {
      if ($fields['amount'] > $fields['max_amount']) {
        $errors['amount'] = ts('Amount can not be greater than maximum amount');
        return $errors;
      }
    }
    return TRUE;
  }

  /**
   * Overridden parent method to process submitted form
   */
  public function postProcess() {
    if (isset($this->_submitValues['contact_id'])) {
      $this->_contactId = $this->_submitValues['contact_id'];
    }
    if (isset($this->_submitValues['recur_id'])) {
      $this->_recurId = $this->_submitValues['recur_id'];
    }
    try {
      $contactName = civicrm_api3('Contact', 'getvalue', array(
        'id' => $this->_contactId,
        'return' => 'display_name',));
    }
    catch (CiviCRM_API3_Exception $ex) {
      $contactName = NULL;
    }
    // if end date set, terminate mandate
    if (isset($this->_submitValues['end_date']) && !empty($this->_submitValues['end_date'])) {
      $this->terminateMandate();
      $endDate = new DateTime($this->_submitValues['end_date']);
      CRM_Core_Session::setStatus('Ended Avtale Giro for ' . $contactName.' on '.$endDate->format('d-m-Y'), 'Avtale Giro ended', 'success');
    } else {
      if (empty($this->_avtaleGiro) && $this->_action != CRM_Core_Action::ADD) {
        $currentAvtaleGiro = new CRM_Mafsepa_AvtaleGiro();
        $this->_avtaleGiro = $currentAvtaleGiro->getAvtaleGiroForRecur($this->_recurId);
      }
      // check if mandate and/or avtale giro need to be created / updated
      $mandateRequired = $this->checkMandateRequired();
      $avtaleGiroRequired = $this->checkAvtaleGiroRequired();
      // set mandate data and create or update mandate
      if ($mandateRequired) {
        $mandateData = $this->setMandateData();
        $mandate = $this->saveSepaMandate($mandateData);
        // set kid and recurId in add mode
        if ($this->_action == CRM_Core_Action::ADD) {
          $this->_avtaleGiro['kid'] = $mandate['reference'];
          $this->_recurId = $mandate['entity_id'];
        }
      }
      // set avtale giro data and create or update avtale giro
      if ($avtaleGiroRequired) {
        // save AvtaleGiro
        $avtaleGiroData = $this->setAvtaleGiroData();
        $this->saveAvtaleGiro($avtaleGiroData);
      }
      CRM_Core_Session::setStatus('Saved Avtale Giro for ' . $contactName, 'Avtale Giro saved', 'success');
    }
    parent::postProcess();
  }
  private function terminateMandate() {
    // get mandate id with recur id - api SepaMandate does not allow getvalue with entity_id so with SQL
    $sql = 'SELECT id FROM civicrm_sdd_mandate WHERE entity_table = %1 AND entity_id = %2';
    $mandateId = CRM_Core_DAO::singleValueQuery($sql, array(
      1 => array('civicrm_contribution_recur', 'String'),
      2 => array($this->_recurId, 'Integer'),
    ));
    if ($mandateId) {
      CRM_Sepa_BAO_SEPAMandate::terminateMandate($mandateId, $this->_submitValues['end_date'], 'End Date in UI');
    }
  }

  /**
   * Method to determine if the mandate needs creating or updating
   *
   * @return bool
   */
  private function checkMandateRequired() {
    // always required if add
    if ($this->_action == CRM_Core_Action::ADD) {
      return TRUE;
    }
    // if edit, check if anything changed that requires mandate update
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $mandateChanges = array('amount', 'campaign_id', 'cycle_day', 'end_date', 'frequency_interval', 'frequency_unit',);
      foreach ($mandateChanges as $mandateChange) {
        if (isset($this->_submitValues[$mandateChange]) && $this->_submitValues[$mandateChange] != $this->_avtaleGiro[$mandateChange]) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Method to determine if the avtale giro custom data needs creating or updating
   *
   * @return bool
   */
  private function checkAvtaleGiroRequired() {
    // always required if add
    if ($this->_action == CRM_Core_Action::ADD) {
      return TRUE;
    }
    if ($this->_action == CRM_Core_Action::UPDATE) {
      // max amount changed
      if (isset($this->_submitValues['max_amount']) && $this->_submitValues['max_amount'] != $this->_avtaleGiro['max_amount']) {
        return TRUE;
      }
      // notification
      if (!isset($this->_submitValues['notification']) && $this->_avtaleGiro['notification'] != 0) {
        return TRUE;
      }
      if (isset($this->_submitValues['notification']) && $this->_avtaleGiro['notification'] != $this->_submitValues['notification']) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Method to create a SEPA mandate for AvtaleGiro with API
   *
   * @param $mandateData
   * @return array
   * @throws Exception when error from API SepaMandate createfull
   */
  private function saveSepaMandate($mandateData) {
    try {
      $created = civicrm_api3('SepaMandate', 'createfull', $mandateData);
      return $created['values'][$created['id']];
    }
    catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create Avtale Giro in '.__METHOD__
        .', contact your system administrator. Error from API SepaMandate createfull: '.$ex->getMessage());
    }
  }

  /**
   * Method to set SEPA mandata data
   *
   * @return array
   * @throws Exception when method for default creditor not found, when no creditor found or error from API
   */
  private function setMandateData() {
    if (!method_exists('CRM_Sepa_Logic_Settings', 'defaultCreditor')) {
      throw new Exception('Could not find method CRM_Sepa_Logic_Settings::defaultCreditor to find a default creditor in '
        .__METHOD__.', contact your system administrator!');
    }
    $creditor = CRM_Sepa_Logic_Settings::defaultCreditor();
    if (empty($creditor)) {
      throw new Exception('Could not find a default creditor in '.__METHOD__.', add one in your CiviSepa Settings.');
    }
    $config = CRM_Mafsepa_Config::singleton();
    try {
      $kid = civicrm_api3('Kid', 'generate', array(
        'contact_id' => $this->_contactId,
        'campaign_id' => $this->_submitValues['campaign_id'],
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
      $kid['kid_number'] = 'temp_kid';
    }
    $mandateData = array(
      'creditor_id' => $creditor->creditor_id,
      'contact_id' => $this->_contactId,
      'financial_type_id' => $config->getDefaultMandateFinancialTypeId(),
      'status' => $config->getDefaultMandateStatus(),
      'type' => $config->getDefaultMandateType(),
      'currency' => $config->getDefaultMandateCurrency(),
      'source' => 'Avtale Giro',
      'reference' => $kid['kid_number'],
      'kid' => $kid['kid_number'],
      'frequency_interval' => $this->_submitValues['frequency_interval'],
      'frequency_unit' => $this->_submitValues['frequency_unit_id'],
      'amount' => $this->_submitValues['amount'],
      'campaign_id' => $this->_submitValues['campaign_id'],
      'cycle_day' => $this->_submitValues['cycle_day'],
    );
    // when edit, set mandate id
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $mandateData['id'] = $this->_avtaleGiro['mandate_id'];
    }
    // when add, set start date
    if ($this->_action == CRM_Core_Action::ADD) {
      $startDate = new DateTime($this->_submitValues['start_date']);
      $mandateData['start_date'] = $startDate->format('d-m-Y');
    }
    return $mandateData;
  }

  /**
   * Method to set avtale giro data
   *
   * @return array
   */
  private function setAvtaleGiroData() {
    $avtaleGiroData = array(
      'max_amount' => $this->_submitValues['max_amount'],
      'entity_id' => $this->_recurId,
    );
    if (isset($this->_submitValues['notification'])) {
      $avtaleGiroData['notification'] = 1;
    } else {
      $avtaleGiroData['notification'] = 0;
    }
    return $avtaleGiroData;
  }

  /**
   * Method to set avtale giro data
   *
   * @param $avtaleGiroData
   */
  private function saveAvtaleGiro($avtaleGiroData) {
    $config = CRM_Mafsepa_Config::singleton();
    $tableName = $config->getAvtaleGiroCustomGroup('table_name');
    $maxAmountCustomField = $config->getAvtaleGiroCustomField('maf_maximum_amount');
    $notificationCustomField = $config->getAvtaleGiroCustomField('maf_notification_bank');
    // insert on add
    if ($this->_action == CRM_Core_Action::ADD) {
      $sql = 'INSERT INTO ' . $tableName . ' (entity_id, ' . $maxAmountCustomField['column_name'] . ', ' . $notificationCustomField['column_name']
        . ') VALUES(%1, %2, %3)';
      $sqlParams = array(
        1 => array($avtaleGiroData['entity_id'], 'Integer',),
        2 => array($avtaleGiroData['max_amount'], 'Money',),
        3 => array($avtaleGiroData['notification'], 'Integer',),);
      CRM_Core_DAO::executeQuery($sql, $sqlParams);
    }
    // update on edit
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $sql = 'UPDATE ' . $tableName . ' SET '. $maxAmountCustomField['column_name']. ' = %1, ' . $notificationCustomField['column_name']
        . ' = %2 WHERE entity_id = %3';
      $sqlParams = array(
        1 => array($avtaleGiroData['max_amount'], 'Money',),
        2 => array($avtaleGiroData['notification'], 'Integer',),
        3 => array($avtaleGiroData['entity_id'], 'Integer',),);
      CRM_Core_DAO::executeQuery($sql, $sqlParams);
    }
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['contact_id'] = $this->_contactId;
    $defaults['recur_id'] = $this->_recurId;
    $now = new DateTime();
    // defaults for add
    if ($this->_action == CRM_Core_Action::ADD) {
      $defaults['start_date'] = $now->format('Y-m-d');
      $defaults['max_amount'] = $this->_defaultAmount;
      $defaults['amount'] = $this->_defaultAmount;
      $defaults['frequency_interval'] = 1;
      $defaults['frequency_unit_id'] = $this->_monthFrequencyUnitId;
      $defaults['notification'] = 0;
    }
    // defaults for edit
    if ($this->_action == CRM_Core_Action::UPDATE) {
      if (isset($this->_avtaleGiro['campaign_id']) && !empty($this->_avtaleGiro['campaign_id'])) {
        $defaults['campaign_id'] = $this->_avtaleGiro['campaign_id'];
      }
      if (isset($this->_avtaleGiro['end_date'])) {
        $endDate = new DateTime($this->_avtaleGiro['end_date']);
        $defaults['end_date'] = $endDate->format('Y-m-d');
      }
      if (isset($this->_avtaleGiro['max_amount'])) {
        $defaults['max_amount'] = $this->_avtaleGiro['max_amount'];
      } else {
        $defaults['max_amount'] = $this->_defaultAmount;
      }
      if (isset($this->_avtaleGiro['amount'])) {
        $defaults['amount'] = $this->_avtaleGiro['amount'];
      } else {
        $defaults['amount'] = $this->_defaultAmount;
      }
      if (isset($this->_avtaleGiro['frequency_interval'])) {
        $defaults['frequency_interval'] = $this->_avtaleGiro['frequency_interval'];
      } else {
        $defaults['frequency_interval'] = 1;
      }
      if (isset($this->_avtaleGiro['frequency_unit'])) {
        $defaults['frequency_unit_id'] = $this->_avtaleGiro['frequency_unit'];
      } else {
        $defaults['frequency_unit_id'] = $this->_monthFrequencyUnitId;
      }
      if (isset($this->_avtaleGiro['notification'])) {
        $defaults['notification'] = $this->_avtaleGiro['notification'];
      } else {
        $defaults['notification'] = 0;
      }
      if (isset($this->_avtaleGiro['cycle_day'])) {
        $defaults['cycle_day'] = CRM_Utils_Array::key($this->_avtaleGiro['cycle_day'], $this->_collectionDaysList);
      } else {
        $defaults['cycle_day'] = array_shift($this->_collectionDaysList);
      }
    }
    return $defaults;
  }

  /**
   * Method to process delete of avtale giro
   *
   * @param $avtaleGiro
   */
  private function processDelete($avtaleGiro) {
    $session = CRM_Core_Session::singleton();
    $avtaleGiro->deleteWithRecurringId($this->_recurId);
    CRM_Core_Session::setStatus('Inactive Avtale Giro deleted from database', 'Avtale Giro deleted', 'success');
    if (empty($this->_contactId)) {
      $this->_contactId = $session->get('userID');
    }
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', 'reset=1&selectedChild=contribute&cid=' . $this->_contactId, true));
  }

}
