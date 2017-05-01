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
          $avtaleGiro = new CRM_Mafsepa_AvtaleGiro();
          $result['values'][$result['id']] = $avtaleGiro->fixAfterCreatefull($result['values'][$result['id']], $apiRequest['params']);
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
    // update the type of the transaction group for MAF Norge
    if (isset($result['id']) && !empty($result['id'])) {
      $sql = 'UPDATE civicrm_sdd_txgroup SET type = %1 WHERE id = %2';
      $sqlParams = array(
        1 => array('OCR', 'String'),
        2 => array($result['id'], 'Integer')
      );
      CRM_Core_DAO::executeQuery($sql, $sqlParams);
    }
  }

}