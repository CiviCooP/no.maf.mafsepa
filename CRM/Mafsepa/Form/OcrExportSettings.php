<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Mafsepa_Form_OcrExportSettings extends CRM_Core_Form {

  private $_employeesList = array();



  /**
   * Method to set the list of employees
   */
  private function setEmployeesList() {
    try {
      $relationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Employee of',
        'name_b_a' => 'Employer of',
        'return' => 'id'
      ));
      $employees = civicrm_api3('Relationship', 'get', array(
        'relationship_type_id' => $relationshipTypeId,
        'contact_id_b' => 1,
        'is_active' => 1,
        'options' => array('limit' => 0,),
      ));
      foreach ($employees['values'] as $relationshipId => $relationship) {
        $contactName = civicrm_api3('Contact', 'getvalue', array(
          'id' => $relationship['contact_id_a'],
          'return' => 'display_name',
        ));
        $this->_employeesList[$relationship['contact_id_a']] = $contactName;
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    // if no employees found, set status and use default organization
    if (empty($this->_employeesList)) {
      try {
        $contactName = civicrm_api3('Contact', 'getvalue', array(
          'id' => 1,
          'return' => 'display_name',
        ));
        $this->_employeesList[1] = $contactName;
        CRM_Core_Session::setStatus(ts('No MAF Norge employees found, default organization used. Add employee relationships for MAF employees!', 'No MAF emplyees found',
          'alert'));
      }
      catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not find contact 1 in '.__METHOD__
          .', contact your system administrator. Error from API Contact getvalue: '.$ex->getMessage());
      }
    }
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    CRM_Utils_System::setTitle(ts('OCR Export Settings'));
    $this->setEmployeesList();
  }

  /**
   * Overridden parent method to build form
   */
  public function buildQuickForm() {
    $this->add('text', 'nets_customer_id', ts('NETS Customer ID'), array(), TRUE);
    $this->add('text', 'nets_id', ts('NETS ID'), array(), TRUE);
    $this->add('text', 'format_code', ts('Format Code'), array(), TRUE);
    $this->add('text', 'start_service_code', ts('Start Service Code'), array(), TRUE);
    $this->add('text', 'start_transmission_type', ts('Start Transmission Type'), array(), TRUE);
    $this->add('text', 'start_record_type', ts('Start Record Type'), array(), TRUE);
    $this->add('text', 'end_service_code', ts('End Service Code'), array(), TRUE);
    $this->add('text', 'end_transmission_type', ts('End Transmission Type'), array(), TRUE);
    $this->add('text', 'end_record_type', ts('End Record Type'), array(), TRUE);
    $this->add('text', 'assignment_account', ts('Assignment Account'), array(), TRUE);
    $this->add('text', 'avtale_giro_service_code', ts('AvtaleGiro Service Code'), array(), TRUE);
    $this->add('text', 'assignment_record_type', ts('Assignment Record Type'), array(), TRUE);
    $this->add('text', 'with_notification_transaction_type', ts('Notificiation On Transaction Type'), array(), TRUE);
    $this->add('text', 'without_notification_transaction_type', ts('Notificiation Off Transaction Type'), array(), TRUE);
    $this->add('text', 'first_contribution_line_record_type', ts('1st Transaction Line Record Type'), array(), TRUE);
    $this->add('text', 'second_contribution_line_record_type', ts('2nd Transaction Line Record Type'), array(), TRUE);
    $this->add('text', 'end_assignment_line_record_type', ts('End Assignment Line Record Type'), array(), TRUE);
    $this->add('text', 'default_external_ref', ts('Default External Reference'), array(), TRUE);
    $this->add('text', 'membership_external_ref', ts('Membership External Reference'), array(), TRUE);
    $this->add('select', 'activity_assignee_id', ts('Assign Error Activity To'), $this->_employeesList, TRUE);

    // add buttons
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel')),
    ));
    parent::buildQuickForm();
  }

  /**
   * Overridden parent method to process submitted form
   */
  public function postProcess() {
    $data = array();
    $ignores = array('entryURL', 'qfKey');
    foreach ($this->_submitValues as $submitKey => $submitValue) {
      // if key does not exists in ignores or in avtale defaults
      if (!in_array($submitKey, $ignores)) {
        // if key does not start with '_qf'
        if (substr($submitKey, 0, 3) != '_qf') {
          $data[$submitKey] = $submitValue;
        }
      }
    }
    if (!empty($data)) {
      $this->saveJsonFile($data);
    }

    CRM_Core_Session::setStatus(ts('OCR Export Settings saved to JSON file ocr_export_settings.json in extension folder').' resources.',
      'OCR Export Settings saved', 'success');
    parent::postProcess();
  }

  /**
   * Method to save json file
   *
   * @param $data
   * @throws Exception when file can not be opened for write
   */
  private function saveJsonFile($data) {
    if (!empty($data)) {
      $container = CRM_Extension_System::singleton()->getFullContainer();
      $fileName = $container->getPath('no.maf.mafsepa').'/resources/ocr_export_settings.json';
      try {
        $fh = fopen($fileName, 'w');
        fwrite($fh, json_encode($data, JSON_PRETTY_PRINT));
        fclose($fh);
      } catch (Exception $ex) {
        throw new Exception('Could not open '.$fileName.', contact your system administrator. Error reported: '
          . $ex->getMessage());
      }
    }

  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  public function setDefaultValues() {
    $defaults = array();
    $ocrDefaults = CRM_Mafsepa_Utils::readDefaultsJson('ocr_export_settings');
    foreach ($ocrDefaults as $ocrDefaultKey => $ocrDefaultValue) {
      $defaults[$ocrDefaultKey] = $ocrDefaultValue;
    }
    return $defaults;
  }

}
