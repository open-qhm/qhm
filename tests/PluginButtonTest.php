<?php
/**
 * Tests for plugin/button.inc.php
 *
 * @covers plugin_button_inline
 * @covers plugin_button_convert
 * @covers plugin_button_body
 */

use PHPUnit\Framework\TestCase;

class PluginButtonTest extends TestCase
{
  private static $testPages = [];

  public static function setUpBeforeClass(): void
  {
    require_once LIB_DIR . 'func.php';
    require_once LIB_DIR . 'file.php';
    require_once LIB_DIR . 'html.php';
    require_once PLUGIN_DIR . 'button.inc.php';

    // Ensure DATA_DIR exists
    if (!is_dir(DATA_DIR)) {
      mkdir(DATA_DIR, 0777, true);
    }
  }

  public static function tearDownAfterClass(): void
  {
    // Clean up all test pages
    foreach (self::$testPages as $page) {
      $file = DATA_DIR . encode($page) . '.txt';
      if (file_exists($file)) {
        unlink($file);
      }
    }
  }

  /**
   * Create a test wiki page
   */
  private static function createTestPage(string $pageName, string $content = 'test'): void
  {
    $file = DATA_DIR . encode($pageName) . '.txt';
    file_put_contents($file, $content);
    self::$testPages[] = $pageName;
  }

  /**
   * @test
   */
  public function body_with_no_args_returns_hash_href(): void
  {
    $result = plugin_button_body([], 'Click me');
    $this->assertStringContainsString('href="#"', $result);
    $this->assertStringContainsString('Click me', $result);
    $this->assertStringContainsString('class="btn btn-default"', $result);
  }

  /**
   * @test
   */
  public function body_with_url_sets_href(): void
  {
    $result = plugin_button_body(['https://example.com'], 'Link');
    $this->assertStringContainsString('href="https://example.com"', $result);
  }

  /**
   * @test
   */
  public function body_with_primary_type(): void
  {
    $result = plugin_button_body(['#', 'primary'], 'Primary');
    $this->assertStringContainsString('btn-primary', $result);
    $this->assertStringNotContainsString('btn-default', $result);
  }

  /**
   * @test
   */
  public function body_with_danger_type(): void
  {
    $result = plugin_button_body(['#', 'danger'], 'Danger');
    $this->assertStringContainsString('btn-danger', $result);
  }

  /**
   * @test
   */
  public function body_with_success_type(): void
  {
    $result = plugin_button_body(['#', 'success'], 'Success');
    $this->assertStringContainsString('btn-success', $result);
  }

  /**
   * @test
   */
  public function body_with_warning_type(): void
  {
    $result = plugin_button_body(['#', 'warning'], 'Warning');
    $this->assertStringContainsString('btn-warning', $result);
  }

  /**
   * @test
   */
  public function body_with_info_type(): void
  {
    $result = plugin_button_body(['#', 'info'], 'Info');
    $this->assertStringContainsString('btn-info', $result);
  }

  /**
   * @test
   */
  public function body_with_link_type(): void
  {
    $result = plugin_button_body(['#', 'link'], 'Link');
    $this->assertStringContainsString('btn-link', $result);
  }

  /**
   * @test
   */
  public function body_with_large_size(): void
  {
    $result = plugin_button_body(['#', 'large'], 'Large');
    $this->assertStringContainsString('btn-lg', $result);
  }

  /**
   * @test
   */
  public function body_with_lg_size(): void
  {
    $result = plugin_button_body(['#', 'lg'], 'Large');
    $this->assertStringContainsString('btn-lg', $result);
  }

  /**
   * @test
   */
  public function body_with_small_size(): void
  {
    $result = plugin_button_body(['#', 'small'], 'Small');
    $this->assertStringContainsString('btn-sm', $result);
  }

  /**
   * @test
   */
  public function body_with_sm_size(): void
  {
    $result = plugin_button_body(['#', 'sm'], 'Small');
    $this->assertStringContainsString('btn-sm', $result);
  }

