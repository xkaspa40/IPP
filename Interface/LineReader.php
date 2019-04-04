<?php
/**
 * Created by PhpStorm.
 * User: pekas
 * Date: 4/4/2019
 * Time: 1:50 PM
 */

interface LineReader
{
  public static function getInstance();
  public function generateLine();
  public function getInstructionArray($line);
}
