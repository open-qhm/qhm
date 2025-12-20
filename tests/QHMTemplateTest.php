<?php

use PHPUnit\Framework\TestCase;

class QHMTemplateTest extends TestCase
{
  private $qt;

  public static function setUpBeforeClass(): void
  {
      define('CACHE_DIR', BASE_PATH . '/cache/');
      define('CONTENT_CHARSET', 'UTF-8');
      define('TEMPLATE_ENCODE', 'UTF-8');
      require_once LIB_DIR . 'func.php';
      require_once LIB_DIR . 'qhm_template.php';
  }

  protected function setUp(): void
  {
    // Reset singleton for each test
    $reflection = new ReflectionClass('QHM_Template');
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);

    $this->qt = QHM_Template::get_instance();
  }

  /**
   * @test
   */
  public function get_instance_returns_singleton(): void
  {
    $instance1 = QHM_Template::get_instance();
    $instance2 = QHM_Template::get_instance();
    $this->assertSame($instance1, $instance2);
  }

  /**
   * @test
   */
  public function setv_and_getv_store_and_retrieve_values(): void
  {
    $this->qt->setv('key1', 'value1');
    $this->assertSame('value1', $this->qt->getv('key1'));
  }

  /**
   * @test
   */
  public function getv_returns_false_for_nonexistent_key(): void
  {
    $this->assertFalse($this->qt->getv('nonexistent'));
  }

  /**
   * @test
   */
  public function setv_returns_false_for_empty_key(): void
  {
    $this->assertFalse($this->qt->setv('', 'value'));
  }

  /**
   * @test
   */
  public function setv_once_sets_value_only_first_time(): void
  {
    $this->qt->setv_once('key', 'first');
    $this->qt->setv_once('key', 'second');
    $this->assertSame('first', $this->qt->getv('key'));
  }

  /**
   * @test
   */
  public function appendv_appends_to_existing_value(): void
  {
    $this->qt->setv('key', 'hello');
    $this->qt->appendv('key', ' world');
    $this->assertSame('hello world', $this->qt->getv('key'));
  }

  /**
   * @test
   */
  public function appendv_creates_new_value_if_not_exists(): void
  {
    $this->qt->appendv('newkey', 'value');
    $this->assertSame('value', $this->qt->getv('newkey'));
  }

  /**
   * @test
   */
  public function prependv_prepends_to_existing_value(): void
  {
    $this->qt->setv('key', 'world');
    $this->qt->prependv('key', 'hello ');
    $this->assertSame('hello world', $this->qt->getv('key'));
  }

  /**
   * @test
   */
  public function prependv_creates_new_value_if_not_exists(): void
  {
    $this->qt->prependv('newkey', 'value');
    $this->assertSame('value', $this->qt->getv('newkey'));
  }

  /**
   * @test
   */
  public function appendv_once_appends_only_first_time(): void
  {
    $this->qt->appendv_once('hash1', 'key', 'first');
    $this->qt->appendv_once('hash1', 'key', 'second');
    $this->assertSame('first', $this->qt->getv('key'));
  }

  /**
   * @test
   */
  public function prependv_once_prepends_only_first_time(): void
  {
    $this->qt->setv('key', 'end');
    $this->qt->prependv_once('hash1', 'key', 'start ');
    $this->qt->prependv_once('hash1', 'key', 'ignored ');
    $this->assertSame('start end', $this->qt->getv('key'));
  }

  /**
   * @test
   */
  public function is_appended_returns_true_after_appendv_once(): void
  {
    $this->assertFalse($this->qt->is_appended('hash1'));
    $this->qt->appendv_once('hash1', 'key', 'value');
    $this->assertTrue($this->qt->is_appended('hash1'));
  }

  /**
   * @test
   */
  public function get_values_returns_all_values(): void
  {
    $this->qt->setv('key1', 'value1');
    $this->qt->setv('key2', 'value2');
    $values = $this->qt->get_values();
    $this->assertSame('value1', $values['key1']);
    $this->assertSame('value2', $values['key2']);
  }

  /**
   * @test
   */
  public function convert_php_replaces_hash_syntax(): void
  {
    $method = new ReflectionMethod('QHM_Template', 'convert_php');
    $method->setAccessible(true);

    // Create a temp file with template syntax
    $tempFile = tempnam(sys_get_temp_dir(), 'qhm_test');
    file_put_contents($tempFile, '#{$title}');

    $result = $method->invoke($this->qt, $tempFile);
    $this->assertSame('<?php echo $title; ?>', $result);

    unlink($tempFile);
  }

  /**
   * @test
   */
  public function convert_php_replaces_percent_syntax_with_escape(): void
  {
    $method = new ReflectionMethod('QHM_Template', 'convert_php');
    $method->setAccessible(true);

    $tempFile = tempnam(sys_get_temp_dir(), 'qhm_test');
    file_put_contents($tempFile, '%{$userInput}');

    $result = $method->invoke($this->qt, $tempFile);
    $this->assertSame('<?php echo h($userInput); ?>', $result);

    unlink($tempFile);
  }

  /**
   * @test
   */
  public function convert_php_handles_multiple_replacements(): void
  {
    $method = new ReflectionMethod('QHM_Template', 'convert_php');
    $method->setAccessible(true);

    $tempFile = tempnam(sys_get_temp_dir(), 'qhm_test');
    file_put_contents($tempFile, '<h1>#{$title}</h1><p>%{$content}</p>');

    $result = $method->invoke($this->qt, $tempFile);
    $this->assertSame('<h1><?php echo $title; ?></h1><p><?php echo h($content); ?></p>', $result);

    unlink($tempFile);
  }
}