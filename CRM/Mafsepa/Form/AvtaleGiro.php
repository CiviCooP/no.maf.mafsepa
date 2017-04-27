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
    $requestValues = CRM_Utils_Request::exportValues();
    // if recurId is passed in request as rid, save and retrieve current avtaleGiro
    if (isset($requestValues['rid'])) {
      $this->_recurId = $requestValues['rid'];
      $avtaleGiro = new CRM_Mafsepa_AvtaleGiro();
      // if action is delete, delete immediately and return to summary
      if ($this->_action == CRM_Core_Action::DELETE) {
        $avtaleGiro->deleteWithRecurringId($this->_recurId);
        CRM_Core_Session::setStatus('Inactive Avtale Giro deleted from database', 'Avtale Giro deleted', 'success');
        $session = CRM_Core_Session::singleton();
        CRM_Utils_System::redirect($session->readUserContext());
      }
      $this->_avtaleGiro = $avtaleGiro->getAvtaleGiroForRecur($this->_recurId);
    }
    $this->_monthFrequencyUnitId = 'month';
    $this->_defaultAmount = 250;
    $values = CRM_Utils_Request::exportValues();
    $contactName = NULL;
    if (isset($values['cid'])) {
      $this->_contactId = $values['cid'];
      try {
        $contactName = civicrm_api3('Contact', 'getvalue', array('id' => $this->_contactId, 'return' => 'display_name',));
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
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
    if ($contactName) {
      CRM_Utils_System::setTitle(ts(trim($actionTitle. ' Avtale Giro for ' . $contactName)));
    } else {
      CRM_Utils_System::setTitle(ts(trim($actionTitle. ' Avtale Giro')));
    }
    // not yet, only with issue <https://civicoop.plan.io/issues/1476>
    //$this->setBankAccountList();

    $this->setFrequencyUnitList();
    $this->setCampaignList();
  }

  /**
   * Overridden parent method to build form
   */
  public function buildQuickForm() {
    // add form elements
    $this->add('hidden', 'avtale_giro_contact_id');
    $this->add('select', 'avtale_giro_campaign_id', ts('Campaign'), $this->_campaignList, TRUE);
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $this->addMoney('avtale_giro_max_amount', ts('Maximum Amount (NOK)'), TRUE, array('readonly' => 'readonly'), FALSE);
    } else {
      $this->addMoney('avtale_giro_max_amount', ts('Maximum Amount (NOK)'), TRUE, array(), FALSE);
    }
    $this->addMoney('avtale_giro_amount', ts('Avtale Giro Collection Amount (NOK)'), TRUE, array(), FALSE);
    $now = new DateTime();
    $minDate = new DateTime($now->format('Y').'-01-01');
    $this->add('datepicker', 'avtale_giro_start_date', ts('Start Date'), array(), TRUE,
      array('time' => FALSE, 'date' => 'dd-mm-yy', 'minDate' => $minDate->format('Y-m-d')));
    // end date only for edit
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $this->add('datepicker', 'avtale_giro_end_date', ts('End Date'), array(), TRUE,
        array('time' => FALSE, 'date' => 'dd-mm-yy', 'minDate' => $minDate->format('Y-m-d')));
    }
    $this->add('text', 'avtale_giro_frequency_interval', ts('Every'), array('maxlength' => 1), TRUE);
    $this->add('select', 'avtale_giro_frequency_unit_id', ts('Frequency'), $this->_frequencyUnitList, TRUE);
    $this->addCheckBox('avtale_giro_notification', ts('Notification to Bank'), array('' => '0'), NULL , NULL, FALSE);
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
    if (isset($fields['avtale_giro_amount']) && !empty($fields['avtale_giro_amount'])) {
      if ($fields['avtale_giro_amount'] > $fields['avtale_giro_max_amount']) {
        $errors['avtale_giro_amount'] = ts('Amount can not be greater than maximum amount');
        return $errors;
      }
    }
    return TRUE;
  }

  /**
   * Overridden parent method to process submitted form
   */
  public function postProcess() {
    if (isset($this->_submitValues['avtale_giro_contact_id'])) {
      $this->_contactId = $this->_submitValues['avtale_giro_contact_id'];
    }
    // create sepa mandate
    $mandate = $this->createSepaMandate($this->_submitValues);
    if ($mandate) {
      // save AvtaleGiro
      $this->saveAvtaleGiro($this->_submitValues, $mandate);
      try {
        $contactName = civicrm_api3('Contact', 'getvalue', array(
          'id' => $this->_contactId,
          'return' => 'display_name',));
        CRM_Core_Session::setStatus('Created Avtale Giro for ' . $contactName, 'Avtale Giro saved', 'success');
      } catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Session::setStatus('Created Avtale Giro', 'Avtale Giro saved', 'success');
      }
    }
    parent::postProcess();
  }

  /**
   * Method to create a SEPA mandate for AvtaleGiro with API
   *
   * @param $submitValues
   * @return array
   * @throws Exception when method for default creditor not found, when no creditor found or error from API
   */
  private function createSepaMandate($submitValues) {
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
        'campaign_id' => $submitValues['avtale_giro_campaign_id'],
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
      $kid['kid_number'] = 'temp_kid';
    }
    $session = CRM_Core_Session::singleton();
    $startDate = new DateTime($submitValues['avtale_giro_start_date']);
    try {
      $contactName = civicrm_api3('Contact', 'getvalue', array(
        'id' => $session->get('userID'),
        'return' => 'display_name'
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
      $contactName = '';
    }
    $mandateParams = array(
      'creditor_id' => $creditor->creditor_id,
      'contact_id' => $this->_contactId,
      'financial_type_id' => $config->getDefaultMandateFinancialTypeId(),
      'status' => $config->getDefaultMandateStatus(),
      'type' => $config->getDefaultMandateType(),
      'currency' => $config->getDefaultMandateCurrency(),
      'source' => 'added by '.$contactName.'(contact ID '.$session->get('userID'),
      'reference' => $kid['kid_number'],
      'kid' => $kid['kid_number'],
      'frequency_interval' => $submitValues['avtale_giro_frequency_interval'],
      'frequency_unit' => $submitValues['avtale_giro_frequency_unit_id'],
      'amount' => $submitValues['avtale_giro_amount'],
      'start_date' => $startDate->format('d-m-Y'),
      'campaign_id' => $submitValues['avtale_giro_campaign_id'],
    );
    try {
      $created = civicrm_api3('SepaMandate', 'createfull', $mandateParams);
      return $created['values'][$created['id']];
    }
    catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not create Avtale Giro in '.__METHOD__
        .', contact your system administrator. Error from API SepaMandate createfull: '.$ex->getMessage());
    }
  }
  private function saveAvtaleGiro($submitValues, $mandate) {
    $config = CRM_Mafsepa_Config::singleton();
    $tableName = $config->getAvtaleGiroCustomGroup('table_name');
    $maxAmountCustomField = $config->getAvtaleGiroCustomField('maf_maximum_amount');
    $notificationCustomField = $config->getAvtaleGiroCustomField('maf_notification_bank');
    $kidCustomField = $config->getAvtaleGiroCustomField('maf_kid');
    if (isset($submitValues['avtale_giro_notification'])) {
      $notification = 1;
    } else {
      $notification = 0;
    }
    $sql = 'INSERT INTO '.$tableName.' (entity_id, '.$maxAmountCustomField['column_name'].', '.$notificationCustomField['column_name'].
      ', '.$kidCustomField['column_name'].') VALUES(%1, %2, %3, %4)';
    $sqlParams = array(
      1 => array($mandate['entity_id'], 'Integer',),
      2 => array($submitValues['avtale_giro_max_amount'], 'Money',),
      3 => array($notification, 'Integer',),
      4 => array($mandate['reference'], 'String',),);
    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }


  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['avtale_giro_contact_id'] = $this->_contactId;
    $now = new DateTime();
    // defaults for add
    if ($this->_action == CRM_Core_Action::ADD) {
      $defaults['avtale_giro_start_date'] = $now->format('Y-m-d');
      $defaults['avtale_giro_max_amount'] = $this->_defaultAmount;
      $defaults['avtale_giro_amount'] = $this->_defaultAmount;
      $defaults['avtale_giro_frequency_interval'] = 1;
      $defaults['avtale_giro_frequency_unit_id'] = $this->_monthFrequencyUnitId;
      $defaults['avtale_giro_notification'] = 0;
    }
    // defaults for edit
    if ($this->_action == CRM_Core_Action::UPDATE) {
      if (isset($this->_avtaleGiro['campaign_id']) && !empty($this->_avtaleGiro['campaign_id'])) {
        $defaults['avtale_giro_campaign_id'] = $this->_avtaleGiro['campaign_id'];
      }
      if (isset($this->_avtaleGiro['start_date'])) {
        $startDate = new DateTime($this->_avtaleGiro['start_date']);
        $defaults['avtale_giro_start_date'] = $startDate->format('Y-m-d');
      } else {
        $defaults['avtale_giro_start_date'] = $now->format('Y-m-d');
      }
      if (isset($this->_avtaleGiro['end_date'])) {
        $endDate = new DateTime($this->_avtaleGiro['end_date']);
        $defaults['avtale_giro_end_date'] = $endDate->format('Y-m-d');
      }
      if (isset($this->_avtaleGiro['max_amount'])) {
        $defaults['avtale_giro_max_amount'] = $this->_avtaleGiro['max_amount'];
      } else {
        $defaults['avtale_giro_max_amount'] = $this->_defaultAmount;
      }
      if (isset($this->_avtaleGiro['amount'])) {
        $defaults['avtale_giro_amount'] = $this->_avtaleGiro['amount'];
      } else {
        $defaults['avtale_giro_amount'] = $this->_defaultAmount;
      }
      if (isset($this->_avtaleGiro['frequency_interval'])) {
        $defaults['avtale_giro_frequency_interval'] = $this->_avtaleGiro['frequency_interval'];
      } else {
        $defaults['avtale_giro_frequency_interval'] = 1;
      }
      if (isset($this->_avtaleGiro['frequency_unit'])) {
        $defaults['avtale_giro_frequency_unit_id'] = $this->_avtaleGiro['frequency_unit'];
      } else {
        $defaults['avtale_giro_frequency_unit_id'] = $this->_monthFrequencyUnitId;
      }
      if (isset($this->_avtaleGiro['notification'])) {
        $defaults['avtale_giro_notification'] = $this->_avtaleGiro['notification'];
      } else {
        $defaults['avtale_giro_notification'] = 0;
      }
      if (isset($this->_avtaleGiro['notification'])) {
        $defaults['avtale_giro_notification'] = $this->_avtaleGiro['notification'];
      } else {
        $defaults['avtale_giro_notification'] = 0;
      }
    }
    return $defaults;
  }

}
