<?php

/**
 * Class for Business Link helper functions
 *
 * @author Erik Hommel (CiviCooP)
 * @date 5 Dec 2016
 * @license AGPL-3.0
 */
class CRM_Businesslink_Utils {

  /**
   * Method to generate a label from a name, replacing '_' with spaces
   *
   * @param $name
   * @return mixed
   */
  public static function generateLabelFromName($name) {
    if (empty($name)) {
      return $name;
    }
    $labelParts = array();
    $nameParts = explode("_", $name);
    foreach ($nameParts as $namePart) {
      $labelParts[] = ucfirst($namePart);
    }
    return implode(" ", $labelParts);
  }

  /**
   * Method to retrieve custom field column name
   *
   * @param $customGroupName
   * @param $customFieldName
   * @param $return
   * @return array
   * @throws Exception when API returns error
   */
  public static function getCustomFieldField($customGroupName, $customFieldName, $return = 'id') {
    try {
      return civicrm_api3('CustomField', 'getvalue', array(
        'custom_group_id' => $customGroupName,
        'name' => $customFieldName,
        'return' => $return
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception("Could not find custom field with name ".$customFieldName." from custom group with name "
        .$customGroupName." in ".__METHOD__
        .", contact your system administrator. Error from API CustomField Getvalue: ".
        $ex->getMessage());
    }
  }
  /**
   * Method to retrieve the table name of the custom group
   *
   * @param $customGroupName
   * @param $extends
   * @return array
   * @throws Exception when API returns error
   */
  public static function getCustomGroupTableName($customGroupName, $extends) {
    try {
      return civicrm_api3('CustomGroup', 'getvalue', array(
        'name' => $customGroupName,
        'extends' => $extends,
        'return' => 'table_name'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception("Could not find custom group with name ".$customGroupName." extending ".$extends." in "
        .__METHOD__.", contact your system administrator. Error from API CustomGroup Getvalue: ".
        $ex->getMessage());
    }
  }

  /**
   * Method to retrieve the id of a relationship type
   *
   * @param $nameAb
   * @param $nameBa
   * @return array
   * @throws Exception when error from API
   */
  public static function getRelationshipTypeId($nameAb, $nameBa) {
    try {
      return civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => $nameAb,
        'name_b_a' => $nameBa,
        'return' => 'id'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception("Could not find relationship type ".$nameAb."in ".__METHOD__
        .", contact your system administrator. Error from API RelationshipType Getvalue: ".
        $ex->getMessage());
    }
  }

  /**
   * Method to retrieve the value of an option value
   * @param string $optionGroupName
   * @param string $optionValueName
   * @return array
   * @throws Exception when error from API
   */
  public static function getOptionValueValue($optionGroupName, $optionValueName) {
    try {
      return civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $optionGroupName,
        'name' => $optionValueName,
        'return' => 'value'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception("Could not find option value ".$optionValueName." from option_group "
        .$optionGroupName."in ".__METHOD__.", contact your system administrator. Error from API OptionValue Getvalue: ".
        $ex->getMessage());
    }
  }


  /**
   * Method uses SMARTY to render a template
   *
   * @param $templatePath
   * @param $vars
   * @return string
   */
  public static function renderTemplate($templatePath, $vars) {
    $smarty = CRM_Core_Smarty::singleton();

    // first backup original variables, since smarty instance is a singleton
    $oldVars = $smarty->get_template_vars();
    $backupFrame = array();
    foreach ($vars as $key => $value) {
      $key = str_replace(' ', '_', $key);
      $backupFrame[$key] = isset($oldVars[$key]) ? $oldVars[$key] : NULL;
    }

    // then assign new variables
    foreach ($vars as $key => $value) {
      $key = str_replace(' ', '_', $key);
      $smarty->assign($key, $value);
    }

    // create result
    $result =  $smarty->fetch($templatePath);

    // reset smarty variables
    foreach ($backupFrame as $key => $value) {
      $key = str_replace(' ', '_', $key);
      $smarty->assign($key, $value);
    }

    return $result;
  }
}