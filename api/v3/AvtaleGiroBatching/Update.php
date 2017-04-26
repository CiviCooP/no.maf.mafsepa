<?php
/**
 * API CALL TO UPDATE TXGROUPs ("Batching")
 *
 * @package MAF_SEPA
 *
 */
function civicrm_api3_avtale_giro_batching_update($params) {
  // get creditor list
  $creditor_query = civicrm_api('SepaCreditor', 'get', array('version' => 3, 'option.limit' => 99999));

  if (!empty($creditor_query['is_error'])) {
    return civicrm_api3_create_error("Cannot get creditor list: ".$creditor_query['error_message']);
  } else {
    $creditors = array();
    foreach ($creditor_query['values'] as $creditor) {
      $creditors[] = $creditor['id'];
    }
  }

  if ($params['type']=='RCUR' || $params['type']=='FRST') {
    // first: make sure, that there are no outdated mandates:
    CRM_Sepa_Logic_Batching::closeEnded();

    // then, run the update for recurring mandates
    foreach ($creditors as $creditor_id) {
      CRM_Mafsepa_AvtaleGiroBatching::updateRCUR($creditor_id, $params['type']);
    }
  }
  return civicrm_api3_create_success();

}
