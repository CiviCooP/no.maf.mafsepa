<?php

/**
 * Class for APIWrapper
 *
 * Initially: process param bank_account which will be passed to API SepaMandate createfull but will not be
 * processed by core CiviSepa API. This will avoid the core CiviSepa API throwing up an error on the IBAN being
 * invalid
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 8 Feb 2017
 * @license AGPL-3.0
 */
class CRM_Mafsepa_APIWrapper implements API_Wrapper {
  /**
   * Void method required from interface
   *
   * @param array $apiRequest
   * @return $apiRequest
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * Method to hack the api result array
   *
   * @param array $apiRequest
   * @param array $result
   * @return array $result
   */
  public function toApiOutput($apiRequest, $result) {
    switch ($apiRequest['entity']) {
      case 'SepaMandate':
        if ($apiRequest['action'] == 'createfull') {
          $this->processBankAccount($apiRequest['params'], $result);
        }
        break;
      case 'SepaTransactionGroup':
        if ($apiRequest['action'] == 'create') {
          // update tx group when there is a new one (no id param)
          if (!isset($apiRequest['params']['id'])) {
            $this->updateTxGroup($result);
          } else {
            // update sdd file with reference and filename
            $this->updateSddFile($apiRequest['params']);
          }
        }
        break;
    }
    return $result;
  }

  /**
   * Method to update the SDD filename with MAF Norge values
   *
   * @param $params
   */
  private function updateSddFile($params) {
    // only if both id (of the transaction group!) and sdd_file_id
    if (isset($params['id']) && isset($params['sdd_file_id']) && !empty($params['id']) && !empty($params['sdd_file_id'])) {
      // get reference from txgroup
      try {
        $txGroupReference = civicrm_api3('SepaTransactionGroup', 'getvalue', array(
          'id' => $params['id'],
          'return' => 'reference'));
        // update reference and filename of sdd file
        $config = CRM_Mafsepa_Config::singleton();
        $ocrReference = $config->getOcrFileReference().$txGroupReference;
        $ocrFileName = $ocrReference.'.ocr';
        $sql = "UPDATE civicrm_sdd_file SET reference = %1, filename = %2 WHERE id = %3";
        $sqlParams = array(
          1 => array($ocrReference, 'String'),
          2 => array($ocrFileName, 'String'),
          3 => array($params['sdd_file_id'], 'Integer')
        );
        CRM_Core_DAO::executeQuery($sql, $sqlParams);
      } catch (CiviCRM_API3_Exception $ex) {}
    }
  }

  /**
   * Method to update the SEPA Transaction Group and SEPA SDD file with MAF Norge values
   *
   * @param $result
   */
  private function updateTxGroup($result) {
    // get the sdd creditor of the tx group
    try {
      $creditorId = civicrm_api3('SepaTransactionGroup', 'getvalue', array(
        'id' => $result['id'],
        'return' => 'sdd_creditor_id'
      ));
      // update the reference and type of the transaction group for MAF Norge
      if (isset($result['id']) && !empty($result['id'])) {
        $config = CRM_Mafsepa_Config::singleton();
        $txGroupReference = $config->getMafTxGroupReference().'-'.$creditorId.'-'.date('Y-m-d');
        $sql = 'UPDATE civicrm_sdd_txgroup SET reference = %1, type = %2 WHERE id = %3';
        $sqlParams = array(
          1 => array($txGroupReference, 'String'),
          2 => array('OCR', 'String'),
          3 => array($result['id'], 'Integer')
        );
        CRM_Core_DAO::executeQuery($sql, $sqlParams);
      }
    } catch (CiviCRM_API3_Exception $ex) {}
  }


  /**
   * Method to change the iban in the sdd mandate into the bank account and update
   * the api result with the bank account as the iban
   *
   * @param $params
   * @param $result
   */
  private function processBankAccount($params, &$result) {
    if (isset($params['bank_account']) && !empty($params['bank_account'])) {
      $sql = "UPDATE civicrm_sdd_mandate SET iban = %1 WHERE id = %2";
      $sqlParams = array(
        1 => array($params['bank_account'], 'String'),
        2 => array($result['id'], 'Integer')
      );
      CRM_Core_DAO::executeQuery($sql, $sqlParams);
      foreach ($result['values'] as $valueId => $valueSet) {
        $result['values'][$valueId]['iban'] = $params['bank_account'];
      }
    }
  }
}