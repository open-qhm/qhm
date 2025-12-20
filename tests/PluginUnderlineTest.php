<?php

use PHPUnit\Framework\TestCase;

class PluginUnderlineTest extends TestCase
{
  public static function setUpBeforeClass(): void
  {
      require_once PLUGIN_DIR . 'underline.inc.php';
  }

  /**
   * @test
   */
  public function inline_wraps_text_with_underline_style(): void
  {
    $result = plugin_underline_inline('test text');
    $this->assertSame('<span style="text-decoration:underline">test text</span>', $result);
  }

  /**
   * @test
   */
  public function inline_with_empty_text(): void
  {
    $result = plugin_underline_inline('');
    $this->assertSame('<span style="text-decoration:underline"></span>', $result);
  }

  /**
   * @test
   */
  public function inline_preserves_html_in_text(): void
  {
    $result = plugin_underline_inline('<strong>bold</strong>');
    $this->assertSame('<span style="text-decoration:underline"><strong>bold</strong></span>', $result);
  }
}