<?php
/**
 * Created by PhpStorm.
 * User: pekas
 * Date: 2/11/2019
 * Time: 2:00 PM
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


//TODO refactor type methods
interface Argument
{
  public function checkLabel($label);
  public function checkVar($variable);
  public function checkSymb($symb);
  public function isComment($comm);
}

interface LineReader
{
  public static function getInstance($args);
  public function generateLine();
  public function getInstructionArray($line);
  public function addComments();
  public function addJumps();
  public function addLabels($label);
  public function addLoc();
}

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
    $at = strpos($const, "@");
    $type = substr($const, 0, $at);
    $val = substr($const, ++$at);
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


/**
 * Class LineGenerator
 * Using Singleton pattern
 */
class LineGenerator implements LineReader
{

  private static $instance = NULL;
  private $labels = array();
  private $stats = array();
  private $file = NULL;

  /**
   * @param $args mixed ; args passed when program is executed; has no significance when instance was already created
   * @return LineGenerator|null
   */
  public static function getInstance($args)
  {
    if (self::$instance == NULL)
    {
      self::$instance = new self($args);
    }
    return self::$instance;
  }

  /**
   * LineGenerator constructor.
   * @param $args mixed arguments passed to program
   */
  private function __construct($args)
  {
    $help = false;
    foreach($args as $arg)
    {
      if($arg == "--help")
      {
        $help = true;
      }
      if(substr($arg, 0, 8) == "--stats=")
      {
        $this->file = substr($arg, 8);
      }
      if($arg == "--loc")
      {
        $this->stats["loc"] = 0;
      }
      if($arg == "--comments")
      {
        $this->stats["comments"] = 0;
      }
      if($arg == "--labels")
      {
        $this->stats["labels"] = 0;
      }
      if($arg == "--jumps")
      {
        $this->stats["jumps"] = 0;
      }
    }
    if($help)
    {
      if(!empty($this->stats) || $this->file != NULL)
      {
        exit(10);
      }
      echo("Usage: php7.3 parse.php \n
      You can use these arguments: 
      --help (you already know this one) - this can't be combined with another arguments 
      --stats=FILE - writes statistics inside FILE according to following arguments: 
      --loc - lines of code 
      --comments 
      --jumps 
      --labels - counts each label only once. 
      ");

      exit(0);
    }
    if(!$this->checkStatArgsOk())
    {
      exit(10);
    }
  }

  /**
   * Checks if --stats=file was passed when another STATP arguments are present
   * @return bool
   */
  private function checkStatArgsOk() : bool
  {
    if(!empty($this->stats) && $this->file == NULL)
    {
      return false;
    }
    return true;
  }


  public function addComments() : void
  {
    if(array_key_exists("comments", $this->stats))
    {
      $this->stats["comments"]++;
    }
    return;
  }

  public function addLoc() : void
  {
    if(array_key_exists("loc", $this->stats))
    {
      $this->stats["loc"]++;
    }
    return;
  }

  public function addJumps() : void
  {
    if(array_key_exists("jumps", $this->stats))
    {
      $this->stats["jumps"]++;
    }
  }

  /**
   * @param $label string if label already exists, it's not counted
   */
  public function addLabels($label) : void
  {
    if(array_key_exists("labels", $this->stats) && !in_array($label, $this->labels))
    {
      $this->stats["labels"]++;
      array_push($this->labels, $label);
    }
  }

  /**
   * @return mixed $line
   */
  public function generateLine()
  {
    while($line = fgets(STDIN))
    {
      $line = trim($line);
      if(strpos($line, "#") === 0)
      {
        $this->addComments();
        continue;
      }
      else if($line == '')
      {
        continue;
      }
      yield $line;
    }
    return;
  }

  /**
   * @param $line string
   * @return array which is $line exploded over whitespace
   */
  public function getInstructionArray($line)
  {
    $instruction = explode(" ", $line);

    return $instruction;
  }

  /**
   * First overwrites file just in case there were some data before, then writes statistics line by line
   */
  public function writeStats()
  {
    if($this->file == NULL)
    {
      return;
    }
    else
    {
      file_put_contents($this->file, '');
      foreach ($this->stats as $key => $value)
      {
        file_put_contents($this->file, $value . "\n", FILE_APPEND);
      }
    }
  }
}

$generator = LineGenerator::getInstance($argv);
$statisticsManager = LineGenerator::getInstance(NULL); //LineGenerator uses singleton, have two variables with same instance for cleaner code
$opcode = new Opcode();
$operand = new Operand();

$firstLine = fgets(STDIN);
$firstLine = $generator->getInstructionArray(trim($firstLine));
if(strtolower($firstLine[0]) != ".ippcode19")
{
  exit(21);
}
if(!empty($firstLine[1])){
  if($operand->isComment($firstLine[1]))
  {
    $statisticsManager->addComments();
  }
  else
  {
    exit(21);
  }
}

$order = 1;
$xw = new XMLWrapper();
$xw->openMemory();
$xw->setIndent(true);
$xw->setIndentString(' ');
$xw->startDocument('1.0', 'UTF-8');
$xw->startElement('program');
$xw->startAttribute('language');
$xw->text('IPPcode19');

foreach ($generator->generateLine() as $line)
{
  $instruction = $generator->getInstructionArray($line);

  //Instructions without operands
  if($opcode->hasNoOperand($instruction[0]))
  {
    if(!empty($instruction[1]))
    {
      if($operand->isComment($instruction[1]))
      {
        $statisticsManager->addComments();
      }
      else
      {
        exit(23);
      }
      $xw->writeOpcode($instruction[0], $order);
      $xw->endElement();
      $statisticsManager->addLoc();
      $order++;
    }
  }

  //Instructions with one variable as operand
  elseif($opcode->hasVar($instruction[0]))
  {
    if($operand->checkVar($instruction[1]))
    {
      if(!empty($instruction[2])) {
        if ($operand->isComment($instruction[2]))
        {
          $statisticsManager->addComments();
        }
        else
        {
          exit(23);
        }
      }
      $xw->writeOpcode($instruction[0], $order);
      $xw->writeOperand("arg1", "var", $instruction[1]);
      $xw->endElement();
      $statisticsManager->addLoc();
      $order++;
    }
    else
    {
      exit(23);
    }
  }

  //Instructions with label as operand
  elseif($opcode->hasLabel($instruction[0]))
  {
    if($operand->checkLabel($instruction[1]))
    {
      if(!empty($instruction[2]))
      {
        if ($operand->isComment($instruction[2]))
        {
          $statisticsManager->addComments();
        }
        else
        {
          exit(23);
        }
      }
      if(strtolower($instruction[0]) == 'jump')
      {
        $statisticsManager->addJumps();
      }
      $xw->writeOpcode($instruction[0], $order);
      $xw->writeOperand("arg1", "label", $instruction[1]);
      $xw->endElement();
      $statisticsManager->addLoc();
      $statisticsManager->addLabels($instruction[1]);
      $order++;
    }
    else
    {
      exit(23);
    }
  }

  //Instructions with either constant or variable as operand
  elseif($opcode->hasSymb($instruction[0]))
  {
    if($operand->checkSymb($instruction[1]))
    {
      if(!empty($instruction[2]))
      {
        if($operand->isComment($instruction[2]))
        {
          $statisticsManager->addComments();
        }
        else
        {
          exit(23);
        }
      }
      $xw->writeOpcode($instruction[0], $order);
      $xw->writeOperandVarOrSymb('arg1', $instruction[1]);
      $xw->endElement();
      $statisticsManager->addLoc();
      $order++;
    }
    else
    {
      exit(23);
    }
  }

  //Instructions with variable and either constant or variable as operand
  elseif($opcode->hasVarSymb($instruction[0]))
  {
    if($operand->checkVar($instruction[1]))
    {
      if($operand->checkSymb($instruction[2]))
      {
        if(!empty($instruction[3]))
        {
          if($operand->isComment($instruction[3]))
          {
            $statisticsManager->addComments();
          }
          else
          {
            exit(23);
          }
        }
      }
      else
      {
        exit(23);
      }
      $xw->writeOpcode($instruction[0], $order);
      $xw->writeOperand("arg1", "var", $instruction[1]);
      $xw->writeOperandVarOrSymb('arg2', $instruction[2]);
      $xw->endElement();
      $statisticsManager->addLoc();
      $order++;
    }
    else
    {
      exit(23);
    }
  }

  //Instructions with variable and either constant or variable [two] as operand
  elseif($opcode->hasVarSymbSymb($instruction[0]))
  {
    if($operand->checkVar($instruction[1]))
    {
      if($operand->checkSymb($instruction[2]))
      {
        if($operand->checkSymb($instruction[3]))
        {
          if(!empty($instruction[4]))
          {
            if($operand->isComment($instruction[4]))
            {
              $statisticsManager->addComments();
            }
            else
            {
              exit(23);
            }
          }
        }
        else
        {
          exit(23);
        }
      }
      else
      {
        exit(23);
      }
      $xw->writeOpcode($instruction[0], $order);
      $xw->writeOperand("arg1", "var", $instruction[1]);
      $xw->writeOperandVarOrSymb('arg2', $instruction[2]);
      $xw->writeOperandVarOrSymb('arg3', $instruction[3]);
      $xw->endElement();
      $statisticsManager->addLoc();
      $order++;
    }
    else
    {
      exit(23);
    }
  }

  //Instructions with label and either constant or variable [two] as operand
  elseif($opcode->hasLabelSymbSymb($instruction[0]))
  {
    if($operand->checkLabel($instruction[1]))
    {
      if($operand->checkSymb($instruction[2]))
      {
        if($operand->checkSymb($instruction[3]))
        {
          if(!empty($instruction[4]))
          {
            if($operand->isComment($instruction[4]))
            {
              $statisticsManager->addComments();
            }
            else
            {
              exit(23);
            }
          }
        }
        else
        {
          exit(23);
        }
      }
      else
      {
        exit(23);
      }
      $xw->writeOpcode($instruction[0], $order);
      $xw->writeOperand("arg1", "label", $instruction[1]);
      $xw->writeOperandVarOrSymb('arg2', $instruction[2]);
      $xw->writeOperandVarOrSymb('arg3', $instruction[3]);
      $xw->endElement();
      $statisticsManager->addJumps();
      $statisticsManager->addLabels($instruction[1]);
      $statisticsManager->addLoc();
      $order++;
    }
    else
    {
      exit(23);
    }
  }

  //Instructions with variable and type as operands
  elseif($opcode->hasVarType($instruction[0]))
  {
    if($operand->checkVar($instruction[1]))
    {
      if($instruction[2] == "int" || $instruction[2] == "string" || $instruction[2] == "bool" || $instruction[2] == "nil")
      {
        if(!empty($instruction[3]))
        {
          if($operand->isComment($instruction[3]))
          {
            $statisticsManager->addComments();
          }
          else
          {
            exit(23);
          }
        }
      }
      else
      {
        exit(23);
      }
    }
    else
    {
      exit(23);
    }
    $xw->writeOpcode($instruction[0], $order);
    $xw->writeOperand('arg1', 'var', $instruction[1]);
    $xw->writeOperand('arg2', 'type', $instruction[2]);
    $xw->endElement();
    $statisticsManager->addLoc();
    $order++;
  }
  else
  {
    exit(22);
  }
}
$statisticsManager->writeStats();  //does nothing if statistics aren't being collected
$xw->endDocument();
echo($xw->outputMemory());  //writing out the XML output
exit(0);
