<?php
require dirname(__DIR__, 1) . '/Interface/LineReader.php';


/**
 * Class LineGenerator
 * Using Singleton pattern
 */
class LineGenerator implements LineReader
{

  private static $instance = NULL;

  /**
   * @return LineGenerator|null
   */
  public static function getInstance()
  {
    if (self::$instance == NULL)
    {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct()
  {
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
        //we need singleton so StatisticsManager "remebers" this operation
        $stats = StatisticsManager::getInstance(NULL);
        $stats->addComments();
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
}