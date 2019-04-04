<?php
/**
 * Created by PhpStorm.
 * User: pekas
 * Date: 4/4/2019
 * Time: 2:06 PM
 */

interface Statistics
{
  public static function getInstance($args);
  public function addComments();
  public function addJumps();
  public function addLabels($label);
  public function addLoc();
}