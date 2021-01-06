<?php
namespace Be\Data\System\Config;

class Watermark
{
  public $enable = 1;
  public $type = 'text';
  public $position = 'south';
  public $offsetX = -70;
  public $offsetY = -70;
  public $image = '';
  public $text = 'BE';
  public $textSize = 20;
  public $textColor = [255,255,255];
}
