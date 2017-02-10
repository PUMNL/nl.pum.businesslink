<?php

/**
 * Class for Business Link Group processing
 *
 * @author Erik Hommel (CiviCooP)
 * @date 6 Dec 2016
 * @license AGPL-3.0
 */
class CRM_Businesslink_Group {

  public static function add($params) {
    if (self::validateParams($params) == TRUE) {
      if (!isset($params['title']) || empty($params['title'])) {
        $params['title'] = CRM_Businesslink_Utils::generateLabelFromName($params['name']);
      }
      // default group type is mailing group
      if (!isset($params['group_type']) || empty($params['group_type'])) {
        $params['group_type'] = 2;
      }
      $params['group_type'] = CRM_Utils_Array::implodePadded($params['group_type']);
      // default visibility is user and admin
      if (!isset($params['visibility']) || empty($params['visibility'])) {
        $params['visibility'] = 'User and User Admin Only';
      }
      // get group if already exists to get update rather than create
      self::exists($params);
      try {
        $group = civicrm_api3('Group', 'create', $params);
        return $group;
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create or update group with title '.$params['title']
          .' in '.__METHOD__.', contact your system administrator');
      }
    }
  }

  /**
   * Method to validate the params array
   *
   * @param $params
   * @return bool
   * @throws Exception when name_a_b or name_b_a empty or not in params
   */
  private static function validateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new Exception('The parameter array should contain name in '.__METHOD__
        .', can not be empty. Contact your system administrator');
    }
    return TRUE;
  }

  /**
   * Method to get the relationship type id if already exists
   *
   * @param $params
   */
  private static function exists(&$params) {
    $api_params['return'] = 'id';
    $api_params['name'] = $params['name'];
    try {
      $params['id'] = civicrm_api3('Group', 'getvalue', $api_params);
    } catch (CiviCRM_API3_Exception $ex) {
      // Do nothing
    }
  }
}
