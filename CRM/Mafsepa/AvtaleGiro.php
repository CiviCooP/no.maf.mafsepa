<?php

/**
 * Class to process Norwegian AvtaleGiro in the SEPA concept
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 March 2017
 * @license AGPL-3.0
 */
class CRM_Mafsepa_AvtaleGiro {

  // ocr properties
  private $_netsCustomerId = NULL;
  private $_netsId = NULL;
  private $_formatCode = NULL;
  private $_startServiceCode = NULL;
  private $_avtaleGiroServiceCode = NULL;
  private $_endServiceCode = NULL;
  private $_startTransmissionType = NULL;
  private $_endTransmissionType = NULL;
  private $_startRecordType = NULL;
  private $_assignmentRecordType = NULL;
  private $_firstContributionLineRecordType = NULL;
  private $_secondContributionLineRecordType = NULL;
  private $_endAssignmentLineRecordType = NULL;
  private $_endRecordType = NULL;
  private $_fileLines = NULL;
  private $_transmissionNumber = NULL;
  private $_assignmentNumber = NULL;
  private $_assignmentAccount = NULL;
  private $_assignmentCount = NULL;
  private $_assignmentTotal = NULL;
  private $_fileCount = NULL;
  private $_fileTotal = NULL;
  private $_transactionNumber = NULL;
  private $_withNotificationTransactionType = NULL;
  private $_withoutNotificationTransactionType = NULL;
  private $_earliestDate = NULL;
  private $_latestDate = NULL;
  private $_membershipExternalRef = NULL;
  private $_defaultExternalRef = NULL;
  private $_countRecords = NULL;

  /**
   * CRM_Mafsepa_AvtaleGiro constructor.
   *
   * @throws Exception when API Kid generate not found
   */
  function __construct() {
    // error when api kid generate not found!
    try {
      civicrm_api3('Kid', 'generate', array());
    } catch (CiviCRM_API3_Exception $ex) {
      if ($ex->getMessage() == "API (Kid, generate) does not exist (join the API team and implement it!)") {
        throw new Exception('Could not find the API KID Generate which is required to generate the KID in '
          .__METHOD__.', contact your system administrator!');
      }
    }
  }

  /**
   * Set Class properties required for OCR Export
   */
  public function setOCRProperties() {
    // define OCR properties
    $this->_netsCustomerId = '00131936';
    $this->_netsId = '00008080';
    $this->_formatCode = 'NY';
    $this->_startServiceCode = '00';
    $this->_startTransmissionType = '00';
    $this->_startRecordType = '10';
    $this->_endServiceCode = '00';
    $this->_endTransmissionType = '00';
    $this->_fileLines = array();
    $this->_transmissionNumber = date('dmy') . '7';
    $this->_assignmentNumber = str_pad(date('d', strtotime('+1 day'))
      .date('m', strtotime('+12 months')).'16','7','0', STR_PAD_LEFT);
    $this->_assignmentAccount = '70586360610';
    $this->_avtaleGiroServiceCode = '21';
    $this->_assignmentRecordType = '20';
    $this->_assignmentCount = 0;
    $this->_assignmentTotal = 0;
    $this->_transactionNumber = 0;
    $this->_withNotificationTransactionType = '21';
    $this->_withoutNotificationTransactionType = '02';
    $this->_firstContributionLineRecordType = '30';
    $this->_secondContributionLineRecordType = '31';
    $this->_endAssignmentLineRecordType = '88';
    $this->_endRecordType = '89';
    $this->_defaultExternalRef = 'MAF Norge';
    $this->_membershipExternalRef = 'Medlemskap';
    $this->_countRecords = 0;
    $this->_fileCount = 0;
    $this->_fileTotal = 0;

  }


