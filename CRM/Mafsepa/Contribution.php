<?php

/**
 * Class for extension specific Contribution processing
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 26 Map 2017
 * @license AGPL-3.0
 */
class CRM_Mafsepa_Contribution {
  /**
   * Process validateForm hook
   *
   * @param $fields
   * @param $form
   * @param $errors
   */
  public static function validateForm($fields, $form, &$errors) {
    if ($form->_action == CRM_Core_Action::ADD || $form->_action == CRM_Core_Action::UPDATE) {
      if (empty($fields['campaign_id'])) {
        $errors['campaign_id'] = ts('You have to enter a campaign for the contribution!');
      }
    }
  }
}