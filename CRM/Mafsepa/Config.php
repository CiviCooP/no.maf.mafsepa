<?php

/**
 * Class for MAF AvtaleGiro Configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 March 2017
 * @license AGPL-3.0
 */
class CRM_Mafsepa_Config {
  // property for singleton pattern (caching the config)
  static private $_singleton = NULL;
  // configuration properties
  private $_mafTxGroupReference = NULL;
  private $_mafOcrFileReference = NULL;
  private $_membershipFinancialTypeId = NULL;
  private $_avtaleIssueAssigneeId = NULL;
  private $_scheduledActivityStatusId = NULL;
  private $_defaultMandateFinancialTypeId = NULL;
  private $_defaultMandateStatus = NULL;
  private $_defaultMandateType = NULL;
  private $_defaultMandateCurrency = NULL;
  private $_fundraisingCampaignTypeId = NULL;
  private $_avtaleGiroCustomGroup = array();
  private $_avtaleGiroCustomFields = array();

  /**
   * CRM_Mafsepa_Config constructor.
   */
  function __construct() {
    $this->_mafTxGroupReference = 'MAF-TXG';
    $this->_mafOcrFileReference = 'OCR-';
    $this->_defaultMandateFinancialTypeId = 1;
    $this->_defaultMandateStatus = 'FRST';
    $this->_defaultMandateType = 'RCUR';
    $this->_defaultMandateCurrency = 'NOK';
    $this->setAvtaleGiroCustomData();

    try {
      $this->_membershipFinancialTypeId = civicrm_api3('FinancialType', 'getvalue', array(
        'name' => 'Member Dues',
        'return' => 'id'
      ));
      $this->_scheduledActivityStatusId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_status',
        'name' => 'Scheduled',
        'return' => 'value'
      ));
      $this->_fundraisingCampaignTypeId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'campaign_type',
        'name' => 'Fuel',
        'return' => 'value'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
    // set the assignee contact for the avtale issues activity type (initially Astrid Tholvsen Kristoffersen)
    $this->setAvtaleIssueAssignee();
  }

  /**
   * Getter for avtale giro custom group
   *
   * @param $key
   * @return mixed
   */
  public function getAvtaleGiroCustomGroup($key = NULL) {
    if (empty($key)) {
      return $this->_avtaleGiroCustomGroup;
    } else {
      return $this->_avtaleGiroCustomGroup[$key];
    }
  }

  /**
   * Getter for avtale giro custom fields
   *
   * @param $name
   * @return mixed
   */
  public function getAvtaleGiroCustomField($name = 'all') {
    if ($name == 'all') {
      return $this->_avtaleGiroCustomFields;
    } else {
      foreach ($this->_avtaleGiroCustomFields as $customFieldId => $customField) {
        if ($customField['name'] == $name) {
          return $customField;
        }
      }
    }
    return NULL;
  }

  /**
   * Getter for fundraising campaign type
   *
   * @return mixed
   */
  public function getFundraisingCampaignType() {
    return $this->_fundraisingCampaignTypeId;
  }

  /**
   * Getter for default mandate type
   *
   * @return mixed
   */
  public function getDefaultMandateType() {
    return $this->_defaultMandateType;
  }

  /**
   * Getter for default mandate status
   *
   * @return mixed
   */
  public function getDefaultMandateStatus() {
    return $this->_defaultMandateStatus;
  }

  /**
   * Getter for default mandate currency
   *
   * @return mixed
   */
  public function getDefaultMandateCurrency() {
    return $this->_defaultMandateCurrency;
  }

  /**
   * Getter for default financial type id
   *
   * @return mixed
   */
  public function getDefaultMandateFinancialTypeId() {
    return $this->_defaultMandateFinancialTypeId;
  }

  /**
   * Getter for activity assignee contact id
   *
   * @return mixed
   */
  public function getAvtaleIssueAssigneeId() {
    return $this->_avtaleIssueAssigneeId;
  }

  /**
   * Getter for scheduled activity status id
   *
   * @return mixed
   */
  public function getScheduledActivityStatusId() {
    return $this->_scheduledActivityStatusId;
  }

  /**
   * Getter for membership financial type id
   *
   * @return mixed
   */
  public function getMembershipFinancialTypeId() {
    return $this->_membershipFinancialTypeId;
  }

  /**
   * Getter for maf ocr file reference
   *
   * @return mixed
   */
  public function getOcrFileReference() {
    return $this->_mafOcrFileReference;
  }

  /**
   * Getter for maf transaction group reference
   *
   * @return mixed
   */
  public function getMafTxGroupReference() {
    return $this->_mafTxGroupReference;
  }

  /**
   * Method to set the assignee contact id for the activity Avtale Issue. Initially this will be Astrid Tholvsen
   * Kristoffersen until UI is created
   */
  private function setAvtaleIssueAssignee() {
    try {
      $this->_avtaleIssueAssigneeId = civicrm_api3('Contact', 'getvalue', array(
        'contact_type' => 'Individual',
        'first_name' => 'Astrid',
        'last_name' => 'Tholvsen Kristoffersen',
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to set custom group and custom fields for avtale giro
   * @throws Exception
   */
  private function setAvtaleGiroCustomData() {
    try {
      $this->_avtaleGiroCustomGroup = civicrm_api3('CustomGroup', 'getsingle', array(
        'name' => 'maf_avtale_giro',
        'extends' => 'ContributionRecur'
      ));
      $customFields = civicrm_api3('CustomField', 'get', array(
        'custom_group_id' => 'maf_avtale_giro',
        'options' => array('limit' => 0),
      ));
      $this->_avtaleGiroCustomFields = $customFields['values'];
    }
    catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find custom group and/or custom fields for Avtale Giro in '.__METHOD__
        .', contact your system administrator. Error from API: '.$ex->getMessage());
    }
  }

  /**
   * Function to return singleton object
   *
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Mafsepa_Config();
    }
    return self::$_singleton;
  }
}