  /**
   * Method to generate the ocr file
   *
   * @param $sddFileId
   * @return string
   * @throws Exception when transaction group not found for sdd file id
   */
  public function generateOCR($sddFileId) {
    // retrieve transaction group id
    try {
      $txGroupId = civicrm_api3('SepaTransactionGroup', 'getvalue', array(
        'sdd_file_id' => $sddFileId,
        'return' => 'id'));
      $this->writeFileStartLine();
      // get all contributions in transaction group
      $txContributions = civicrm_api3('SepaContributionGroup', 'get', array('txgroup_id' => $txGroupId));
      if (!empty($txContributions['values'])) {
        // start assignment for contributions
        $this->writeAssignmentStartLine();
        foreach ($txContributions['values'] as $txContribution) {
          $this->writeContributionLine($txContribution);
        }
        $this->writeAssignmentEndLine();
      }
      $this->writeFileEndLine();
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find transaction group to generate OCR file for in '.__METHOD__
        .', contact your system administrator!');
    }
    return implode("\n", $this->_fileLines);
  }

  /**
   * Method to add two lines for each contribution
   *
   * @param $txContribution
   */
  private function writeContributionLine($txContribution) {
    $this->_assignmentCount++;
    $this->_transactionNumber++;
    $this->_fileCount++;
    $transactionNumber = str_pad($this->_transactionNumber, 7, '0', STR_PAD_LEFT);
    // get contribution with id from txContribution
    try {
      $contribution = civicrm_api3('Contribution', 'getsingle', array(
        'id' => $txContribution['contribution_id']));
      // retrieve the avtale giro agreement for this contribution
      $avtaleGiroContract = $this->getAvtaleGiroForRecur($contribution['contribution_recur_id']);
      // validate if the amount is not bigger than the max amount of the avtale giro, and only process if
      // it is not!
      if ($this->isValidAmount($avtaleGiroContract, $contribution) == TRUE) {
        // use default campaign 1 if no campaign found
        if (!isset($contribution['contribution_campaign_id'])) {
          $contribution['contribution_campaign_id'] = 1;
        }
        $transactionType = $this->getTransactionType($avtaleGiroContract);
        // write first contribution line
        $this->writeContributionFirstLine($transactionNumber, $transactionType, $contribution);
        // write second contribution line
        $this->writeContributionSecondLine($transactionNumber, $transactionType, $contribution);
        // keep running total for assignment
        $this->_assignmentTotal += (float)$contribution['total_amount'];
        $this->_fileTotal += (float)$contribution['total_amount'];
        // save earliest and latest date for assignment
        $this->checkEarliestAndLatestDate($contribution['receive_date']);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $message = ts('No contribution found in the database!').' '.ts('Contact your system administrator');
      $this->createActivity('error', $message, array('contribution_id in transaction group' => $txContribution['contribution_id']));
    }
  }

  /**
   * Method to create a warning or error activity
   *
   * @param string $type
   * @param string $message
   * @param array $details
   */
  private function createActivity($type, $message, $details) {
    $activity = new CRM_Mafsepa_Activity();
    $activity->create(array(
      'subject' => 'OCR Export '.ucfirst($type),
      'message' => $message,
      'details' => $details
    ));
  }

  /**
   * Method to set the external reference based on the incoming financial type id
   *
   * @param $financialTypeId
   * @return null|string
   */
  private function getExternalRef($financialTypeId) {
    $externalRef = $this->_defaultExternalRef;
    $config = CRM_Mafsepa_Config::singleton();
    if ($financialTypeId == $config->getMembershipFinancialTypeId()) {
      $externalRef = $this->_membershipExternalRef;
    }
    return $externalRef;
  }

  /**
   * Method to save the earliest and latest date of the assignment in the class properties
   *
   * @param $inDate
   */
  private function checkEarliestAndLatestDate($inDate) {
    $testingDate = new DateTime($inDate);
    $earliestDate = new DateTime($this->_earliestDate);
    $latestDate = new DateTime($this->_latestDate);
    if ($testingDate < $earliestDate) {
      $this->_earliestDate = $inDate;
    }
    if ($testingDate > $latestDate) {
      $this->_latestDate = $inDate;
    }
  }

  /**
   * Method to get bank account from sepa mandate
   *
   * @param $contribution
   * @return array|null
   */
  private function getBankAccountFromMandate($contribution) {
    $bankAccount = NULL;
    try {
      $bankAccount = civicrm_api3('SepaMandate', 'getvalue', array(
        'entity_table' => 'civicrm_contribution_recur',
        'entity_id' => $contribution['contribution_recur_id'],
        'return' => 'iban'
      ));
      if (empty($bankAccount)) {
        $message = ts('Empty bank account in recurring contribution/sepa mandate for contribution');
        $details = array(
          'contribution id' => $contribution['id'],
          'receive date' => $contribution['receive date'],
          'amount' => $contribution['total_amount'],
          'contact id' => $contribution['contact_id'],
        );
        $this->createActivity('warning', $message, $details);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $message = ts('No sepa mandate found for contribution');
      $details = array(
        'contribution id' => $contribution['id'],
        'receive date' => $contribution['receive date'],
        'amount' => $contribution['total_amount'],
        'contact id' => $contribution['contact_id'],
      );
      $this->createActivity('error', $message, $details);
    }
    return $bankAccount;
  }

  /**
   * Method to get the avtale giros for a contact
   * (using SQL rather than API for performance)
   *
   * @param $contactId
   * @return array
   */
  public function getAvtaleGiroForContact($contactId) {
    $result = array();
    if (!empty($contactId)) {
      $result = array();
      $sql = "SELECT a.entity_id, c.title AS campaign, r.campaign_id, r.amount, r.frequency_interval, r.frequency_unit, 
        r.start_date, r.end_date, s.is_enabled, a.maf_kid, a.maf_maximum_amount, a.maf_notification_bank, s.id AS mandate_id
        FROM civicrm_contribution_recur r 
        LEFT JOIN civicrm_sdd_mandate s ON r.id = s.entity_id AND s.entity_table = %1
        LEFT JOIN civicrm_value_maf_avtale_giro a ON r.id = a.entity_id
        LEFT JOIN civicrm_campaign c ON r.campaign_id = c.id
        WHERE r.contact_id = %2";
      $sqlParams = array(
        1 => array('civicrm_contribution_recur', 'String',),
        2 => array($contactId, 'Integer',),
      );
      $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
      while ($dao->fetch()) {
        $avtaleGiro = array(
          'recur_id' => $dao->entity_id,
          'kid' => $dao->maf_kid,
          'campaign' => $dao->campaign,
          'campaign_id' => $dao->campaign_id,
          'amount' => $dao->amount,
          'max_amount' => $dao->maf_maximum_amount,
          'notification' => $dao->maf_notification_bank,
          'frequency' => $dao->frequency_interval.' '.$dao->frequency_unit,
          'frequency_interval' => $dao->frequency_interval,
          'frequency_unit' => $dao->frequency_unit,
          'start_date' => $dao->start_date,
          'status' => $dao->is_enabled,
          'mandate_id' => $dao->mandate_id,
        );
        $result[] = $avtaleGiro;
      }
    }
    return $result;
  }

  /**
   * Method to get the avtale giro for a recurring contribution
   * (using SQL rather than API for performance)
   *
   * @param $recurId
   * @return array
   */
  public function getAvtaleGiroForRecur($recurId) {
    $result = array();
    if (!empty($recurId)) {
      $result = array();
      $sql = "SELECT a.entity_id, c.title AS campaign, r.campaign_id, r.amount, r.frequency_interval, r.frequency_unit, 
        r.start_date, r.end_date, s.is_enabled, a.maf_kid, a.maf_maximum_amount, a.maf_notification_bank, s.id AS mandate_id
        FROM civicrm_contribution_recur r 
        LEFT JOIN civicrm_sdd_mandate s ON r.id = s.entity_id AND s.entity_table = %1
        LEFT JOIN civicrm_value_maf_avtale_giro a ON r.id = a.entity_id
        LEFT JOIN civicrm_campaign c ON r.campaign_id = c.id
        WHERE r.id = %2";
      $sqlParams = array(
        1 => array('civicrm_contribution_recur', 'String',),
        2 => array($recurId, 'Integer',),
      );
      $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
      if ($dao->fetch()) {
        $result = array(
          'recur_id' => $dao->entity_id,
          'kid' => $dao->maf_kid,
          'campaign' => $dao->campaign,
          'campaign_id' => $dao->campaign_id,
          'amount' => $dao->amount,
          'max_amount' => $dao->maf_maximum_amount,
          'notification' => $dao->maf_notification_bank,
          'frequency' => $dao->frequency_interval.' '.$dao->frequency_unit,
          'frequency_interval' => $dao->frequency_interval,
          'frequency_unit' => $dao->frequency_unit,
          'start_date' => $dao->start_date,
          'end_date' => $dao->end_date,
          'status' => $dao->is_enabled,
          'mandate_id' => $dao->mandate_id,
        );
      }
    }
    return $result;
  }

  /**
   * Method to get the transaction type for a contribution line based on the notification on the avtale giro
   *
   * @param array $avtaleGiroContract
   * @return string $transactionType
   */
  private function getTransactionType($avtaleGiroContract) {
    $transactionType = $this->_withoutNotificationTransactionType;
    if (isset($avtaleGiroContract['notification']) && $avtaleGiroContract['notification'] == 1) {
      $transactionType = $this->_withNotificationTransactionType;
    }
    return (string) $transactionType;
  }

  /**
   * Method to write the first line for the contribution
   *
   * @param $transactionNumber
   * @param $transactionType
   * @param $contribution
   */
  private function writeContributionFirstLine($transactionNumber, $transactionType, $contribution) {
    $contributionDate = date('dmy', strtotime($contribution['receive_date']));
    $contributionAmount = str_pad((float) $contribution['total_amount'] * 100, 17, 0, STR_PAD_LEFT);
    try {
      $contributionKid = civicrm_api3('Kid', 'generate', array(
        'contribution_id' => $contribution['id'],
        'campaign_id' => $contribution['contribution_campaign_id'],
        'contact_id' => $contribution['contact_id']
      ));
      $this->_countRecords++;
      $this->_fileLines[] = implode('', array(
        $this->_formatCode,
        $this->_avtaleGiroServiceCode,
        $transactionType,
        $this->_firstContributionLineRecordType,
        $transactionNumber,
        $contributionDate,
        str_pad('', 11),
        $contributionAmount,
        str_pad($contributionKid['kid_number'], 25, ' ', STR_PAD_LEFT),
        str_pad(0, 6, 0)
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      $message = ts('Could not generate a KID for contribution, collection cancelled');
      $details = array(
        'contribution id' => $contribution['id'],
        'campaign id' => $contribution['contribution_campaign_id'],
        'receive date' => $contribution['receive date'],
        'amount' => $contribution['total_amount'],
        'contact id' => $contribution['contact_id'],
      );
      $this->createActivity('error', $message, $details);
    }
  }

  /**
   * Method to write the second line for the contribution
   *
   * @param $transactionNumber
   * @param $transactionType
   * @param $contribution
   */
  private function writeContributionSecondLine($transactionNumber, $transactionType, $contribution) {
    $sql = 'SELECT first_name, last_name FROM civicrm_contact WHERE id = %1';
    $contact = CRM_Core_DAO::executeQuery($sql, array(1 => array($contribution['contact_id'], 'Integer')));
    if ($contact->fetch()) {
      $firstName = @iconv("UTF-8", "ASCII//IGNORE", mb_substr($contact->first_name, 0, 5));
      $lastName = @iconv("UTF-8", "ASCII//IGNORE", mb_substr($contact->last_name, 0, 5));
      $abbreviatedName = str_pad($firstName . $lastName, 10);
    } else {
      $message = ts('Could not find a contact for contribution, collection still going ahead without name');
      $details = array(
        'contribution id' => $contribution['id'],
        'campaign id' => $contribution['contribution_campaign_id'],
        'receive date' => $contribution['receive date'],
        'amount' => $contribution['total_amount'],
        'contact id' => $contribution['contact_id'],
      );
      $this->createActivity('warning', $message, $details);
      $abbreviatedName = '';
    }
    $externalRef = str_pad($this->getExternalRef($contribution['financial_type_id']), 25);
    $this->_countRecords++;
    $this->_fileLines[] = implode('', array(
      $this->_formatCode,
      $this->_avtaleGiroServiceCode,
      $transactionType,
      $this->_secondContributionLineRecordType,
      $transactionNumber,
      $abbreviatedName,
      str_pad('', 25),
      $externalRef,
      str_pad(0, 5, 0)
    ));
  }

  /**
   * Method to write the start lines for the ocr file (transmission and assignment)
   */
  private function writeFileStartLine() {
    $this->_countRecords++;
    $this->_fileLines[] = implode('', array(
      $this->_formatCode,
      $this->_startServiceCode,
      $this->_startTransmissionType,
      $this->_startRecordType,
      $this->_netsCustomerId,
      $this->_transmissionNumber,
      $this->_netsId,
      str_pad(0, 49, 0)
    ));
  }

  /**
   * Method to write an assignment start line
   */
  private function writeAssignmentStartLine() {
    $this->_assignmentCount = 0;
    $this->_assignmentTotal = 0;
    $this->_countRecords++;
    $this->_fileLines[] = implode('', array(
      $this->_formatCode,
      $this->_avtaleGiroServiceCode,
      $this->_startTransmissionType,
      $this->_assignmentRecordType,
      str_pad(0, 9, 0),
      $this->_assignmentNumber,
      $this->_assignmentAccount,
      str_pad(0, 45, 0),
    ));
  }

  /**
   * Method to write the assignment end line
   */
  private function writeAssignmentEndLine() {
    $countTransactions = str_pad($this->_assignmentCount, 8, 0, STR_PAD_LEFT);
    // each contribution * 2 (2 lines each) + start and end assignment
    $countRecords = ($this->_assignmentCount * 2) + 2;
    $countRecords = str_pad($countRecords, 8, 0, STR_PAD_LEFT);
    $assignmentTotal = $this->_assignmentTotal * 100;
    $assignmentTotal = str_pad($assignmentTotal, 17, 0, STR_PAD_LEFT);
    $this->_countRecords++;
    $earliestDate = date('dmy', strtotime($this->_earliestDate));
    $latestDate = date('dmy', strtotime($this->_latestDate));
    if ($earliestDate == '010170') {
      $earliestDate = '000000';
    }
    if ($latestDate == '010170') {
      $latestDate = '000000';
    }
    $this->_fileLines[] = implode('', array(
      $this->_formatCode,
      $this->_avtaleGiroServiceCode,
      $this->_endTransmissionType,
      $this->_endAssignmentLineRecordType,
      $countTransactions,
      $countRecords,
      $assignmentTotal,
      $earliestDate,
      $latestDate,
      str_pad(0, 27, 0),
    ));
  }

  /**
   * Method to write end line of the file
   */
  private function writeFileEndLine() {
    $this->_countRecords++;
    $countRecords = str_pad($this->_countRecords, 8, 0, STR_PAD_LEFT);
    $fileTransactions = str_pad($this->_fileCount, 8, 0, STR_PAD_LEFT);
    $fileTotalAmount = str_pad(($this->_fileTotal * 100), 17, 0, STR_PAD_LEFT);
    $earliestDate = date('dmy', strtotime($this->_earliestDate));
    if ($earliestDate == '010170') {
      $earliestDate = '000000';
    }
    $this->_fileLines[] = implode('', array(
      $this->_formatCode,
      $this->_endServiceCode,
      $this->_endTransmissionType,
      $this->_endRecordType,
      $fileTransactions,
      $countRecords,
      $fileTotalAmount,
      $earliestDate,
      str_pad(0, 33, 0),
    ));
  }

  /**
   * Method to check if the amount in the contribution is valid against the maximum amount in the avtale giro
   * agreement. If it is NOT, generate warning activity and return FALSE
   *
   * @param array $avtaleGiroContract
   * @param array $contribution
   * @return bool
   */
  private function isValidAmount($avtaleGiroContract, $contribution) {
    if (isset($contribution['total_amount']) && !empty($contribution['total_amount'])) {
      if ($contribution['total_amount'] > $avtaleGiroContract['max_amount']) {
        $message = ts('Amount of contribution exceeds Avtale Giro contract maximum amount, collection cancelled');
        $details = array(
          'contribution id' => $contribution['id'],
          'campaign id' => $contribution['contribution_campaign_id'],
          'receive date' => $contribution['receive date'],
          'amount' => $contribution['total_amount'],
          'contact id' => $contribution['contact_id'],
          'maximum amount contract' => $avtaleGiroContract['max_amount']
        );
        $this->createActivity('error', $message, $details);
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Method to fix the data after the SEPA mandate has been created with API SepaMandate createfull
   *
   * @param $apiResult
   * @param $apiParams
   * @return array
   */
  public function fixAfterCreatefull($apiResult, $apiParams) {
    // check if this SEPA mandate should be fixed
    if ($this->sepaMandateShouldBeFixed($apiResult, $apiParams)) {
      $sql = 'UPDATE civicrm_sdd_mandate SET is_enabled = %1 WHERE id = %2';
      $sqlParams = array(
        1 => array(0, 'Integer'),
        2 => array($apiResult['id'], 'Integer'),
      );
      CRM_Core_DAO::executeQuery($sql, $sqlParams);
      $apiResult['is_enabled'] = 0;
    }
    return $apiResult;
  }

  /**
   * Method to determine if SEPA Mandate is one that should be fixed for new AvtaleGiro:
   * - status = 'FRST', type = 'RCUR' and entity_table = 'civicrm_contribution_recur'
   * - entity_id is not empty
   * - kid and campaign_id where in the param list of the API call
   *
   * @param $sepaMandate
   * @param $apiParams
   * @return bool
   */
  private function sepaMandateShouldBeFixed($sepaMandate, $apiParams) {
    if (isset($sepaMandate['status']) && $sepaMandate['status'] == 'FRST') {
      if (isset($sepaMandate['type']) && $sepaMandate['type'] == 'RCUR') {
        if (isset($sepaMandate['entity_table']) && $sepaMandate['entity_table'] == 'civicrm_contribution_recur') {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Method to delete Avtale Giro
   *
   * @param $recurId
   * @throws Exception when error from Sepa Mandate delete API
   */
  public function deleteWithRecurringId($recurId) {
    // retrieve avtale giro data with recurring id
    $avtaleGiro = $this->getAvtaleGiroForRecur($recurId);
    // delete is only allowed if is_enabled = 0
    if ($avtaleGiro['status'] == 0) {
      try {
        // first delete sepa mandate
        civicrm_api3('SepaMandate', 'delete', array('id' => $avtaleGiro['mandate_id'],));
        // next remove related recurring contribution
        civicrm_api3('ContributionRecur', 'delete', array('id' => $recurId));
      }
      catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not delete AvtaleGiro with KID '.$avtaleGiro['kid'].' in '.__METHOD__
          .', error from API SepaMandate delete: '.$ex->getMessage());
      }
    }
  }
}