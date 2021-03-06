<?php

/**
 * Class for Business Participant processing
 *
 * @author Erik Hommel (CiviCooP)
 * @date 5 Dec 2016
 * @license AGPL-3.0
 */

class CRM_Businesslink_BusinessParticipant {

  private $_dataDiffActivityTypeId = NULL;
  private $_travelCaseTypeId = NULL;
  private $_maleGenderId = NULL;
  private $_femaleGenderId = NULL;
  private $_scheduledActivityStatusId = NULL;
  private $_businessParticipantRelationshipTypeId = NULL;
  private $_employeeRelationshipTypeId = NULL;
  private $_projectOfficerRelationshipTypeId = NULL;
  private $_sourceData = array();
  private $_businessParticipantContactId = NULL;
  private $_casePeriodStartDate = NULL;
  private $_casePeriodEndDate = NULL;
  private $_caseProjectOfficerId = NULL;
  private $_caseCustomerId = NULL;
  private $_mainActivityTableName = NULL;
  private $_mainActivityStartColumnName = NULL;
  private $_mainActivityEndColumnName = NULL;
  private $_passportInfoTableName = NULL;
  private $_passportNumberColumnName = NULL;
  private $_passportFirstCustomId = NULL;
  private $_passportLastCustomId = NULL;
  private $_passportNumberCustomId = NULL;
  private $_passportExpiryCustomId = NULL;
  private $_travelParentTableName = NULL;
  private $_travelParentCaseIdColumnName = NULL;
  private $_infoForDsaTableName = NULL;
  private $_infoForDsaFillFromLinkedEntityColumnName = NULL;
  private $_nationalityTableName = NULL;
  private $_nationalityCustomId = NULL;
  private $_dataDifferences = array();
  private $_authorisedContactId = NULL;

  /**
   * CRM_Businesslink_BusinessParticipant constructor.
   *
   * @throws Exception when one of the required settings not found
   */
  function __construct() {
    $this->_dataDiffActivityTypeId = CRM_Businesslink_Utils::getOptionValueValue('activity_type', 'Different Data Registering');
    $this->_travelCaseTypeId = CRM_Businesslink_Utils::getOptionValueValue('case_type', 'TravelCase');
    $this->_femaleGenderId = CRM_Businesslink_Utils::getOptionValueValue('gender', 'Female');
    $this->_maleGenderId = CRM_Businesslink_Utils::getOptionValueValue('gender', 'Male');
    $this->_scheduledActivityStatusId = CRM_Businesslink_Utils::getOptionValueValue('activity_status', 'Scheduled');

    $this->_businessParticipantRelationshipTypeId =
      CRM_Businesslink_Utils::getRelationshipTypeId('Business participant is', 'Business participant for');
    $this->_employeeRelationshipTypeId = CRM_Businesslink_Utils::getRelationshipTypeId('Employee of', 'Employer of');
    $this->_projectOfficerRelationshipTypeId = CRM_Businesslink_Utils::getRelationshipTypeId('Project Officer for', 'Project Officer is');

    $this->_mainActivityTableName = CRM_Businesslink_Utils::getCustomGroupTableName('main_activity_info', 'Case');
    $this->_mainActivityStartColumnName = CRM_Businesslink_Utils::getCustomFieldField('main_activity_info', 'main_activity_start_date', 'column_name');
    $this->_mainActivityEndColumnName = CRM_Businesslink_Utils::getCustomFieldField('main_activity_info', 'main_activity_end_date', 'column_name');

    $this->_passportInfoTableName = CRM_Businesslink_Utils::getCustomGroupTableName('Passport_Information', 'Individual');
    $this->_passportNumberColumnName = CRM_Businesslink_Utils::getCustomFieldField('Passport_Information', 'Passport_Number', 'column_name');
    $this->_passportFirstCustomId = CRM_Businesslink_Utils::getCustomFieldField('Passport_Information', 'Passport_Name');
    $this->_passportLastCustomId = CRM_Businesslink_Utils::getCustomFieldField('Passport_Information', 'Passport_Name_Last_Name');
    $this->_passportNumberCustomId = CRM_Businesslink_Utils::getCustomFieldField('Passport_Information', 'Passport_Number');
    $this->_passportExpiryCustomId = CRM_Businesslink_Utils::getCustomFieldField('Passport_Information', 'Passport_Valid_until');

    $this->_travelParentTableName = CRM_Businesslink_Utils::getCustomGroupTableName('travel_parent', 'Case');
    $this->_travelParentCaseIdColumnName = CRM_Businesslink_Utils::getCustomFieldField('travel_parent', 'case_id', 'column_name');

    $this->_infoForDsaTableName = CRM_Businesslink_Utils::getCustomGroupTableName('Info_for_DSA', 'Case');
    $this->_infoForDsaFillFromLinkedEntityColumnName = CRM_Businesslink_Utils::getCustomFieldField('Info_for_DSA', 'fill_from_linked_entity', 'column_name');

    $this->_nationalityTableName = CRM_Businesslink_Utils::getCustomGroupTableName('Nationality', 'Individual');
    $this->_nationalityCustomId = CRM_Businesslink_Utils::getCustomFieldField('Nationality', 'Nationality');
  }

