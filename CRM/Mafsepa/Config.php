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
    } catch (CiviCRM_API3_Exception $ex) {}
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