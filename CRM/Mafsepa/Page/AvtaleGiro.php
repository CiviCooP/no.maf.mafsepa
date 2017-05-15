<?php

class CRM_Mafsepa_Page_AvtaleGiro extends CRM_Core_Page {

  public function run() {
    $requestValues = CRM_Utils_Request::exportValues();
    // error if no rid and cid
    if (!isset($requestValues['rid']) && !isset($requestValues['cid']) && !isset($requestValues['id'])) {
      throw new Exception('Parameters rid, id and cid mandatory in '.__METHOD__.', contact your system administrator');
    }
    // set url for done button
    $doneUrl = CRM_Utils_System::url('civicrm/contact/view/contribution',
      '&reset=1&action=view&context=contribution&selectedChild=contribute&id='.$requestValues['id'].'&cid='.$requestValues['cid'],
      true);
    $this->assign('doneUrl', $doneUrl);
    // retrieve avtale with recur id and display
    $avtaleGiro = new CRM_Mafsepa_AvtaleGiro();
    $avtaleData = $avtaleGiro->getAvtaleGiroForRecur($requestValues['rid']);
    $this->assign('contactDisplayName', civicrm_api3('Contact', 'getvalue', array(
      'id' => $avtaleData['contact_id'],
      'return' => 'display_name')));
    $this->assign('campaignTitle', $avtaleData['campaign']);
    $this->assign('amount', $avtaleData['amount']);
    $this->assign('maxAmount', $avtaleData['max_amount']);
    $this->assign('frequencyInterval', $avtaleData['frequency_interval']);
    $this->assign('frequencyUnit', $avtaleData['frequency_unit']);
    $this->assign('cycleDay', $avtaleData['cycle_day']);
    if ($avtaleData['notification'] == 1) {
      $this->assign('notification', ts('Yes'));
    } else {
      $this->assign('notification', ts('No'));
    }
    $startDate = new DateTime($avtaleData['start_date']);
    $this->assign('startDate', $startDate->format('Y-m-d'));
    if (!empty($avtaleData['end_date'])) {
      $endDate = new DateTime($avtaleData['end_date']);
      $this->assign('endDate', $endDate->format('Y-m-d'));
    }
    if ($avtaleData['status'] == 1) {
      $this->assign('isActive', ts('Yes'));
    } else {
      $this->assign('isActive', ts('No'));
    }
    parent::run();
  }

}
