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

class CRM_Mafsepa_Page_CloseGroup extends CRM_Core_Page {

  function run() {
    $requestValues = CRM_Utils_Request::exportValues();
    if (isset($requestValues['group_id'])) {
      if (isset($requestValues['status']) && ($requestValues['status'] == "missed"
          || $requestValues['status'] == "invalid" || $requestValues['status'] == "closed")) {
        $this->assign('status', $requestValues['status']);
      } else {
        $requestValues['status'] = "";
      }
    }
    $groupId = (int) $requestValues['group_id'];
    $this->assign('txgid', $groupId);
    try {
      // LOAD/CREATE THE TXFILE
      $group = civicrm_api3('SepaTransactionGroup', 'getsingle', array('id'=>$groupId));
      $this->assign('txgroup', $group);
      try {
        $creditor = civicrm_api3('SepaCreditor', 'getsingle', array('id' => $group['sdd_creditor_id']));
        $isTestGroup = isset($creditor['category']) && ($creditor['category'] == "TEST");
        $this->assign('is_test_group', $isTestGroup);
        if ($requestValues['status'] == "") {
          // first adjust group's collection date if requested
          if (!empty($requestValues['adjust'])) {
            $result = CRM_Sepa_BAO_SEPATransactionGroup::adjustCollectionDate($groupId, $requestValues['adjust']);
            if (is_string($result)) {
              // that's an error -> stop here!
              throw new Exception('Error adjusting collection date for group '.$groupId,' in '.__METHOD__);
            } else {
              // that went well, so result should be the update group data
              $group = $result;
            }
          }
          // delete old txfile
          if (!empty($group['sdd_file_id'])) {
            try {
              civicrm_api3('SepaSddFile', 'delete', array('id' => $group['sdd_file_id']));
            } catch (CiviCRM_API3_Exception $ex) {
              CRM_Core_Session::setStatus("Cannot delete file #".$group['sdd_file_id'].".<br/>Error was: ".$ex->getMessage(), ts('Error', array('domain' => 'no.maf.mafsepa')), 'error');
            }
          }
          try {
            $ocrFile = civicrm_api3('SepaAlternativeBatching', 'createocr', array('txgroup_id' => $groupId,
              'override' => TRUE));
            $fileId = $ocrFile['id'];
            $this->assign('file_link', CRM_Utils_System::url('civicrm/sepa/ocr', "id=$fileId"));
            $this->assign('file_name', $ocrFile['filename']);
          } catch (CiviCRM_API3_Exception $ex) {
            CRM_Core_Session::setStatus("Cannot load for group #".$groupId.".<br/>Error was: ".$ex->getMessage(), ts('Error', array('domain' => 'no.maf.mafsep')), 'error');
          }
        }
        if ($requestValues['status'] == "closed" && !$isTestGroup) {
          // CLOSE THE GROUP:
          try {
            civicrm_api3('SepaAlternativeBatching', 'closeocr', array('txgroup_id' => $groupId));
          } catch (CiviCRM_API3_Exception $ex) {
            CRM_Core_Session::setStatus("Cannot close group #$groupId.<br/>Error was: ".$ex->getMessage(), ts('Error', array('domain' => 'no.maf.mafsepa')), 'error');
          }
        }
      } catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Session::setStatus("Cannot load creditor.<br/>Error was: ".$ex->getMessage(), ts('Error', array('domain' => 'no.maf.mafsepa')), 'error');
      }
    } catch (CiviCRM_API3_Exception $ex) {
      CRM_Core_Session::setStatus("Cannot load group #$groupId.<br/>Error was: " . $ex->getMessage(), ts('Error', array('domain' => 'no.maf.mafsepa')), 'error');
    }
    parent::run();
  }
}