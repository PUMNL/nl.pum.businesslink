<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Businesslink_Upgrader extends CRM_Businesslink_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Method to run on install, creating activity types and relationships
   */
  public function install() {
    $this->createOptionValues();
    $this->createRelationshipTypes();
    $this->createGroups();
    $this->executeCustomDataFile('xml/business_programme.xml');
  }

  /**
   * Method to run on enable, creating activity types and relationships
   */
  public function enable() {
    $this->createOptionValues();
    $this->createRelationshipTypes();
    $this->createGroups();
  }

  /**
   * Method to create required groups
   */
  private function createGroups() {
    $groupParams = array(
			'name' => 'pum_companies_not_checked',
			'title' => 'Companies Not Checked',
			'description' => 'This group contains companies added in Complete Business Link that were not checked yet. Once checked, companies should be removed from this group manually',
      'group_type' => 2,
      'visibility' => 'User and User Admin Only',
      'is_active' => 1,
      'is_reserved' => 1
    );
    CRM_Businesslink_Group::add($groupParams);
  }
  
  /**
   * Method to create required relationship types
   */
  private function createRelationshipTypes() {
    $relationshipTypeParams = array(
      'contact_type_a' => 'Organization',
      'contact_sub_type_a' => 'Customer',
      'contact_type_b' => 'Organization',
      'name_a_b' => 'has_visited',
      'label_a_b' => 'Has Visited',
      'name_b_a' => 'visited_by',
      'label_b_a' => 'Visited By',
      'is_active' => 1,
      'is_reserved' => 1
    );
    CRM_Businesslink_RelationshipType::add($relationshipTypeParams);
  }

  /**
   * Method to create required option values
   */
  private function createOptionValues() {
    $reqBusProgParams = array(
      'option_group_id' => 'activity_type',
      'name' => 'Request Business Programme',
      'label' => 'Request Business Programme',
      'is_active' => 1,
      'is_reserved' => 1,
      'component_id' => 7
    );
    CRM_Businesslink_OptionValue::add($reqBusProgParams);
    $dataDiffParams = array(
      'option_group_id' => 'activity_type',
      'name' => 'Different Data Registering',
      'label' => 'Different Data Registering',
      'is_active' => 1,
      'is_reserved' => 1,
      'component_id' => 7
    );
    CRM_Businesslink_OptionValue::add($dataDiffParams);
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