  /**
   * Method to add a new business participant to the case of the type business
   *
   * @param $sourceData
   * @throws Exception when empty sourceData
   */
  public function addParticipant($sourceData) {
    if (empty($sourceData) || !is_array($sourceData)) {
      throw new Exception('Received unexpected empty or invalid set of incoming parameters in '.__METHOD__
        .', contact your system administrator');
    }
    $this->_sourceData = $sourceData;
    // set gender
    if (strtolower($this->_sourceData['gender']) == "female") {
      $this->_sourceData['gender_id'] = 1;
    } else {
      $this->_sourceData['gender_id'] = 2;
    }
    // get case data
    $this->retrieveCaseData();
    // if relationship_id is passed, this is an edit which means the 'old' stuff has to be removed first.
    if (isset($this->_sourceData['relationship_id']) && !empty($this->_sourceData['relationship_id'])) {
      $this->removeRelationship($this->_sourceData['relationship_id']);
    }
    // match contact
    $this->matchContact();
    // add relationship Business Participant (case role)
    $relationshipParams = array(
      'relationship_type_id' => $this->_businessParticipantRelationshipTypeId,
      'contact_id_b' => $this->_businessParticipantContactId,
      'contact_id_a' => $this->_caseCustomerId,
      'start_date' => $this->_casePeriodStartDate,
      'end_date' => $this->_casePeriodEndDate,
      'case_id' => $this->_sourceData['case_id']
    );
    $this->createRelationship($relationshipParams);
    // and finally add travel case
    $this->createTravelCase();
  }

  /**
   * Method to remove the travel case based on an incoming relation (edit or remove option used on form)
   * Use the relation to find the contact_id_b, then use that found contact_id in combination with the
   * case_id passed to find the travel case and delete it
   *
   * @param $relationshipId
   */
  public function removeTravelCase($relationshipId) {
    if (!empty($relationshipId)) {
     $relationSql = "SELECT contact_id_b, case_id FROM civicrm_relationship WHERE id = %1";
     $relationship = CRM_Core_DAO::executeQuery($relationSql, array(1 => array($relationshipId, 'Integer')));
     if ($relationship->fetch()) {
       $travelCaseSql = "SELECT entity_id FROM ".$this->_travelParentTableName." tp LEFT JOIN civicrm_case_contact cc
      ON tp.entity_id = cc.case_id WHERE tp.case_id = %1 AND cc.contact_id = %2";
       $params = array(
         1 => array($relationship->case_id, 'Integer'),
         2 => array($relationship->contact_id_b, 'Integer'));
       $caseId = CRM_Core_DAO::singleValueQuery($travelCaseSql, $params);
       try {
         civicrm_api3('Case', 'delete', array('id' => $caseId));
       } catch (CiviCRM_API3_Exception $ex) {}
     }
    }
  }
  /**
   * Method to remove the relationship on id
   *
   * @param $relationshipId
   */
  public function removeRelationship($relationshipId) {
    if (!empty($relationshipId)) {
      try {
        civicrm_api3('Relationship', 'delete', array('id' => $relationshipId));
      } catch (CiviCRM_API3_Exception $ex) {
        // Do nothing
      }
    }
  }

