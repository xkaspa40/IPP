<?php
require dirname(__DIR__,1) . '/Interface/Instruction.php';

class Opcode implements Instruction
{
  /**
   * These arrays ease analysis
   */
  private $noOperandInstructions = array("createframe", "pushframe", "popframe", "return", "break");
  private $varInstructions = array("defvar", "pops");
  private $labelInstructions = array("call", "label", "jump");
  private $symbInstructions = array("pushs", "write", "exit", "dprint");
  private $varSymbInstructions = array("move", "int2char", "strlen", "type");
  private $varSymbSymbInstructions = array("add", "sub", "mul", "idiv", "lt", "gt", "eq", "and", "or", "not", "stri2int", "concat",
    "getchar", "setchar");
  private $labelSymbSymbInstructions = array("jumpifeq", "jumpifneq");
  private $varTypeInstructions = array("read");

  /**
   * @param $instruction string
   * @return bool
   */
  public function hasNoOperand($instruction) : bool
  {
    return in_array(strtolower($instruction), $this->noOperandInstructions);
  }

  /**
   * @param $instruction string
   * @return bool
   */
  public function hasVar($instruction) : bool
  {
    return in_array(strtolower($instruction), $this->varInstructions);
  }

  /**
   * @param $instruction string
   * @return bool
   */
  public function hasLabel($instruction) : bool
  {
    return in_array(strtolower($instruction), $this->labelInstructions);
  }

  /**
   * @param $instruction string
   * @return bool
   */
  public function hasSymb($instruction) : bool
  {
    return in_array(strtolower($instruction), $this->symbInstructions);
  }

  /**
   * @param $instruction string
   * @return bool
   */
  public function hasVarSymb($instruction) : bool
  {
    return in_array(strtolower($instruction), $this->varSymbInstructions);
  }

  /**
   * @param $instruction string
   * @return bool
   */
  public function hasVarSymbSymb($instruction) : bool
  {
    return in_array(strtolower($instruction), $this->varSymbSymbInstructions);
  }

  /**
   * @param $instruction string
   * @return bool
   */
  public function hasLabelSymbSymb($instruction) : bool
  {
    return in_array(strtolower($instruction), $this->labelSymbSymbInstructions);
  }

  /**
   * @param $instruction string
   * @return bool
   */
  public function hasVarType($instruction) : bool
  {
    return in_array(strtolower($instruction), $this->varTypeInstructions);
  }
}
