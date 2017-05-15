<?php

/**
 * AvtaleGiro.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_avtale_giro_Get_spec(&$spec) {
  $spec['contact_id'] = array(
    'api.required' => 1,
    'name' => 'contact_id',
    'title' => 'contact_id',
    'type' => CRM_Utils_Type::T_INT,
  );
}

/**
 * AvtaleGiro.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_avtale_giro_Get($params) {
  // check if param['contact_id'] exists
  $contactCount = civicrm_api3('Contact', 'getcount', array('id' => $params['contact_id']));
  if ($contactCount != 1) {
    return civicrm_api3_create_error('Could not find contact with contactID '.$params['contact_id'].'found '.$contactCount);
  }
  // retrieve all avtale giros for contact
  $avtaleGiro = new CRM_Mafsepa_AvtaleGiro();
  return civicrm_api3_create_success($avtaleGiro->getAvtaleGiroForContact($params['contact_id']), $params, 'AvtaleGiro', 'Get');
}

