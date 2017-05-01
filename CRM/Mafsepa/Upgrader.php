<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Mafsepa_Upgrader extends CRM_Mafsepa_Upgrader_Base {

  protected $_wordReplacements = array(
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
    array(
      'original' => 'Recurring Contributions',
      'replacement' => 'Avtale Giros',
      'exactMatch' => true,
    ),
    array(
      'original' => 'Recurring Contribution',
      'replacement' => 'Avtale Giro',
      'exactMatch' => true,
    ),
  );

  public function postInstall() {
    // set word replacements
    foreach($this->_wordReplacements as $replacement) {
      $params = array();
      $params['find_word'] = $replacement['original'];
      $params['replace_word'] = $replacement['replacement'];
      if (!empty($replacement['exactMatch'])) {
        $params['match_type'] = "exactMatch";
      }
      civicrm_api3('WordReplacement', 'create', $params);
    }
    // set payment instrument labels
    $avtaleGiro = new CRM_Mafsepa_AvtaleGiro();
    $avtaleGiro->initializePaymentLabels();
  }

  public function uninstall() {
    foreach($this->_wordReplacements as $replacement) {
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
    // set payment instrument labels
    $avtaleGiro = new CRM_Mafsepa_AvtaleGiro();
    $avtaleGiro->resetPaymentLabels();
  }

  public function enable() {
    foreach($this->_wordReplacements as $replacement) {
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
      // set payment instrument labels
      $avtaleGiro = new CRM_Mafsepa_AvtaleGiro();
      $avtaleGiro->initializePaymentLabels();
    }
  }

  public function disable() {
    foreach($this->_wordReplacements as $replacement) {
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
    // set payment instrument labels
    $avtaleGiro = new CRM_Mafsepa_AvtaleGiro();
    $avtaleGiro->resetPaymentLabels();
  }

}
