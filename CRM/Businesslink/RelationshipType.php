<?php

/**
 * Class for Business Link Relationship Type processing
 *
 * @author Erik Hommel (CiviCooP)
 * @date 5 Dec 2016
 * @license AGPL-3.0
 */
class CRM_Businesslink_RelationshipType {

  public static function add($params) {
    if (self::validateParams($params) == TRUE) {
      // get relationship type if if already exists to get update rather than create
      self::exists($params);
      try {
        $relationshipType = civicrm_api3('RelationshipType', 'create', $params);
        return $relationshipType;
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create or update relationship type '.$params['name_a_b']
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
    if (!isset($params['name_a_b']) || empty($params['name_a_b'])) {
      throw new Exception('The parameter array should contain name_a_b in '.__METHOD__
        .', can not be empty. Contact your system administrator');
    }
    if (!isset($params['name_b_a']) || empty($params['name_b_a'])) {
      throw new Exception('The parameter array should contain name_b_a in '.__METHOD__
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
    $params['return'] = 'id';
    try {
      $params['id'] = civicrm_api3('RelationshipType', 'getvalue', $params);
      unset($params['return']);
    } catch (CiviCRM_API3_Exception $ex) {
      unset($params['return']);
    }
  }
}