  /**
   * Method to get the case data required from the case
   */
  private function retrieveCaseData() {
    $this->_caseCustomerId = CRM_Threepeas_Utils::getCaseClientId($this->_sourceData['case_id']);
    $sql = "SELECT ".$this->_mainActivityStartColumnName." AS start_date, ".$this->_mainActivityEndColumnName
      ." AS end_date FROM ".$this->_mainActivityTableName." WHERE entity_id = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($this->_sourceData['case_id'], 'Integer')));
    if ($dao->fetch()) {
      if (!empty($dao->start_date)) {
        $this->_casePeriodStartDate = $dao->start_date;
      }
      if (!empty($dao->end_date)) {
        $this->_casePeriodEndDate = $dao->end_date;
      }
    }
    // get project officer if there is any
    try {
      $this->_caseProjectOfficerId = civicrm_api3('Relationship', 'getvalue', array(
        'relationship_type_id' => $this->_projectOfficerRelationshipTypeId,
        'case_id' => $this->_sourceData['case_id'],
        'return' => 'contact_id_b'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to either find contact or create one based on passport number:
   * - if none found, create contact unless the authorised contact of the customer has no passport number yet and
   *   the email of the authorised contact is equal to the email passed in. If that is the case, use and update
   *   the authorised contact
   * - if one found, use contact
   * - if more found, create data difference activity, use oldest
   *
   * @throws Exception when table or column not found
   */
  private function matchContact() {
    if (!CRM_Core_DAO::checkTableExists($this->_passportInfoTableName)) {
      throw new Exception('Could not find table '.$this->_passportInfoTableName.' in '.__METHOD__
        .', contact your system administrator');
    }
    if (!CRM_Core_DAO::checkFieldExists($this->_passportInfoTableName, $this->_passportNumberColumnName)) {
      throw new Exception('Could not find column '.$this->_passportNumberColumnName.' in table '
        .$this->_passportInfoTableName.' in '.__METHOD__.', contact your system administrator');
    }
    $sql = "SELECT entity_id FROM ".$this->_passportInfoTableName." WHERE ".$this->_passportNumberColumnName
      ." = %1 ORDER BY entity_id";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($this->_sourceData['passport_number'], 'String')));
    switch ($dao->N) {
      case 0:
        if ($this->isAuthorisedContact() == TRUE) {
          $this->_businessParticipantContactId = $this->_authorisedContactId;
          $this->updateAuthorisedContact();
        } else {
          $contact = $this->createContact();
        }
        if (isset($contact['id'])) {
          $this->_businessParticipantContactId = $contact['id'];
        }
        break;
      case 1:
        $dao->fetch();
        $this->_businessParticipantContactId = $dao->entity_id;
        if ($this->isDataDifferent($dao->entity_id) == TRUE) {
          $this->createDataDifference('diff', $dao->entity_id);
        }
        return TRUE;
        break;
      default:
        $dao->fetch();
        $this->_businessParticipantContactId = $dao->entity_id;
        $this->createDataDifference('more', $dao->entity_id);
        return TRUE;
        break;
    }
  }

  /**
   * Method to check if the person registering is the authorised contact. This should only
   * be called if there is no match on passport number.
   * The authorised contact of the case customer is retrieved, and if the authorised contact does
   * not have a passport number and the email address is the same as in the registration, return TRUE
   *
   * @return bool
   */
  private function isAuthorisedContact() {
    $this->_authorisedContactId = CRM_Threepeas_BAO_PumCaseRelation::getAuthorisedContactId($this->_caseCustomerId);
    if ($this->_authorisedContactId) {
      $ppNumber = 'custom_'.$this->_passportNumberCustomId;
      try {
        $authorisedContact = civicrm_api3('Contact', 'getsingle', array(
          'id' => $this->_authorisedContactId,
          'return' => 'email,'.$ppNumber
        ));
        if (empty($authorisedContact[$ppNumber])) {
          if ($authorisedContact['email'] == $this->_sourceData['email']) {
            return TRUE;
          }
        }
      } catch (CiviCRM_API3_Exception $ex) {
      }
    }
    return FALSE;
  }

  /**
   * Method to update the authorised contact with the data from the registration
   */
  private function updateAuthorisedContact() {
    $ppFirst = 'custom_'.$this->_passportFirstCustomId;
    $ppLast = 'custom_'.$this->_passportLastCustomId;
    $ppNumber = 'custom_'.$this->_passportNumberCustomId;
    $ppExpiry = 'custom_'.$this->_passportExpiryCustomId;
    $nationality = 'custom_'.$this->_nationalityCustomId;

    $params = array('id' => $this->_authorisedContactId);
    $standardFields = array('first_name', 'last_name', 'gender_id', 'job_title');
    foreach ($standardFields as $fieldName) {
      if (!empty($this->_sourceData[$fieldName])) {
        $params[$fieldName] = $this->_sourceData[$fieldName];
      }
    }
    // birth_date
    if (!empty($this->_sourceData['birth_date'])) {
      $params['birth_date'] = date('d-m-Y', strtotime($this->_sourceData['birth_date']));
    }
    // custom fields
    if (!empty($this->_sourceData['passport_first_name'])) {
      $params[$ppFirst] = $this->_sourceData['passport_first_name'];
    }
    if (!empty($this->_sourceData['passport_last_name'])) {
      $params[$ppLast] = $this->_sourceData['passport_last_name'];
    }
    if (!empty($this->_sourceData['passport_number'])) {
      $params[$ppNumber] = $this->_sourceData['passport_number'];
    }
    if (!empty($this->_sourceData['passport_expiry_date'])) {
      $params[$ppExpiry] = date('d-m-Y', strtotime($this->_sourceData['passport_expiry_date']));
    }
    if (!empty($this->_sourceData['nationality'])) {
      $params[$nationality] = $this->_sourceData['nationality'];
    }
    try {
      civicrm_api3('Contact', 'create', $params);
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to create the data difference activity
   *
   * @param $type
   * @param $contactId
   * @throws Exception when error creating activity
   */
  private function createDataDifference($type, $contactId) {
    switch ($type) {
      case "diff":
        $subject = ts("Registration has different data than contact in database for contact id ").
          $contactId;
        $details = CRM_Businesslink_Utils::renderTemplate('DataDifference.tpl', $this->_dataDifferences);
        break;
      case "more":
        $subject = ts("Found more than one contact with passport number ").$this->_sourceData['passport_number'].
          ", used contact id ".$contactId;
        $details = "";
        break;
      default:
        $subject = 'Data Difference on Registration';
        $details = "";
        break;
    }
    $activityParams = array(
      'activity_type_id' => $this->_dataDiffActivityTypeId,
      'status_id' => $this->_scheduledActivityStatusId,
      'subject' => $subject,
      'source_contact_id' => $this->_caseCustomerId,
      'target_customer_id' => $contactId,
      'details' => $details,
      'case_id' => $this->_sourceData['case_id'],
    );
    if (!empty($this->_caseProjectOfficerId)) {
      $activityParams['assignee_contact_id'] = $this->_caseProjectOfficerId;
    }
    try {
      civicrm_api3('Activity', 'create', $activityParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Error when creating Data Difference on Registration activity in '.__METHOD__
        .', contact your system administrator. Error from API Activity create: '.$ex->getMessage());
    }
  }

  /**
   * Method to create a new contact for the business participant
   *
   * @return array
   * @throws Exception
   */
  private function createContact() {
    // set params to create contact with
    $contactParams = array(
      'contact_type' => 'Individual',
      'first_name' => trim(stripslashes($this->_sourceData['first_name'])),
      'last_name' => trim(stripslashes($this->_sourceData['last_name'])),
      'birth_date' => date('d-m-Y', strtotime($this->_sourceData['birth_date'])),
      'job_title' => trim(stripslashes($this->_sourceData['job_title'])),
      'employer_id' => $this->_caseCustomerId,
      'email' => trim($this->_sourceData['email']),
      'gender_id' => $this->_sourceData['gender_id'],
      'custom_'.$this->_passportFirstCustomId => trim(stripslashes($this->_sourceData['passport_first_name'])),
      'custom_'.$this->_passportLastCustomId => trim(stripslashes($this->_sourceData['passport_last_name'])),
      'custom_'.$this->_passportNumberCustomId => trim(stripslashes($this->_sourceData['passport_number'])),
      'custom_'.$this->_passportExpiryCustomId => date('d-m-Y', strtotime($this->_sourceData['passport_expiry_date'])),
      'custom_'.$this->_nationalityCustomId => $this->_sourceData['nationality'],
    );
    try {
      return civicrm_api3('Contact', 'create', $contactParams);
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception("Could not create a contact ".$this->_sourceData['first_name']." ".$this->_sourceData['last_name']
        ." in ".__METHOD__.", contact your system administrator. Error from API Contact Create: ".$ex->getMessage());
    }
  }

  /**
   * Method to create a travel case and the link to the parent case
   */
  private function createTravelCase() {
    // if there is already a travel case on this contact id linked to the same parent case, do not create.
    if ($this->travelCaseExists() == FALSE) {
      $travelCaseParams = array(
        'contact_id' => $this->_businessParticipantContactId,
        'subject' => '{contactName}-{caseType}-{caseId}',
        'case_type_id' => $this->_travelCaseTypeId
      );
      try {
        $travelCase = civicrm_api3('Case', 'create', $travelCaseParams);
        // now link parent case
        $sql = 'INSERT INTO ' . $this->_travelParentTableName . ' (entity_id, ' . $this->_travelParentCaseIdColumnName
          . ') VALUES(%1, %2)';
        CRM_Core_DAO::executeQuery($sql, array(
          1 => array($travelCase['id'], 'Integer'),
          2 => array($this->_sourceData['case_id'], 'Integer')));
        // and copy donor link to travel case
        if (method_exists('CRM_Travelcase_Utils_AddDonorFromParentCase', 'copyDonorLink')) {
          CRM_Travelcase_Utils_AddDonorFromParentCase::copyDonorLink($this->_sourceData['case_id'], $travelCase['id']);
        } else {
          throw new Exception('Could not find method copyDonorLink from class CRM_Travelcase_Utils_AddDonorFromParentCase in '
            .__METHOD__.', donor can not be copied to created travel case. Contact your system administrator');
        }

        // Also copy info for DSA from business case
        $sql = 'INSERT INTO ' . $this->_infoForDsaTableName . ' (entity_id, ' . $this->_infoForDsaFillFromLinkedEntityColumnName. ') VALUES(%1, \'1\')';
        CRM_Core_DAO::executeQuery($sql, array(1 => array($travelCase['id'], 'Integer')));
        $ma_info = CRM_Travelcase_Utils_CopyDsaInfo::getMAInfo($this->_sourceData['case_id']);
        CRM_Travelcase_Utils_CopyDsaInfo::fillFromParentCase($this->_sourceData['case_id'], $travelCase['id'], $ma_info);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create a travel case for contact id ' . $this->_businessParticipantContactId . ' in '
          . __METHOD__ . ', contact your system administrator. Error from API Case create: ' . $ex->getMessage());
      }
    }
  }

  /**
   * Method to check if there is already as travel case for the contact linked to the same parent case
   * This could be the case when the data coming in from the form just has a correction of the name, but the travel case
   * was already created in the first registration
   *
   * @return bool
   */
  private function travelCaseExists() {
    $sql = "SELECT COUNT(*) FROM ".$this->_travelParentTableName." tp LEFT JOIN civicrm_case_contact cc
      ON tp.entity_id = cc.case_id WHERE tp.case_id = %1 AND cc.contact_id = %2";
    $params = array(
      1 => array($this->_sourceData['case_id'], 'Integer'),
      2 => array($this->_businessParticipantContactId, 'Integer')
    );
    $travelCaseCount = CRM_Core_DAO::singleValueQuery($sql, $params);
    if ($travelCaseCount > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Method to add a relationship
   *
   * @param $params
   */
  private function createRelationship($params) {
    //first check if relationship does not exist yet, and if not create
    try {
      // Do a check whether the relationship already exists. If so do not try
      // to recreate otherwise the API throws an exception and the transaction is roll backed.
      if (!$this->doesRelationshipExists($params)) {
        civicrm_api3('Relationship', 'create', $params);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      // Do nothing
    }
  }

  /**
   * Check whether a relationship exists.
   * @param $params
   * @return bool
   */
  private function doesRelationshipExists($params) {
    $checkParams['relationship_type_id'] = $params['relationship_type_id'];
    $checkParams['contact_id_a'] = $params['contact_id_a'];
    $checkParams['contact_id_b'] = $params['contact_id_b'];
    $checkParams['case_id'] = $params['case_id'];
    try {
      $relationship = civicrm_api3('Relationship', 'getsingle', $checkParams);
      if (!empty($relationship['id'])) {
        return true;
      }
    } catch (CiviCRM_API3_Exception $ex) {
      // Do nothing
    }
    return false;
  }

  /**
   * Method to check if any data from the form is different from the database and store the
   * differences
   *
   * @param $contactId
   * @return bool
   */
  private function isDataDifferent($contactId) {
    if (empty($contactId)) {
      return FALSE;
    }
    $this->_dataDifferences = array();
    $ppFirst = 'custom_'.$this->_passportFirstCustomId;
    $ppLast = 'custom_'.$this->_passportLastCustomId;
    $ppNumber = 'custom_'.$this->_passportNumberCustomId;
    $ppExpiry = 'custom_'.$this->_passportExpiryCustomId;
    $nationality = 'custom_'.$this->_nationalityCustomId;
    $contactParams = array(
      'id' => $contactId,
      'return' => 'first_name,last_name,gender_id,birth_date,job_title,email,'.$ppFirst.','.$ppLast.','.$ppNumber.','. $ppExpiry.','.$nationality);
    $contact = civicrm_api3('Contact', 'getsingle', $contactParams);
    // check all standard data elements and add to difference array if different
    $fieldsToCheck = array('first_name', 'last_name', 'job_title', 'email');
    foreach ($fieldsToCheck as $fieldName) {
      if ($contact[$fieldName] != $this->_sourceData[$fieldName]) {
        $this->_dataDifferences[$fieldName] = array('old' => $contact[$fieldName],
          'new' => $this->_sourceData[$fieldName]);
      }
    }
    // check gender (check id and show label)
    if ($contact['gender_id'] != $this->_sourceData['gender_id']) {
      $this->_dataDifferences['gender'] = array('old' => $contact['gender'],
        'new' => $this->_sourceData['gender']);
    }
    // check birth date
    $birthOld = new DateTime($contact['birth_date']);
    $birthNew = new DateTime($this->_sourceData['birth_date']);
    if ($birthOld != $birthNew) {
      $this->_dataDifferences['birth_date'] = array('old' => $birthOld->format("d-m-Y"), 'new' => $birthNew->format('d-m-Y'));
    }

    // now check all custom fields
    if ($contact[$ppFirst] != $this->_sourceData['passport_first_name']) {
      $this->_dataDifferences['passport_first_name'] = array('old' => $contact[$ppFirst],
        'new' => $this->_sourceData['passport_first_name']);
    }
    if ($contact[$ppLast] != $this->_sourceData['passport_last_name']) {
      $this->_dataDifferences['passport_last_name'] = array('old' => $contact[$ppLast],
        'new' => $this->_sourceData['passport_last_name']);
    }
    if ($contact[$ppNumber] != $this->_sourceData['passport_number']) {
      $this->_dataDifferences['passport_number'] = array('old' => $contact[$ppNumber],
        'new' => $this->_sourceData['passport_number']);
    }
    $expiryOld = new DateTime($contact[$ppExpiry]);
    $expiryNew = new DateTime($this->_sourceData['passport_expiry_date']);
    if ($expiryOld != $expiryNew) {
      $this->_dataDifferences['passport_expiry_date'] = array('old' => $expiryOld->format('d-m-Y'), 'new' => $expiryNew->format('d-m-Y'));
    }
    if ($contact[$nationality] != $this->_sourceData['nationality']) {
      $this->_dataDifferences['nationality'] = array('old' => $contact[$nationality],
        'new' => $this->_sourceData['nationality']);
    }
    if (empty($this->_dataDifferences)) {
      return FALSE;
    } else {
      $this->_dataDifferences['contact'] = array('contact_id' => $contactId, 'contact_name' => CRM_Threepeas_Utils::getContactName($contactId));
      return TRUE;
    }
  }
}
