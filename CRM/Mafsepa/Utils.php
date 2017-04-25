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
}