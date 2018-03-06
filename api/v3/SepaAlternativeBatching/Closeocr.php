<?php

/**
 * This function will close a transaction group,
 * and perform the necessary logical changes to the mandates contained
 */

function civicrm_api3_sepa_alternative_batching_closeocr($params) {
  if (!is_numeric($params['txgroup_id'])) {
    return civicrm_api3_create_error("Required field txgroup_id was not properly set.");
  }

  $error_message = CRM_Mafsepa_Logic_Group::close($params['txgroup_id']);
  if (empty($error_message)) {
    return civicrm_api3_create_success();
  } else {
    return civicrm_api3_create_error($error_message);
  }
}

function _civicrm_api3_sepa_alternative_batching_closeocr_spec (&$params) {
  $params['txgroup_id']['api.required'] = 1;
}