<?php

/**
 * Class for Business Link processing
 *
 * @author Erik Hommel (CiviCooP)
 * @date 5 Dec 2016
 * @license AGPL-3.0
 */
class CRM_Businesslink_BusinessLink {

  /**
   * Method to process civicrm hook buildForm
   *
   * @param $formName
   * @param $form
   */
  public static function buildForm($formName, &$form) {
    // if form to add activity to case
    if ($formName == "CRM_Case_Form_Activity") {
      $formAction = $form->getVar('_action');
      $formCaseType = $form->getVar('_caseType');
      // case type business link and action is add
      if ($formCaseType == 'Business' && $formAction == CRM_Core_Action::ADD) {
        $formActivityType = $form->getVar('_activityTypeName');
        // activity type is Request Business Programme
        if ($formActivityType == 'Request Business Programme') {
          // set defaults for business programme
          self::setDefaultsNewBusinessProgramme($form);
        }
      }
      // case type is main activity and action is add

    }
  }

  /**
   * Method to get authorised contact -first try to get authorizedContactId from _relatedContact in Form,
   * if not found get from api on case, if not found get on customer
   *
   * @param array $relatedContacts
   * @param int $caseId
   * @return int
   */
  private static function getAuthorisedContactId($relatedContacts, $caseId) {
    $authorisedContactId = NULL;
    // return from relatedContacts if role is correct
    foreach ($relatedContacts as $key => $relatedContact) {
      if ($relatedContact['role'] == 'Authorised contact for') {
        return $relatedContact['contact_id'];
      }
    }
    // retrieve from case relationship
    try {
      $authorisedRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Has authorised',
        'name_b_a' => 'Authorised contact for',
        'return' => 'id'
      ));
      $authorisedContactId = civicrm_api3('Relationship', 'getvalue', array(
        'relationship_type_id' => $authorisedRelationshipTypeId,
        'case_id' => $caseId,
        'return' => 'contact_id_b'));
    } catch (CiviCRM_API3_Exception $ex) {
      // get from customer
      foreach ($relatedContacts as $key => $relatedContact) {
        if ($relatedContact['role'] == 'Client') {
          $caseClientId = $relatedContact['contact_id'];
        }
      }
      if (method_exists('CRM_Threepeas_BAO_PumCaseRelation', 'getAuthorisedContactId') && isset($caseClientId)) {
        $authorisedContactId = CRM_Threepeas_BAO_PumCaseRelation::getAuthorisedContactId($caseClientId);
      }
    }
    return $authorisedContactId;
  }

  /**
   * Method to set defaults for new business programme
   *
   * @param $form
   */
  private static function setDefaultsNewBusinessProgramme(&$form) {
    $defaults = array('subject' => 'Request Business Programme');
    // default medium email
    try {
      $defaults['medium_id'] = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'encounter_medium',
        'name' => 'email',
        'return' => 'value'));
    } catch (CiviCRM_API3_Exception $ex) {}
    // default status completed
    try {
      $defaults['status_id'] = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_status',
        'name' => 'Completed',
        'return' => 'value'));
    } catch (CiviCRM_API3_Exception $ex) {}
    // default assignee is expert. Retrieve from API as relatedContacts in form might not
    // hold the id of the expert if the same contact has more roles. In this case the _relatedContacts
    // property in the form only holds the unique contact_ids but NOT all the roles! So for example
    // if sector coordinator is the same as expert, the expert role will not be present
    $formCaseId = $form->getVar('_caseId');
    $expertId = CRM_Threepeas_BAO_PumCaseRelation::getCaseExpert($formCaseId);
    if ($expertId) {
      $defaults['assignee_contact_id'] = $expertId;
    }
    $form->setDefaults($defaults);
  }
}