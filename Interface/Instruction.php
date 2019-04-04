<?php
/**
 * Created by PhpStorm.
 * User: pekas
 * Date: 4/4/2019
 * Time: 1:48 PM
 */

interface Instruction
{
  public function hasNoOperand($instruction);
  public function hasVar($instruction);
  public function hasLabel($instruction);
  public function hasSymb($instruction);
  public function hasVarSymb($instruction);
  public function hasVarSymbSymb($instruction);
  public function hasLabelSymbSymb($instruction);
  public function hasVarType($instruction);
}