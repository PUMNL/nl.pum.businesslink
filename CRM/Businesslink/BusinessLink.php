<?php

/**
 * Class for Business Link processing
 *
 * @author Erik Hommel (CiviCooP)
 * @date 5 Dec 2016
 * @license AGPL-3.0
 */
class CRM_Businesslink_BusinessLink {
  public static function buildForm($formName, &$form) {
    if ($formName == "CRM_Case_Form_Activity") {
      $formAction = $form->getVar('_action');
      $formCaseType = $form->getVar('_caseType');
      // case type business link and action is add
      if ($formCaseType == 'Business' && $formAction == CRM_Core_Action::ADD) {
        $formActivityType = $form->getVar('_activityTypeName');
        // activity type is Request Business Programme
        if ($formActivityType == 'Request Business Programme') {
          // set defaults for business programme
          Self::setDefaultsNewBusinessProgramme($form);
        }
      }
    }
  }
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