<?php
/*
 * This method will create the OCR file for the given group
 *
 * @param txgroup_id  the transaction group for which the file should be created
 * @param override    if true, will override an already existing file and create a new one
 */
function civicrm_api3_sepa_alternative_batching_createocr($params) {
  $override = (isset($params['override'])) ? $params['override'] : false;

  $result = CRM_Sepa_BAO_SEPATransactionGroup::createFile((int) $params['txgroup_id'], $override);
  if (is_numeric($result)) {
    // this was succesfull -> load the sepa file
    return civicrm_api3('SepaSddFile', 'getsingle', array('id'=>$result));
  } else {
    // there was an error:
    civicrm_api3_create_error($result);
  }
}

function civicrm_api3_sepa_alternative_batching_createocr_spec(&$params) {
  $params['txgroup_id']['api.required'] = 1;
}
