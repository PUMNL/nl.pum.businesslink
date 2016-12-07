<?php

/**
 * Busisnesslink.getvisitdetails API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_businesslink_getvisitdetails_spec(&$spec) {
  $spec['activity_id']['type'] = 'Integer';
  $spec['activity_id']['api.required'] = 1;
}

/**
 * Busisnesslink.Completevisit API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_businesslink_getvisitdetails($params) {
  $processor = new CRM_Businesslink_CompleteVisit();
  $returnValues[] = $processor->getVisitDetails($params['activity_id']);
  return civicrm_api3_create_success($returnValues, $params, 'Businesslink', 'Getvisitdetails');
}