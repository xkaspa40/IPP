<?php
require dirname(__DIR__,1) . '/Interface/Argument.php';

class Operand implements Argument
{

  /**
   * @param string $frame
   * @return bool
   */
  private function checkFrameOk($frame): bool
  {
    if ($frame != 'GF@' && $frame != 'LF@' && $frame != 'TF@') {
      return false;
    }
    return true;
  }

  /**
   * @param $varName
   * @return bool
   */
  public function checkLabel($varName): bool
  {
    return preg_match("/^[a-zA-Z\-\$&%\*!\?]+[\w\-\$&%\*!\?]*$/", $varName);
  }

  /**
   * @param string $var
   * @return bool
   */
  public function checkVar($var): bool
  {
    $frame = substr($var, 0, 3);
    $name = substr($var, 3);
    if ($this->checkLabel($name) && $this->checkFrameOk($frame)){
      return true;
    }
    return false;
  }


  /**
   * @param $val
   * @return bool
   */
  private function checkStringValue($val): bool
  {
    $str = str_split($val);
    $state = 0;
    foreach ($str as $char)
    {
      switch($state)
      {
        case 0:
          if($char == "\\")
          {
            $state = 1;
          }
          else if($char == "#")
          {
            return false;
          }
          else if(preg_match("/\p{Cn}/", $char))
          {
            $state = 4;
          }
          break;
        case 1:
          if(preg_match("/\d/", $char))
          {
            $state = 2;
          }
          else
          {
            return false;
          }
          break;
        case 2:
          if(preg_match("/\d/", $char))
          {
            $state = 3;
          }
          else
          {
            return false;
          }
          break;
        case 3:
          if(preg_match("/\d/", $char))
          {
            $state = 0;
          }
          else
          {
            return false;
          }
          break;
        default:
          return false;
      }
    }
    return true;
  }


  /**
   * @param $val
   * @return bool
   */
  private function checkIntValue($val): bool
  {
    return preg_match("/(\+|\-)?[0-9]*/", $val);
  }

  /**
   * @param $val
   * @return bool
   */
  private function checkBoolValue($val): bool
  {
    $val = strtolower($val);
    return ($val == "true" || $val == "false");
  }

  /**
   * @param $val string
   * @return bool
   */
  private function checkNilValue($val): bool
  {
    return (strtolower($val) == "nil");
  }

  /**
   * @param $type string
   * @param $val string
   * @return bool
   */
  private function checkTypeOk($type, $val): bool
  {
    switch ($type) {
      case "string":
        return $this->checkStringValue($val);
      case "int":
        return $this->checkIntValue($val);
      case "bool":
        return $this->checkBoolValue($val);
      case "nil":
        return $this->checkNilValue($val);
      default:
        return false;
    }
  }

  /**
   * @param $const string
   * @return bool
   */
  private function checkConst($const): bool
  {
    $type = $this->getType($const);
    $val = $this->getValue($const);
    if (!$this->checkTypeOk($type, $val))
    {
      return false;
    }
    return true;
  }

  /**
   * @param $symb
   * @return bool
   */
  public function checkSymb($symb): bool
  {
    return ($this->checkVar($symb) || $this->checkConst($symb));
  }

  /**
   * @param $comm string
   * @return bool
   */
  public function isComment($comm)
  {
    return ($comm[0] == '#');
  }

  /**
   * @param $operand string
   * @return string|mixed
   */
  public function getValue($operand)
  {
    $operand = explode('@', $operand);
    return $operand[1];
  }

  /**
   * @param $operand string
   * @return string|mixed
   */
  public function getType($operand)
  {
    $operand = explode('@', $operand);
    return $operand[0];
  }
}