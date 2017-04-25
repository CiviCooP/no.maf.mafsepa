<?php

/**
 * Class to generate a OCR file for MAF Norge
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 March 2017
 * @license AGPL-3.0
 */

require_once 'CRM/Core/Page.php';

class CRM_Mafsepa_Page_AvtaleGiroFile extends CRM_Core_Page {
  function run() {
    CRM_Utils_System::setTitle(ts('Generate AvtaleGiro OCR File', array('domain' => 'no.maf.mafsepa')));

    $sddFileId = (int)CRM_Utils_Request::retrieve('id', 'Positive', $this);
    if ($sddFileId > 0) {
      //fetch the file, then the group
      $file = new CRM_Mafsepa_AvtaleGiro();
      $file->setOCRProperties();
      $ocr = $file->generateOCR($sddFileId);
      header('Content-Type: text/plain; charset=utf-8');
      echo $ocr;

      CRM_Utils_System::civiExit();
    } else {
      CRM_Core_Error::fatal("missing parameter. you need id");
      return;
    }
    parent::run();
  }
}
