<?php
/**
 * Tests for plugin/ruby.inc.php
 *
 * PukiWiki標準プラグインだが、QHMでメッセージシステム(get_qm)に拡張されている部分をテスト
 *
 * @covers plugin_ruby_inline
 */

use PHPUnit\Framework\TestCase;

class PluginRubyTest extends TestCase
{
  public static function setUpBeforeClass(): void
  {
    require_once LIB_DIR . 'func.php';
    require_once LIB_DIR . 'html.php';
    require_once LIB_DIR . 'qhm_message.php';
    require_once PLUGIN_DIR . 'ruby.inc.php';
  }

  protected function setUp(): void
  {
    // Reset QHM_Message singleton
    $reflection = new ReflectionClass('QHM_Message');
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
  }

  /**
   * @test
   * QHM拡張: エラー時にget_qm()からメッセージを取得
   */
  public function inline_returns_qhm_error_message_with_invalid_args(): void
  {
    $qm = get_qm();
    $expectedError = $qm->m['plg_ruby']['err_usage'];

    $result = plugin_ruby_inline();
    $this->assertSame($expectedError, $result);
  }

  /**
   * @test
   * QHM拡張: 空のrubyでもget_qm()からメッセージを取得
   */
  public function inline_returns_qhm_error_message_with_empty_ruby(): void
  {
    $qm = get_qm();
    $expectedError = $qm->m['plg_ruby']['err_usage'];

    $result = plugin_ruby_inline('', 'text');
    $this->assertSame($expectedError, $result);
  }

  /**
   * @test
   * QHM拡張: 空のbodyでもget_qm()からメッセージを取得
   */
  public function inline_returns_qhm_error_message_with_empty_body(): void
  {
    $qm = get_qm();
    $expectedError = $qm->m['plg_ruby']['err_usage'];

    $result = plugin_ruby_inline('ruby', '');
    $this->assertSame($expectedError, $result);
  }
}