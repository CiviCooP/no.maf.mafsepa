<?php
/*-------------------------------------------------------+
| Project 60 - SEPA direct debit                         |
| Copyright (C) 2013-2014 SYSTOPIA                       |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

/**
 * Close a sepa group
 *
 * @package CiviCRM_SEPA
 *
 */

require_once 'CRM/Core/Page.php';

class CRM_Mafsepa_Page_DeleteGroup extends CRM_Core_Page {

  function run() {
    $requestValues = CRM_Utils_Request::exportValues();
    if (empty($requestValues['group_id'])) {
    	$this->assign('status', 'error');
    } else {
      $groupId = (int) $requestValues['group_id'];
      $this->assign('txgid', $groupId);
      try {
        $txGroup = civicrm_api3('SepaTransactionGroup', 'getsingle', array('id' => $groupId,));
      }
      catch (CiviCRM_API3_Exception $ex) {
        $requestValues['confirmed'] = 'error'; // skip the parts below
      }
      try {
        $batchStatus = civicrm_api3('OptionValue', 'getsingle', array(
          'option_group_id' => 'batch_status',
          'value' => $txGroup['status_id'],
          ));
        $txGroup['status_label'] = $batchStatus['label'];
        $txGroup['status_name'] = $batchStatus['name'];
        $this->assign('txgroup', $txGroup);
      }
      catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not find the status '.$txGroup['status_id'].' in batch status option group in '
          .__METHOD__.', contact your system administrator! Error from API OptionValue getsingle: '.$ex->getMessage());
      }

      if (!isset($requestValues['confirmed']) || empty($requestValues['confirmed'])) {
        // gather information to display
        try {
          $pendingStatus = civicrm_api3('OptionValue', 'getvalue', array(
            'option_group_id' => 'contribution_status',
            'name' => 'Pending',
            'return' => 'value',
          ));
          $inProgressStatus = civicrm_api3('OptionValue', 'getvalue', array(
            'option_group_id' => 'contribution_status',
            'name' => 'In Progress',
            'return' => 'value',
          ));
        }
        catch (CiviCRM_API3_Exception $ex) {
          $pendingStatus = '';
          $inProgressStatus = '';
        }

        $stats = array('busy' => 0, 'open' => 0, 'other' => 0, 'total' => 0);
        $status2Contributions = $this->contributionStats($groupId);
        foreach ($status2Contributions as $contributionStatusId => $contributions) {
          foreach ($contributions as $contributionId) {
            $stats['total'] += 1;
            if ($contributionStatusId == $pendingStatus) {
              $stats['open'] += 1;
            } elseif ($contributionStatusId == $inProgressStatus) {
              $stats['busy'] += 1;
            } else {
              $stats['other'] += 1;
            }
          }
        }
        $this->assign('stats', $stats);
	    	$this->assign('status', 'unconfirmed');
        $this->assign('submit_url', CRM_Utils_System::url('civicrm/mafsepa/deletegroup'));

      } elseif ($requestValues['confirmed'] == 'yes') {
        // delete the group
        $this->assign('status', 'done');
        if (isset($requestValues['delete_contents'])) {
          $deleteContributionsMode = $requestValues['delete_contents'];
        } else {
          $deleteContributionsMode = 0;
        }
        $deletedOk = array();
			  $deletedError = array();
			  $result = CRM_Sepa_BAO_SEPATransactionGroup::deleteGroup($groupId, $deleteContributionsMode);
			  if (is_string($result)) {
			    // a very basic error happened
          $this->assign('error', $result);
        } else {
			    // do some stats on the result
          $deletedTotal = count($result);
          foreach ($result as $contributionId => $message) {
            if ($message == 'ok') {
              array_push($deletedOk, $contributionId);
            } else {
              array_push($deletedError, $contributionId);
            }
          }
          $this->assign('deleted_result', $result);
          $this->assign('deleted_ok', $deletedOk);
          $this->assign('deleted_error', $deletedError);
        }
      } elseif ($requestValues['confirmed'] == 'error') {
        $this->assign('status', 'error');
      } else {
    		CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/sepa'));
      }
    }
    parent::run();
  }

  /**
   * gather some statistics about the contributions linked to this txGroup
   *
   * @param $groupId
   * @return array(contributionStatusId->array(contributionIds))
   */
  function contributionStats($groupId) {
    $stats = array();
  	$sql = "
  	SELECT
  		civicrm_contribution.id 						AS contribution_id,
  		civicrm_contribution.contribution_status_id 	AS status_id
  	FROM 		civicrm_sdd_contribution_txgroup
  	LEFT JOIN 	civicrm_contribution ON civicrm_sdd_contribution_txgroup.contribution_id = civicrm_contribution.id
  	WHERE
  		civicrm_sdd_contribution_txgroup.txgroup_id = $groupId;
  	";
  	$contributionInfo = CRM_Core_DAO::executeQuery($sql);
  	while ($contributionInfo->fetch()) {
  		if (!isset($stats[$contributionInfo->status_id])) {
  			$stats[$contributionInfo->status_id] = array();
  		}
  		array_push($stats[$contributionInfo->status_id], $contributionInfo->contribution_id);
  	}
  	return $stats;
  }
}