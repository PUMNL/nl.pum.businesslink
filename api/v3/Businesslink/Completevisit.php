<?php

/**
 * Busisnesslink.Completevisit API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_businesslink_completevisit_spec(&$spec) {
  $spec['activity_id']['type'] = 'Integer';

  $spec['case_id']['api.required'] = 1;
  $spec['case_id']['type'] = 'Integer';
  $spec['company_name']['api.required'] = 1;
  $spec['company_name']['type'] = 'String';
  $spec['company_address']['api.required'] = 1;
  $spec['company_address']['type'] = 'String';
  $spec['company_postal_code']['api.required'] = 1;
  $spec['company_postal_code']['type'] = 'String';
  $spec['company_city']['api.required'] = 1;
  $spec['company_city']['type'] = 'String';
  $spec['company_email']['api.required'] = 1;
  $spec['company_email']['type'] = 'String';
  $spec['contact_person_prefix']['api.required'] = 1;
  $spec['contact_person_prefix']['type'] = 'String';
  $spec['contact_person_firstname']['api.required'] = 1;
  $spec['contact_person_firstname']['type'] = 'String';
  $spec['contact_person_lastname']['api.required'] = 1;
  $spec['contact_person_lastname']['type'] = 'String';
  $spec['contact_person_email']['api.required'] = 1;
  $spec['contact_person_email']['type'] = 'String';
  $spec['contact_person_phone']['api.required'] = 1;
  $spec['contact_person_phone']['type'] = 'String';
  $spec['visit_from']['api.required'] = 1;
  $spec['visit_from']['type'] = 'Date';
  $spec['visit_to']['api.required'] = 1;
  $spec['visit_to']['type'] = 'Date';
  $spec['result_of_visit']['api.required'] = 1;
  $spec['result_of_visit']['type'] = 'Date';
  $spec['thank_you_send']['api.required'] = 1;
  $spec['thank_you_send']['type'] = 'Boolean';

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
function civicrm_api3_businesslink_completevisit($params) {
  $processor = new CRM_Businesslink_BusinessProgrammeVisitAPI();
  if (!$processor->completeVisit($params)) {
    throw new API_Exception('Could not complete business link visit');
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'Businesslink', 'Completevisit');
}

