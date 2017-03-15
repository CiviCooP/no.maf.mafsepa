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

  /**
   * CRM_Mafsepa_Config constructor.
   */
  function __construct() {
    $this->_mafTxGroupReference = 'MAF-TXG';
    $this->_mafOcrFileReference = 'OCR-';
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
    } catch (CiviCRM_API3_Exception $ex) {}
    // set the assignee contact for the avtale issues activity type (initially Astrid Tholvsen Kristoffersen)
    $this->setAvtaleIssueAssignee();
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