  /**
   * @test
   */
  public function body_with_mini_size(): void
  {
    $result = plugin_button_body(['#', 'mini'], 'Mini');
    $this->assertStringContainsString('btn-xs', $result);
  }

  /**
   * @test
   */
  public function body_with_xs_size(): void
  {
    $result = plugin_button_body(['#', 'xs'], 'Extra Small');
    $this->assertStringContainsString('btn-xs', $result);
  }

  /**
   * @test
   */
  public function body_with_block(): void
  {
    $result = plugin_button_body(['#', 'block'], 'Block');
    $this->assertStringContainsString('btn-block', $result);
  }

  /**
   * @test
   */
  public function body_with_round(): void
  {
    $result = plugin_button_body(['#', 'round'], 'Round');
    $this->assertStringContainsString('btn-round', $result);
  }

  /**
   * @test
   */
  public function body_with_rounded(): void
  {
    $result = plugin_button_body(['#', 'rounded'], 'Rounded');
    $this->assertStringContainsString('btn-round', $result);
  }

  /**
   * @test
   */
  public function body_with_gradient(): void
  {
    $result = plugin_button_body(['#', 'gradient'], 'Gradient');
    $this->assertStringContainsString('btn-gradient', $result);
  }

  /**
   * @test
   */
  public function body_with_ghost(): void
  {
    $result = plugin_button_body(['#', 'ghost'], 'Ghost');
    $this->assertStringContainsString('btn-ghost', $result);
  }

  /**
   * @test
   */
  public function body_with_ghost_w(): void
  {
    $result = plugin_button_body(['#', 'ghost-w'], 'Ghost White');
    $this->assertStringContainsString('btn-ghost-w', $result);
  }

  /**
   * @test
   */
  public function body_with_window_target(): void
  {
    $result = plugin_button_body(['#', 'window=_blank'], 'New Window');
    $this->assertStringContainsString('target="_blank"', $result);
  }

  /**
   * @test
   */
  public function body_with_custom_class(): void
  {
    $result = plugin_button_body(['#', 'my-custom-class'], 'Custom');
    $this->assertStringContainsString('my-custom-class', $result);
  }

  /**
   * @test
   */
  public function body_with_multiple_options(): void
  {
    $result = plugin_button_body(['https://example.com', 'primary', 'lg', 'block'], 'Full');
    $this->assertStringContainsString('btn-primary', $result);
    $this->assertStringContainsString('btn-lg', $result);
    $this->assertStringContainsString('btn-block', $result);
    $this->assertStringContainsString('href="https://example.com"', $result);
  }

  /**
   * @test
   */
  public function body_escapes_href(): void
  {
    $result = plugin_button_body(['https://example.com?a=1&b=2'], 'Link');
    $this->assertStringContainsString('href="https://example.com?a=1&amp;b=2"', $result);
  }

  /**
   * @test
   */
  public function body_escapes_target(): void
  {
    $result = plugin_button_body(['#', 'window=<script>'], 'XSS');
    $this->assertStringContainsString('target="&lt;script&gt;"', $result);
    $this->assertStringNotContainsString('target="<script>"', $result);
  }

  /**
   * @test
   */
  public function body_with_existing_page_generates_page_link(): void
  {
    global $script;
    $script = 'index.php';

    // Create actual test page
    self::createTestPage('ButtonTestPage');

    $result = plugin_button_body(['ButtonTestPage'], 'Go to Test');
    $this->assertStringContainsString('href="index.php?ButtonTestPage"', $result);
  }

  /**
   * @test
   */
  public function body_with_nonexistent_page_generates_edit_link(): void
  {
    global $script;
    $script = 'index.php';

    // 'NonExistentPage' does not exist
    $result = plugin_button_body(['NonExistentPage'], 'Create Page');
    $this->assertStringContainsString('href="index.php?cmd=edit&amp;page=NonExistentPage"', $result);
  }
}