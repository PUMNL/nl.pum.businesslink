<?php

/**
 * Class for Business Link Option Value processing
 *
 * @author Erik Hommel (CiviCooP)
 * @date 5 Dec 2016
 * @license AGPL-3.0
 */
class CRM_Businesslink_OptionValue {

  public static function add($params) {
    if (self::validateParams($params) == TRUE) {
      // get option value if if already exists to get update rather than create
      self::exists($params);
      // if no label, generate label from name
      if (!isset($params['label']) || empty($params['label'])) {
        $params['label'] = CRM_Businesslink_Utils::generateLabelFromName($params['name']);
      }
      try {
        $optionValue = civicrm_api3('OptionValue', 'create', $params);
        return $optionValue;
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create or update option value with name '.$params['name']
          .' in option group '.$params['option_group_id'].' in '.__METHOD__
          .', contact your system administrator');
      }
    }
  }

  /**
   * Method to validate the params array
   *
   * @param $params
   * @return bool
   * @throws Exception when name or option_group_id empty or not in params
   */
  private static function validateParams($params) {
    if (!isset($params['option_group_id']) || empty($params['option_group_id'])) {
      throw new Exception('The parameter array should contain option_group_id in '.__METHOD__
        .', can not be empty. Contact your system administrator');
    }
    if (!isset($params['name']) || empty($params['name'])) {
      throw new Exception('The parameter array should contain name in '.__METHOD__
        .', can not be empty. Contact your system administrator');
    }
    return TRUE;
  }

  /**
   * Method to get the option value id if the option value already exists
   *
   * @param $params
   */
  private static function exists(&$params) {
    $params['return'] = 'id';
    try {
      $params['id'] = civicrm_api3('OptionValue', 'getvalue', $params);
      unset($params['return']);
    } catch (CiviCRM_API3_Exception $ex) {
      unset($params['return']);
    }
  }
}