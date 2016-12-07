<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Businesslink_BusinessProgrammeVisitAPI {

  private $cancelledStatusId;

  private $submittedStatusId;

  private $customGroups;

  private $hasVisitedRelationshipId;

  private $businessProgrammeActivityTypeId;

  private $workLocationTypeId;

  private $phoneTypeId;

  private $companyNotCheckedGroupId;

  public function __construct() {
    $activity_status_option_group = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'activity_status'));
    $this->cancelledStatusId = civicrm_api3('OptionValue', 'getValue', array('name' => 'Cancelled', 'return' => 'value', 'option_group_id' => $activity_status_option_group));
    $this->submittedStatusId = civicrm_api3('OptionValue', 'getValue', array('name' => 'Submitted', 'return' => 'value', 'option_group_id' => $activity_status_option_group));

    $this->customGroups['Business_Programme'] = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Business_Programme'));
    $this->customGroups['Business_Programme']['fields']['Business_Visit_Cancelled'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Business_Visit_Cancelled', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Company'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Company', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Contact_person'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Contact_person', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Visit_from'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Visit_from', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Visit_to'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Visit_ot', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Aim_of_Visit'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Short_description_Aim_of_Visit', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Result_of_Visit'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Short_description_Result_of_Visit', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Thank_you_Note_sent'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Thank_you_Note_sent', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Naam_bedrijf'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Naam_bedrijf', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Location'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Location', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Name_Company_Contact'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Name_Company_Contact', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['First_name_Initials_Company_Contact'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'First_name_Initials_Company_Contact', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Prefix_Company_Contact'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Prefix_Company_Contact', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));
    $this->customGroups['Business_Programme']['fields']['Email_Company_Contact'] = civicrm_api3('CustomField', 'getsingle', array('name' => 'Email_Company_Contact', 'custom_group_id' => $this->customGroups['Business_Programme']['id']));

    $this->hasVisitedRelationshipId = civicrm_api3('RelationshipType', 'getvalue', array('name_a_b' => 'has_visited', 'return' => 'id'));

    $this->programme_activity_type_name = 'Business Programme';
    $activity_type_option_group = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'activity_type'));
    $this->businessProgrammeActivityTypeId = civicrm_api3('OptionValue', 'getvalue', array('return' => 'value', 'name' => $this->programme_activity_type_name, 'option_group_id' => $activity_type_option_group));

    $this->workLocationTypeId = civicrm_api3('LocationType', 'getvalue', array('name' => 'Work', 'return' => 'id'));

    $this->phoneTypeId = civicrm_api3('OptionValue', 'getvalue', array('return' => 'value', 'option_group_id' => 'phone_type', 'name' => 'Phone'));

    $this->companyNotCheckedGroupId = civicrm_api3('Group', 'getvalue', array('return' => 'id', 'name' => 'pum_companies_not_checked'));
  }

  /**
   * Get the details of an existing business programme visit.
   *
   * @param $activityId
   * @return array
   */
  public function getVisitDetails($activityId) {
    $return = array();
    $return['company_name'] = '';
    $return['company_address'] = '';
    $return['company_postal_code'] = '';
    $return['company_city'] = '';
    $return['company_email'] = '';
    $return['contact_person_prefix'] = '';
    $return['contact_person_firstname'] = '';
    $return['contact_person_lastname'] = '';
    $return['contact_person_email'] = '';
    $return['contact_person_phone'] = '';
    $return['visit_from'] = '';
    $return['visit_to'] = '';
    $return['result_of_visit'] = '';
    $return['aim_of_visit'] = '';
    $return['thank_you_send'] = '';

    $activity = $this->getCustomValuesForActivity($activityId);

    $companyContactId = $this->getCurrentCompanyContactId($activityId);
    if ($companyContactId) {
      $company = civicrm_api3('Contact', 'getsingle', array('id' => $companyContactId));
      $return['company_name'] = $company['display_name'];
      $return['company_address'] = $company['street_address'];
      $return['company_postal_code'] = $company['postal_code'];
      $return['company_city'] = $company['city'];
      $return['company_email'] = $company['email'];
    } else {
      $return['company_name'] = $activity['custom_'.$this->customGroups['Business_Programme']['fields']['Naam_bedrijf']['id']];
      $return['company_city'] = $activity['custom_'.$this->customGroups['Business_Programme']['fields']['Location']['id']];
    }
    $contactPersonContactId = $this->getCurrentContactPersonId($activityId);
    if ($contactPersonContactId) {
      $contactPerson = civicrm_api3('Contact', 'getsingle', array('id' => $contactPersonContactId));
      $return['contact_person_prefix'] = $contactPerson['individual_prefix'];
      $return['contact_person_firstname'] = $contactPerson['first_name'];
      $return['contact_person_lastname'] = $contactPerson['last_name'];
      $return['contact_person_email'] = $contactPerson['email'];
      $return['contact_person_phone'] = $contactPerson['phone'];
    }

    $return['visit_from'] = $activity['custom_'.$this->customGroups['Business_Programme']['fields']['Visit_from']['id']];
    $return['visit_to'] = $activity['custom_'.$this->customGroups['Business_Programme']['fields']['Visit_to']['id']];
    $return['result_of_visit'] = $activity['custom_'.$this->customGroups['Business_Programme']['fields']['Result_of_Visit']['id']];
    $return['aim_of_visit'] = $activity['custom_'.$this->customGroups['Business_Programme']['fields']['Aim_of_Visit']['id']];
    $return['thank_you_send'] = $activity['custom_'.$this->customGroups['Business_Programme']['fields']['Thank_you_Note_sent']['id']];

    return $return;
  }

  /**
   * Cancel an Business Programme activity.
   *
   * @param $activityId
   */
  public function cancelVisit($activityId) {
    $activityParams['id'] = $activityId;
    $activityParams['status_id'] = $this->cancelledStatusId;
    civicrm_api3('Activity', 'create', $activityParams);
  }

  /**
   * Complete a business programme visit.
   * @param $params
   * @return bool
   */
  public function completeVisit($params) {
    $transaction = new CRM_Core_Transaction();
    try {
      $customerId = $this->getCustomerId($params['case_id']);
      $companyContactId = $this->createCompanyContact($params);
      civicrm_api3('GroupContact', 'create', array('contact_id' => $companyContactId, 'group_id' => $this->companyNotCheckedGroupId, 'status' => 'Added'));
      $contactPersonId = $this->createCompanyContactPerson($params, $companyContactId);
      $this->createHasVisitedRelationship($customerId, $companyContactId);
      $this->createActivity($params, $companyContactId, $contactPersonId);

      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      return false;
    }
    return true;
  }

  /**
   * Creates or updates a Business Programme activity
   *
   * @param $params
   * @param $companyId
   * @param $contactPersonId
   */
  private function createActivity($params, $companyId, $contactPersonId) {
    $activityParams = array();
    if (!empty($params['activity_id'])) {
      $activityParams['id'] = $params['activity_id'];
    } else {
      $activityParams['case_id'] = $params['case_id'];
      $activityParams['activity_type_id'] = $this->businessProgrammeActivityTypeId;
    }
    $activityParams['status_id'] = $this->submittedStatusId;
    $activityParams['subject'] = $params['company_name'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Company']['id']] = $companyId;
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Contact_person']['id']] = $contactPersonId;
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Visit_from']['id']] = $params['visit_from'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Visit_to']['id']] = $params['visit_to'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Result_of_Visit']['id']] = $params['result_of_visit'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Thank_you_Note_sent']['id']] = $params['thank_you_send'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Naam_bedrijf']['id']] = $params['company_name'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Location']['id']] = $params['company_city'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Name_Company_Contact']['id']] = $params['contact_person_lastname'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['First_name_Initials_Company_Contact']['id']] = $params['contact_person_firstname'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Email_Company_Contact']['id']] = $params['contact_person_email'];
    $activityParams['custom_'.$this->customGroups['Business_Programme']['fields']['Prefix_Company_Contact']['id']] = $params['contact_person_prefix'];

    civicrm_api3('Activity','create', $activityParams);
  }

  /**
   * Creates a relationship has visited between the customer and the company.
   * When an active relationship already exists do not create a new one.
   *
   * @param $customerId
   * @param $companyId
   */
  private function createHasVisitedRelationship($customerId, $companyId) {
    try {
      $relationshipParams = array();
      $relationshipParams['contact_id_a'] = $customerId;
      $relationshipParams['contact_id_b'] = $companyId;
      $relationshipParams['relationship_type_id'] = $this->hasVisitedRelationshipId;
      $relationshipParams['status_id'] = CRM_Contact_BAO_Relationship::CURRENT;
      $currentRelationship = civicrm_api3('Relationship', 'getsingle', $relationshipParams);
    } catch (Exception $e) {
      // No current relationship set
      unset($relationshipParams['status_id']);
      $relationshipParams['is_active'] = 1;
      civicrm_api3('Relationship', 'create', $relationshipParams);
    }
  }

  /**
   * Create a new contact person for the company
   *
   * @param $params
   * @param $companyContactId
   */
  private function createCompanyContactPerson($params, $companyContactId) {
    if (!empty($params['activity_id'])) {
      $currentContactPersonId = $this->getCurrentContactPersonId($params['activity_id']);
      if ($currentContactPersonId) {
        $contactParams['id'] = $currentContactPersonId;
      }
    }

    try {
      $contactParams['prefix_id'] = civicrm_api3('OptionValue', 'getvalue', array('return' => 'value', 'option_group_id' => 'individual_prefix', 'label' => $params['contact_person_prefix']));
    } catch (Exception $e) {
      // Do nothing
    }

    $contactParams['contact_type'] = 'Individual';
    $contactParams['first_name'] = $params['contact_person_firstname'];
    $contactParams['last_name'] = $params['contact_person_lastname'];
    $contactParams['email'] = $params['contact_person_email'];
    $contactParams['current_employer_id'] = $companyContactId;
    $result = civicrm_api3('Contact', 'create', $contactParams);
    $contactPersonId = $result['id'];

    try {
      $phoneParams['id'] = civicrm_api3('Phone', 'getvalue', array('return' => 'id', 'contact_id' => $contactPersonId, 'is_primary' => 1));
    } catch (Exception $e) {
      // Do Nothing.
    }
    $phoneParams['location_type_id'] = $this->workLocationTypeId;
    $phoneParams['contact_id'] = $contactPersonId;
    $phoneParams['is_primary'] = 1;
    $phoneParams['phone'] = $params['contact_person_phone'];
    $phoneParams['phone_type_id'] = $this->phoneTypeId;

    civicrm_api3('Phone', 'create', $phoneParams);

    return $contactPersonId;
  }

  /**
   * Create a new company contact.
   *
   * @param $params
   * @return int The ID of the created contact.
   */
  private function createCompanyContact($params) {
    if (!empty($params['activity_id'])) {
      $currentCompanyContactId = $this->getCurrentCompanyContactId($params['activity_id']);
      if ($currentCompanyContactId) {
        $companyParams['id'] = $currentCompanyContactId;
      }
    }
    $companyParams['contact_type'] = 'Organization';
    $companyParams['organization_name'] = $params['company_name'];
    $companyParams['email'] = $params['company_email'];
    $result = civicrm_api3('Contact', 'create', $companyParams);
    $companyContactId = $result['id'];

    try {
      $addressParams['id'] = civicrm_api3('Address', 'getvalue', array('return' => 'id', 'contact_id' => $companyContactId, 'is_primary' => 1));
    } catch (Exception $e) {
      // Do Nothing.
    }
    $addressParams['location_type_id'] = $this->workLocationTypeId;
    $addressParams['contact_id'] = $companyContactId;
    $addressParams['is_primary'] = 1;
    $addressParams['street_address'] = $params['company_address'];
    $addressParams['postal_code'] = $params['company_postal_code'];
    $addressParams['city'] = $params['company_city'];

    civicrm_api3('Address', 'create', $addressParams);

    return $companyContactId;
  }

  private function getCustomerId($case_id) {
    $case = civicrm_api3('Case', 'getsingle', array('id' => $case_id));
    $customer_id = reset($case['client_id']);
    return $customer_id;
  }

  /**
   * Gets the current contact person id from the activity
   *
   * @param $activityId
   * @return array|bool
   */
  private function getCurrentContactPersonId($activityId) {
    return $this->getCustomValueForActivity($activityId, $this->customGroups['Business_Programme']['fields']['Contact_person']['id']);
  }

  /**
   * Gets the current company contact id from the activity
   *
   * @param $activityId
   * @return array|bool
   */
  private function getCurrentCompanyContactId($activityId) {
    return $this->getCustomValueForActivity($activityId, $this->customGroups['Business_Programme']['fields']['Company']['id']);
  }

  private function getCustomValueForActivity($activityId, $customFieldId) {
    $return = array();
    $customValues = civicrm_api3('CustomValue', 'get', array('entity_id' => $activityId, 'entity_table' => 'civicrm_activity'));
    foreach($customValues['values'] as $customValue) {
      if (isset($customValue['id']) && isset($customValue['latest']) && $customValue['id'] == $customFieldId) {
        return $customValue['latest'];
      }
    }
    return false;
  }

  /**
   * Returns an array with custom values for an activiy.
   *
   * @param $activityId
   * @return array
   */
  private function getCustomValuesForActivity($activityId) {
    $return = array();
    $customValues = civicrm_api3('CustomValue', 'get', array('entity_id' => $activityId, 'entity_table' => 'civicrm_activity'));
    foreach($customValues['values'] as $customValue) {
      if (isset($customValue['id']) && isset($customValue['latest'])) {
        $return['custom_' . $customValue['id']] = $customValue['latest'];
      }
    }
    return $return;
  }

}