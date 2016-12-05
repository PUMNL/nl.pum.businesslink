<?php

/**
 * Class for Business Link processing
 *
 * @author Erik Hommel (CiviCooP)
 * @date 5 Dec 2016
 * @license AGPL-3.0
 */
class CRM_Businesslink_BusinessLink {
  public static function validateForm($formName, &$fields, &$files, &$form, &$errors) {
    if ($formName == "CRM_Case_Form_Activity") {
      CRM_Core_Error::debug('fields', $fields);
      CRM_Core_Error::debug('form', $form);
      exit();
    }
  }
  /**
   * Method to process civicrm hook buildForm
   *
   * @param $formName
   * @param $form
   */
  public static function buildForm($formName, &$form) {
    // if form is case summary, remove Request New Business Programme if no Expert
    if ($formName == "CRM_Case_Form_CaseView") {
      $formCaseType = $form->getVar('_caseType');
      if ($formCaseType == 'Business') {
        self::checkRequestBusinessProgramme($form);
      }
    }
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
    }
  }

  /**
   * Method to remove activity from select list on form if no expert on case
   *
   * @param $form
   */
  private static function checkRequestBusinessProgramme(&$form) {
    $caseId = $form->getVar('_caseID');
    $optionsList = $form->_elements[$form->_elementIndex['activity_type_id']]->_options;
    foreach ($optionsList as $key => $option) {
      if ($option['text'] == 'Request Business Programme') {
        if (self::expertOnCase($caseId) == FALSE) {
          unset($form->_elements[$form->_elementIndex['activity_type_id']]->_options[$key]);
        }
      }
    }
  }

  /**
   * Method to check if there is an active expert on the case
   * @param $caseId
   * @return bool
   * @throws Exception when expert relationship type not found
   */
  private static function expertOnCase($caseId) {
    try {
      $expertRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Expert',
        'name_b_a' => 'Expert',
        'return' => 'id'));
      try {
        $expertCount = civicrm_api3('Relationship', 'getcount', array(
          'relationship_type_id' => $expertRelationshipTypeId,
          'case_id' => $caseId,
        ));
        if ($expertCount > 0) {
          return TRUE;
        }
      } catch (CiviCRM_API3_Exception $ex) {
        return TRUE;
      }
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a relationship type with name_a_b Expert in '.__METHOD__
        .', contact your system administrator');
    }
    return FALSE;
  }

  /**
   * Method to set defaults for new business programme
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
    // default assignee is expert
    $relatedContacts = $form->getVar('_relatedContacts');
    foreach ($relatedContacts as $relatedContact) {
      if ($relatedContact['role'] == 'Expert') {
        $defaults['assignee_contact_id'] = $relatedContact['contact_id'];
      }
    }
    $form->setDefaults($defaults);
  }
}