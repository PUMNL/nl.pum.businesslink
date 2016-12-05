<?php

/**
 * Created by PhpStorm.
 * User: erik
 * Date: 5-12-16
 * Time: 10:36
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