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
    }
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