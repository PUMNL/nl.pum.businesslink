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
}