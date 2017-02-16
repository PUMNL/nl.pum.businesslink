<?php

/**
 * Businesslink.Cancelvisit API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_businesslink_cancelvisit_spec(&$spec) {
  $spec['activity_id']['type'] = 'Integer';
  $spec['activity_id']['api.required'] = 1;
}

/**
 * Businesslink.Cancelvisit API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_businesslink_cancelvisit($params) {
  $processor = new CRM_Businesslink_BusinessProgrammeVisitAPI();
  $processor->cancelVisit($params['activity_id']);
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'Businesslink', 'Cancelvisit');
}

