<?php
class IcollectUtils{
  public static function htmlDecode($v) {
    $v = str_replace('&gt;', '>', $v);
    $v = str_replace('&lt;', '<', $v);
    $v = str_replace('&quot;', '"', $v);
    $v = str_replace('&amp;', '&', $v);
    $v = str_replace('&#1001;', "'", $v);
    $v = str_replace('&#1002;', "(", $v);
    $v = str_replace('&#1003;', ")", $v);
    $v = str_replace('&#1004;', "/", $v);
    return wp_kses_post($v);
  }  
  public static function create_folders($dir) {
    return is_dir($dir) or (IcollectUtils::create_folders(dirname($dir)) and mkdir($dir, 0777));
  }
  public static function randFloat($min = 0, $max = 1) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
  }
  public static function randomIp() {
    return rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
  }
  public static function getHomeUrl() {
    $domain='';
    if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
      $domain= "https://";
    } else {
      $domain= "http://";
    }
    $domain = $domain.str_replace('\\', '/', $_SERVER['HTTP_HOST']);
    return $domain;
  }
}
?>