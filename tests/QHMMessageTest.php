<?php
/**
 * Tests for lib/qhm_message.php
 *
 * @covers QHM_Message
 */

use PHPUnit\Framework\TestCase;

class QHMMessageTest extends TestCase
{
    private static $originalCwd;

    public static function setUpBeforeClass(): void
    {
        // Store original working directory
        self::$originalCwd = getcwd();

        // Change to project root so QHM_Message can find lng.ja.txt
        chdir(BASE_PATH);

        // Define CACHE_DIR if not defined
        if (!defined('CACHE_DIR')) {
            define('CACHE_DIR', BASE_PATH . '/cache');
        }

        require_once LIB_DIR . 'qhm_message.php';
    }

    public static function tearDownAfterClass(): void
    {
        // Restore original working directory
        chdir(self::$originalCwd);
    }

    protected function setUp(): void
    {
        // Reset singleton for each test
        $reflection = new ReflectionClass('QHM_Message');
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }

    /**
     * @test
     */
    public function get_instance_returns_singleton(): void
    {
        $instance1 = QHM_Message::get_instance();
        $instance2 = QHM_Message::get_instance();
        $this->assertSame($instance1, $instance2);
    }

    /**
     * @test
     */
    public function get_qm_returns_singleton_instance(): void
    {
        $instance1 = get_qm();
        $instance2 = get_qm();
        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(QHM_Message::class, $instance1);
    }

    /**
     * @test
     */
    public function messages_are_loaded_from_language_file(): void
    {
        $qm = get_qm();
        $this->assertIsArray($qm->m);
        $this->assertNotEmpty($qm->m);
    }

    /**
     * @test
     */
    public function replace_substitutes_single_placeholder(): void
    {
        $qm = get_qm();
        // Set up test message directly
        $qm->m['test'] = 'Hello $1';

        $result = $qm->replace('test', 'World');
        $this->assertSame('Hello World', $result);
    }

    /**
     * @test
     */
    public function replace_substitutes_multiple_placeholders(): void
    {
        $qm = get_qm();
        $qm->m['test'] = '$1 and $2 and $3';

        $result = $qm->replace('test', 'one', 'two', 'three');
        $this->assertSame('one and two and three', $result);
    }

    /**
     * @test
     */
    public function replace_handles_section_dot_notation(): void
    {
        $qm = get_qm();
        $qm->m['section']['key'] = 'Value is $1';

        $result = $qm->replace('section.key', 'test');
        $this->assertSame('Value is test', $result);
    }

    /**
     * @test
     */
    public function replace_pads_missing_arguments_with_empty_string(): void
    {
        $qm = get_qm();
        $qm->m['test'] = '$1 $2 $3 $4 $5';

        $result = $qm->replace('test', 'only');
        $this->assertSame('only    ', $result);
    }

    /**
     * @test
     */
    public function replace_handles_up_to_five_placeholders(): void
    {
        $qm = get_qm();
        $qm->m['test'] = '$1-$2-$3-$4-$5';

        $result = $qm->replace('test', 'a', 'b', 'c', 'd', 'e');
        $this->assertSame('a-b-c-d-e', $result);
    }

    /**
     * @test
     */
    public function file_property_is_set_correctly(): void
    {
        $qm = get_qm();
        $this->assertSame('lng.ja.txt', $qm->file);
    }

    /**
     * @test
     */
    public function cache_property_is_set_correctly(): void
    {
        $qm = get_qm();
        $this->assertSame(CACHE_DIR . '/lng.ja.qmc', $qm->cache);
    }

    /**
     * @test
     */
    public function messages_contain_expected_sections(): void
    {
        $qm = get_qm();
        // Check that some expected sections exist from the language file
        $this->assertArrayHasKey('mb_language', $qm->m);
    }
}