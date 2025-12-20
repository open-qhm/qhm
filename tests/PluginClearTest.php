<?php

use PHPUnit\Framework\TestCase;

class PluginClearTest extends TestCase
{
  public static function setUpBeforeClass(): void
  {
      require_once PLUGIN_DIR . 'clear.inc.php';
  }

  /**
   * @test
   */
  public function convert_returns_div_with_clear_class(): void
  {
    $result = plugin_clear_convert();
    $this->assertSame('<div class="clear"></div>', $result);
  }
}