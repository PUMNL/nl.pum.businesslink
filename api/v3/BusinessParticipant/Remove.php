<?php

/**
 * BusinessParticipant.Remove API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_business_participant_remove_spec(&$spec) {
  $spec['relationship_id'] = array(
    'name' => 'relationship_id',
    'title' => 'relationship_id',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  );
}

/**
 * BusinessParticipant.Remove API
 *
 * @param array $params
 * @return array API result descriptor
 * @see https://github.com/PUMNL/nl.pum.businesslink/blob/master/README.md
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_business_participant_remove($params) {
  $handler = new CRM_Businesslink_BusinessParticipant();
  $handler->removeTravelCase($params['relationship_id']);
  $handler->removeRelationship($params['relationship_id']);
  return civicrm_api3_create_success(array(), $params, 'BusinessParticipant', 'Remove');
}

