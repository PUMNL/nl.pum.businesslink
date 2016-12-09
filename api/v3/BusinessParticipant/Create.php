<?php

/**
 * BusinessParticipant.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_business_participant_create_spec(&$spec) {
  $spec['first_name'] = array(
    'name' => 'first_name',
    'title' => 'first_name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  );
  $spec['last_name'] = array(
    'name' => 'last_name',
    'title' => 'last_name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  );
  $spec['passport_first_name'] = array(
    'name' => 'passport_first_name',
    'title' => 'passport_first_name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  );
  $spec['passport_last_name'] = array(
    'name' => 'passport_last_name',
    'title' => 'passport_last_name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  );
  $spec['passport_number'] = array(
    'name' => 'passport_number',
    'title' => 'passport_number',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  );
  $spec['passport_expiry_date'] = array(
    'name' => 'passport_expiry_date',
    'title' => 'passport_expiry_date',
    'type' => CRM_Utils_Type::T_DATE,
    'api.required' => 1
  );
  $spec['gender'] = array(
    'name' => 'gender',
    'title' => 'gender',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  );
  $spec['birth_date'] = array(
    'name' => 'birth_date',
    'title' => 'birth_date',
    'type' => CRM_Utils_Type::T_DATE,
    'api.required' => 1
  );
  $spec['nationality'] = array(
    'name' => 'nationality',
    'title' => 'nationality',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  );
  $spec['email'] = array(
    'name' => 'email',
    'title' => 'email',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  );
  $spec['job_title'] = array(
    'name' => 'job_title',
    'title' => 'job_title',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  );
  $spec['case_id'] = array(
    'name' => 'case_id',
    'title' => 'case_id',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  );
  $spec['relation_id'] = array(
    'name' => 'relation_id',
    'title' => 'relation_id',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 0
  );
}

/**
 * BusinessParticipant.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see https://github.com/PUMNL/nl.pum.businesslink/blob/master/README.md
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_business_participant_create($params) {
  $handler = new CRM_Businesslink_BusinessParticipant();
  $handler->addParticipant($params);
  return civicrm_api3_create_success(array(), $params, 'BusinessParticipant', 'Create');
}

