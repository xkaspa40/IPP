<?php
/**
 * Created by PhpStorm.
 * User: pekas
 * Date: 4/4/2019
 * Time: 1:51 PM
 */

/**
 * Class XMLWrapper
 * Used to ease work with base XMLWriter class
 */
class XMLWrapper extends XMLWriter
{

  /**
   * @param $argnum string arg1 | arg2 | arg3
   * @param $arg string value of argumen
   * Writes operand as XML based on whether operand is var or symb
   */
  public function writeOperandVarOrSymb($argnum, $arg)
  {
    $operand = new Operand();
    if(!$operand->checkVar($arg))
    {
      $this->writeOperand($argnum, $operand->getType($arg), $operand->getValue($arg));
    }
    else
    {
      $this->writeOperand($argnum, "var", $arg);
    }
  }

  /**
   * @param $opCode string
   * @param $order int
   * Writes Opcode as XML [instruction tag]
   */
  public function writeOpcode($opCode, $order)
  {
    $this->startElement('instruction');
    $this->startAttribute('order');
    $this->text($order);
    $this->endAttribute();
    $this->startAttribute('opcode');
    $this->text(strtoupper($opCode));
    $this->endAttribute();
  }

  /**
   * @param $arg string
   * @param $type string
   * @param $val string
   * Used for writing operands as XML with explicitly said type
   */
  public function writeOperand($arg, $type, $val)
  {
    $this->startElement($arg);
    $this->startAttribute('type');
    $this->text($type);
    $this->endAttribute();
    $this->text($val);
    $this->endElement();
  }

}