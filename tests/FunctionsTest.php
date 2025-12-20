<?php

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
  public static function setUpBeforeClass(): void
  {
      require_once LIB_DIR . 'func.php';
  }

  /**
   * @test
   */
  public function h_escapes_html_special_characters(): void
  {
    $this->assertSame('&lt;script&gt;', h('<script>'));
    $this->assertSame('&amp;', h('&'));
    $this->assertSame('&quot;', h('"'));
    $this->assertSame('&#039;', h("'"));
  }

  /**
   * @test
   */
  public function h_returns_empty_string_for_empty_input(): void
  {
    $this->assertSame('', h(''));
  }

  /**
   * @test
   */
  public function h_decode_restores_escaped_html(): void
  {
    $original = '<script>alert("test")</script>';
    $escaped = h($original);
    $this->assertSame($original, h_decode($escaped));
  }

  /**
   * @test
   */
  public function is_image_returns_true_for_image_files(): void
  {
    $this->assertEquals(1, is_image('photo.jpg'));
    $this->assertEquals(1, is_image('photo.jpeg'));
    $this->assertEquals(1, is_image('photo.png'));
    $this->assertEquals(1, is_image('photo.gif'));
    $this->assertEquals(1, is_image('PHOTO.JPG'));
  }

  /**
   * @test
   */
  public function is_image_returns_false_for_non_image_files(): void
  {
    $this->assertEquals(0, is_image('document.pdf'));
    $this->assertEquals(0, is_image('script.js'));
    $this->assertEquals(0, is_image('style.css'));
  }

  /**
   * @test
   */
  public function get_mimetype_returns_correct_type_for_images(): void
  {
    $this->assertSame('image/jpeg', get_mimetype('photo.jpg'));
    $this->assertSame('image/jpeg', get_mimetype('photo.jpeg'));
    $this->assertSame('image/png', get_mimetype('photo.png'));
    $this->assertSame('image/gif', get_mimetype('photo.gif'));
  }

  /**
   * @test
   */
  public function get_mimetype_returns_octet_stream_for_unknown_types(): void
  {
    $this->assertSame('application/octet-stream', get_mimetype('file.unknown'));
  }

  /**
   * @test
   */
  public function wrap_script_tag_wraps_javascript(): void
  {
    $js = 'console.log("test");';
    $expected = "<script>\n{$js}\n</script>\n";
    $this->assertSame($expected, wrap_script_tag($js));
  }

  /**
   * @test
   */
  public function is_url_returns_true_for_valid_http_urls(): void
  {
    $this->assertEquals(1, is_url('http://example.com'));
    $this->assertEquals(1, is_url('https://example.com'));
    $this->assertEquals(1, is_url('http://example.com/path/to/page'));
    $this->assertEquals(1, is_url('https://example.com/path?query=value'));
    $this->assertEquals(1, is_url('http://example.com:8080/path'));
  }

  /**
   * @test
   */
  public function is_url_returns_true_for_ftp_and_news_urls(): void
  {
    $this->assertEquals(1, is_url('ftp://example.com'));
    $this->assertEquals(1, is_url('news://example.com'));
  }

  /**
   * @test
   */
  public function is_url_returns_false_for_invalid_urls(): void
  {
    $this->assertEquals(0, is_url(''));
    $this->assertEquals(0, is_url('example.com'));
    $this->assertEquals(0, is_url('not a url'));
    $this->assertEquals(0, is_url('mailto:test@example.com'));
    $this->assertEquals(0, is_url('javascript:alert(1)'));
  }

  /**
   * @test
   */
  public function is_url_with_only_http_rejects_ftp_and_news(): void
  {
    $this->assertEquals(1, is_url('http://example.com', true));
    $this->assertEquals(1, is_url('https://example.com', true));
    $this->assertEquals(0, is_url('ftp://example.com', true));
    $this->assertEquals(0, is_url('news://example.com', true));
  }

  /**
   * @test
   */
  public function is_url_with_omit_protocol_accepts_protocol_relative_urls(): void
  {
    $this->assertEquals(1, is_url('//example.com', false, true));
    $this->assertEquals(1, is_url('//example.com/path', false, true));
    $this->assertEquals(1, is_url('http://example.com', false, true));
  }

  /**
   * @test
   */
  public function wikiescape_escapes_dangerous_tags(): void
  {
    $this->assertSame("# html\n", wikiescape('#html'));
    $this->assertSame("# beforescript\n", wikiescape('#beforescript'));
    $this->assertSame("# style\n", wikiescape('#style'));
    $this->assertSame("# lastscript\n", wikiescape('#lastscript'));
  }

  /**
   * @test
   */
  public function wikiescape_is_case_insensitive(): void
  {
    $this->assertSame("# HTML\n", wikiescape('#HTML'));
    $this->assertSame("# Html\n", wikiescape('#Html'));
  }

  /**
   * @test
   */
  public function wikiescape_preserves_normal_text(): void
  {
    $this->assertSame("normal text\n", wikiescape('normal text'));
    $this->assertSame("#other\n", wikiescape('#other'));
  }

  /**
   * @test
   */
  public function wikiescape_handles_multiline_input(): void
  {
    $input = "#html\nsome content\n#style";
    $expected = "# html\nsome content\n# style\n";
    $this->assertSame($expected, wikiescape($input));
  }

  /**
   * @test
   */
  public function get_bs_style_returns_bootstrap_class_for_standard_colors(): void
  {
    $this->assertSame('btn btn-primary', get_bs_style('primary', 'btn'));
    $this->assertSame('btn btn-info', get_bs_style('info', 'btn'));
    $this->assertSame('btn btn-success', get_bs_style('success', 'btn'));
    $this->assertSame('btn btn-danger', get_bs_style('danger', 'btn'));
    $this->assertSame('btn btn-warning', get_bs_style('warning', 'btn'));
  }

  /**
   * @test
   */
  public function get_bs_style_maps_color_names_to_bootstrap_classes(): void
  {
    $this->assertSame('btn btn-primary', get_bs_style('blue', 'btn'));
    $this->assertSame('btn btn-info', get_bs_style('skyblue', 'btn'));
    $this->assertSame('btn btn-success', get_bs_style('green', 'btn'));
    $this->assertSame('btn btn-danger', get_bs_style('red', 'btn'));
    $this->assertSame('btn btn-warning', get_bs_style('orange', 'btn'));
  }

  /**
   * @test
   */
  public function get_bs_style_handles_different_types(): void
  {
    $this->assertSame('alert alert-success', get_bs_style('green', 'alert'));
    $this->assertSame('label label-success', get_bs_style('green', 'label'));
    $this->assertSame('badge badge-success', get_bs_style('green', 'badge'));
  }

  /**
   * @test
   */
  public function get_bs_style_is_case_insensitive(): void
  {
    $this->assertSame('btn btn-primary', get_bs_style('PRIMARY', 'btn'));
    $this->assertSame('btn btn-primary', get_bs_style('Blue', 'BTN'));
  }

  /**
   * @test
   */
  public function get_bs_style_normalizes_button_type(): void
  {
    $this->assertSame('btn btn-primary', get_bs_style('primary', 'button'));
  }
}
