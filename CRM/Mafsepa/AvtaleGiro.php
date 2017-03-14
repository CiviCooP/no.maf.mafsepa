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
  private $_avtaleGiroCustomGroup = NULL;
  private $_avBankAccountCustomField = NULL;
  private $_avMaxAmountCustomField = NULL;
  private $_avNotificationCustomField = NULL;

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
        $this->writeAssignmentStartLine('contribution');
        foreach ($txContributions['values'] as $txContribution) {
          $this->writeContributionLine($txContribution);
        }
        $this->writeAssignmentEndLine('contribution');
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
      // use default campaign 1 if no campaign found
      if (!isset($contribution['contribution_campaign_id'])) {
        $contribution['contribution_campaign_id'] = 1;
      }
      $transactionType = $this->getTransactionType($contribution['id']);
      // write first contribution line
      $this->writeContributionFirstLine($transactionNumber, $transactionType, $contribution);
      // write second contribution line
      $this->writeContributionSecondLine($transactionNumber, $transactionType, $contribution);
      // keep running total for assignment
      $this->_assignmentTotal += (float) $contribution['total_amount'];
      $this->_fileTotal += (float) $contribution['total_amount'];
      // save earliest and latest date for assignment
      $this->checkEarliestAndLatestDate($contribution['receive_date']);
    } catch (CiviCRM_API3_Exception $ex) {
      // todo add logger
    }
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
   * Method to get the transaction type for a contribution line based on the notification on the avtale giro
   *
   * @param $contributionId
   * @return $transactionType
   */
  private function getTransactionType($contributionId) {
    $transactionType = $this->_withoutNotificationTransactionType;
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
      // todo add logger
      $abbreviatedName = '';
    }
    $externalRef = $this->getExternalRef($contribution['financial_type_id']);
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
   *
   * @param $type
   */
  private function writeAssignmentStartLine($type) {
    $this->_assignmentCount = 0;
    $this->_assignmentTotal = 0;
    if ($type == 'contribution') {
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
  }

  /**
   * Method to write the assignment end line
   *
   * @param $type
   */
  private function writeAssignmentEndLine($type) {
    if ($type == 'contribution') {
      $countTransactions = str_pad($this->_assignmentCount, 8, 0, STR_PAD_LEFT);
      // each contribution * 2 (2 lines each) + start and end assignment
      $countRecords = ($this->_assignmentCount * 2) + 2;
      $assignmentTotal = $this->_assignmentTotal * 100;
      $assignmentTotal = str_pad($assignmentTotal, 17, 0, STR_PAD_LEFT);
      $this->_countRecords++;
      $this->_fileLines[] = implode('', array(
        $this->_formatCode,
        $this->_avtaleGiroServiceCode,
        $this->_endTransmissionType,
        $this->_endAssignmentLineRecordType,
        $countTransactions,
        $countRecords,
        $assignmentTotal,
        date('dmy', strtotime($this->_earliestDate)),
        date('dmy', strtotime($this->_latestDate)),
        str_pad(0, 27, 0),
      ));
    }
  }

  /**
   * Method to write end line of the file
   */
  private function writeFileEndLine() {
    $this->_countRecords++;
    $countRecords = str_pad($this->_countRecords, 8, 0, STR_PAD_LEFT);
    $fileTransactions = str_pad($this->_fileCount, 8, 0, STR_PAD_LEFT);
    $fileTotalAmount = str_pad($this->_fileTotal, 17, 0, STR_PAD_LEFT);
    $this->_fileLines[] = implode('', array(
      $this->_formatCode,
      $this->_endServiceCode,
      $this->_endTransmissionType,
      $this->_endRecordType,
      $fileTransactions,
      $countRecords,
      $fileTotalAmount,
      date('dmy', strtotime($this->_earliestDate)),
      str_pad(0, 33, 0),
    ));
  }
}