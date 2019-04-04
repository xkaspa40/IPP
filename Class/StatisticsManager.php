<?php
require dirname(__DIR__,1) . '/Interface/Statistics.php';



/**
 * Class StatisticsManager
 * Using Singleton pattern
 */
class StatisticsManager implements Statistics
{
  private static $instance = NULL;
  private $labels = array();
  private $stats = array();
  private $file = NULL;

  /**
   * @param $args mixed ; args passed when program is executed; has no significance when instance was already created
   * @return StatisticsManager|null
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