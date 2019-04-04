<?php


interface Argument
{
  public function checkLabel($label);
  public function checkVar($variable);
  public function checkSymb($symb);
  public function isComment($comm);
}