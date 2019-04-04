<?php
require './Class/LineGenerator.php';
require './Class/StatisticsManager.php';
require './Class/Opcode.php';
require './Class/XMLWrapper.php';
require './Class/Operand.php';

$generator = LineGenerator::getInstance();
$statisticsManager = StatisticsManager::getInstance($argv);
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
    }
    $xw->writeOpcode($instruction[0], $order);
    $xw->endElement();
    $statisticsManager->addLoc();
    $order++;
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
