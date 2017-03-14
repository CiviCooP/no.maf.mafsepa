<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Mafsepa_Upgrader extends CRM_Mafsepa_Upgrader_Base {

  protected $wordReplacements = array(
    array(
      'original' => 'SEPA',
      'replacement' => 'Avtale Giro',
      'exactMatch' => false,
    ),
    array(
      'original' => 'CiviSEPA Dashboard',
      'replacement' => 'Avtale Giro Dashboard',
      'exactMatch' => true,
    ),
  );

  public function postInstall() {
    foreach($this->wordReplacements as $replacement) {
      $params = array();
      $params['find_word'] = $replacement['original'];
      $params['replace_word'] = $replacement['replacement'];
      if (!empty($replacement['exactMatch'])) {
        $params['match_type'] = "exactMatch";
      }
      civicrm_api3('WordReplacement', 'create', $params);
    }
    // replace url for menu option SEPA dashboard to MAF AvtaleGiro dashboard
    new CRM_Mafsepa_AvtaleGiro();
  }

  public function uninstall() {
    foreach($this->wordReplacements as $replacement) {
      try {
        $params = array();
        $params['find_word'] = $replacement['original'];
        $params['replace_word'] = $replacement['replacement'];
        $word_replacement = civicrm_api3('WordReplacement', 'getsingle', $params);
        civicrm_api3('WordReplacement', 'delete', array('id' => $word_replacement['id']));
      } catch (Exception $e) {
        // Do nothing;
      }
    }
  }

  public function enable() {
    foreach($this->wordReplacements as $replacement) {
      try {
        $params = array();
        $params['find_word'] = $replacement['original'];
        $params['replace_word'] = $replacement['replacement'];
        $word_replacement = civicrm_api3('WordReplacement', 'getsingle', $params);
        $word_replacement['is_active'] = '1';
        civicrm_api3('WordReplacement', 'create', $word_replacement);
      } catch (Exception $e) {
        // Do nothing;
      }
    }
  }

  public function disable() {
    foreach($this->wordReplacements as $replacement) {
      try {
        $params = array();
        $params['find_word'] = $replacement['original'];
        $params['replace_word'] = $replacement['replacement'];
        $word_replacement = civicrm_api3('WordReplacement', 'getsingle', $params);
        $word_replacement['is_active'] = '0';
        civicrm_api3('WordReplacement', 'create', $word_replacement);
      } catch (Exception $e) {
        // Do nothing;
      }
    }
  }


}
