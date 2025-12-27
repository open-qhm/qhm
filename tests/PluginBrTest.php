<?php
/**
 * Tests for plugin/br.inc.php
 *
 * @covers plugin_br_inline
 */

use PHPUnit\Framework\TestCase;

class PluginBrTest extends TestCase
{
  public static function setUpBeforeClass(): void
  {
      require_once PLUGIN_DIR . 'br.inc.php';
  }

  /**
   * @test
   */
  public function inline_returns_br_tag(): void
  {
    $result = plugin_br_inline();
    $this->assertSame('<br class="spacer" />', $result);
  }

  /**
   * @test
   */
  public function convert_returns_div_with_spacer_class(): void
  {
    $result = plugin_br_convert();
    $this->assertMatchesRegularExpression('/<div id="plugin_br_\d+" class="spacer">&ensp;<\/div>/', $result);
  }

  /**
   * @test
   */
  public function convert_with_margin_returns_styled_div(): void
  {
    $result = plugin_br_convert('20');
    $this->assertMatchesRegularExpression('/<div id="plugin_br_\d+" class="spacer" style="margin-top:20px"><\/div>/', $result);
  }

  /**
   * @test
   */
  public function convert_with_margin_and_unit_preserves_unit(): void
  {
    $result = plugin_br_convert('2em');
    $this->assertMatchesRegularExpression('/<div id="plugin_br_\d+" class="spacer" style="margin-top:2em"><\/div>/', $result);
  }

  /**
   * @test
   */
  public function convert_with_negative_margin(): void
  {
    $result = plugin_br_convert('-10');
    $this->assertMatchesRegularExpression('/<div id="plugin_br_\d+" class="spacer" style="margin-top:-10px"><\/div>/', $result);
  }

  /**
   * @test
   */
  public function convert_increments_id_each_call(): void
  {
    $result1 = plugin_br_convert();
    $result2 = plugin_br_convert();

    preg_match('/plugin_br_(\d+)/', $result1, $match1);
    preg_match('/plugin_br_(\d+)/', $result2, $match2);

    $this->assertGreaterThan((int)$match1[1], (int)$match2[1]);
  }
}