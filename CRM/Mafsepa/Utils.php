<?php

/**
 * Class with helper functions for MAF AvtaleGiro Configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 13 March 2017
 * @license AGPL-3.0
 */
class CRM_Mafsepa_Utils {
  /**
   * Method to replace special chars
   *
   * @param $text
   * @return mixed
   */
  public static function replaceSpecialChars($text) {
    $letters = [
      0 => "a à á â ä æ ã å ā",
      1 => "c ç ć č",
      2 => "e é è ê ë ę ė ē",
      3 => "i ī į í ì ï î",
      4 => "l ł",
      5 => "n ñ ń",
      6 => "o ō ø œ õ ó ò ö ô",
      7 => "s ß ś š",
      8 => "u ū ú ù ü û",
      9 => "w ŵ",
      10 => "y ŷ ÿ",
      11 => "z ź ž ż",
    ];
    foreach ($letters as &$values) {
      $newValue = substr($values, 0, 1);
      $values = substr($values, 2, strlen($values));
      $values = explode(" ", $values);
      foreach ($values as &$oldValue){
        while ($pos=strpos($text,$oldValue)){
          $text = preg_replace("/" . $oldValue . '/', $newValue, $text, 1);
        }
      }
    }
    return $text;
  }
  /**
   * Function to read the defaults json file
   *
   * @param string $fileName
   * @return array|mixed
   */
  public static function readDefaultsJson($fileName) {
    $container = CRM_Extension_System::singleton()->getFullContainer();
    $fileName = $container->getPath('no.maf.mafsepa').'/resources/'.$fileName.'.json';
    if (!file_exists($fileName)) {
      CRM_Core_Session::setStatus('Could not read the defaults file from resources/'.$fileName,
        'Error reading Defaults', 'Error');
      return array();
    } else {
      return json_decode(file_get_contents($fileName), true);
    }
  }

  /**
   * Method creates a new, unique navID for the CiviCRM menu
   * It will consider the IDs from the database,
   * as well as the 'volatile' ones already injected into the menu
   *
   * @param array $menu
   * @return int
   * @access public
   * @static
   */
  public static function createUniqueNavID($menu) {
    $maxStoredNavId = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");
    $maxCurrentNavId = self::getMaxNavID($menu);
    return max($maxStoredNavId, $maxCurrentNavId) + 1;
  }

  /**
   * Method crawls the menu tree to find the (currently) biggest navID
   *
   * @param array $menu
   * @return int
   * @access public
   * @static
   */
  public static function getMaxNavID($menu)   {
    $maxId = 1;
    foreach ($menu as $entry) {
      $maxId = max($maxId, $entry['attributes']['navID']);
      if (!empty($entry['child'])) {
        $maxIdChildren = self::getMaxNavID($entry['child']);
        $maxId = max($maxId, $maxIdChildren);
      }
    }
    return $maxId;
  }

  /**
   * Method to add the given menu item to the CiviCRM navigation menu if it does not exist yet.
   *
   * @param array $parentParams the params array into whose 'child' attribute the new item will be added.
   * @param array $menuEntryAttributes the attributes array to be added to the navigation menu
   * @access public
   * @static
   */
  public static function addNavigationMenuEntry(&$parentParams, $menuEntryAttributes) {
    // see if it is already in the menu...
    $menuItemSearch = array('url' => $menuEntryAttributes['url']);
    $menuItems = array();
    CRM_Core_BAO_Navigation::retrieve($menuItemSearch, $menuItems);

    if (empty($menuItems)) {
      // it's not already contained, so we want to add it to the menu

      // insert at the bottom
      $parentParams['child'][] = array(
        'attributes' => $menuEntryAttributes);
    }
  }

}