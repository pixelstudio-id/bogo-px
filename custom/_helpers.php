<?php

/**
 * Get the IP address of visitor
 */
static function bogo_get_visitor_ip() {
  $ip = $_SERVER['REMOTE_ADDR'];
  if ($deep_detect) {
    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
  }

  return $ip;
}