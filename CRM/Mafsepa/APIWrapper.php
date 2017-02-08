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
   * Method process bank account into sepa mandate
   *
   * @param array $apiRequest
   * @param array $result
   * @return $result
   */
  public function toApiOutput($apiRequest, $result) {
    foreach ($apiRequest['params'] as $key=>$value) {
      CRM_Core_DAO::executeQuery("INSERT INTO ehtst (message) VALUES(%1)", array(
        1 => array('params key is '.$key.' met waarde '.$value, 'String')));
    }
    if (isset($apiRequest['params']['bank_account']) && !empty($apiRequest['params']['bank_account'])) {
      $sql = "UPDATE civicrm_sdd_mandate SET iban = %1 WHERE id = %2";
      $sqlParams = array(
        1 => array($apiRequest['params']['bank_account'], 'String'),
        2 => array($result['id'], 'Integer')
      );
      CRM_Core_DAO::executeQuery($sql, $sqlParams);
      foreach ($result['values'] as $valueId => $valueSet) {
        $result['values'][$valueId]['iban'] = $apiRequest['params']['bank_account'];
      }
    }
    return $result;
  }

}