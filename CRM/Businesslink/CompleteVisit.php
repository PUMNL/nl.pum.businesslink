<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Businesslink_CompleteVisit {

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

  public function completeVisit($params) {
    $transaction = new CRM_Core_Transaction();
    try {
      if (!empty($params['cancelled'])) {
        if (!empty($params['activity_id'])) {
          $this->cancelVisit($params['activity_id']);
        }
      } else {
        $customerId = $this->getCustomerId($params['case_id']);
        $companyContactId = $this->createCompanyContact($params);
        civicrm_api3('GroupContact', 'create', array('contact_id' => $companyContactId, 'group_id' => $this->companyNotCheckedGroupId, 'status' => 'Added'));
        $contactPersonId = $this->createCompanyContactPerson($params, $companyContactId);
        $this->createHasVisitedRelationship($customerId, $companyContactId);
        $this->createActivity($params, $companyContactId, $contactPersonId);
      }

      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollback();
      return false;
    }
    return true;
  }

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

  /**
   * Cancel an Business Programme activity.
   *
   * @param $activityId
   */
  private function cancelVisit($activityId) {
    $activityParams['id'] = $activityId;
    $activityParams['status_id'] = $this->cancelledStatusId;
    civicrm_api3('Activity', 'create', $activityParams);
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
    try {
      $activityParams['id'] = $activityId;
      $activityParams['return'] = 'custom_'.$this->customGroups['Business_Programme']['fields']['Contact_person']['id'];
      return civicrm_api3('Activity', 'getvalue', $activityParams);
    } catch (Exception $e) {
      return false;
    }
    return false;
  }

  /**
   * Gets the current company contact id from the activity
   *
   * @param $activityId
   * @return array|bool
   */
  private function getCurrentCompanyContactId($activityId) {
    try {
      $activityParams['id'] = $activityId;
      $activityParams['return'] = 'custom_'.$this->customGroups['Business_Programme']['fields']['Company']['id'];
      return civicrm_api3('Activity', 'getvalue', $activityParams);
    } catch (Exception $e) {
      return false;
    }
    return false;
  }

}