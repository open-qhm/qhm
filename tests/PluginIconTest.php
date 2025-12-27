<?php

use PHPUnit\Framework\TestCase;

class PluginIconTest extends TestCase
{
  public static function setUpBeforeClass(): void
  {
    require_once LIB_DIR . 'func.php';
    require_once LIB_DIR . 'qhm_template.php';
    require_once PLUGIN_DIR . 'icon.inc.php';
  }

  protected function setUp(): void
  {
    // Reset QHM_Template singleton for each test
    $reflection = new ReflectionClass('QHM_Template');
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
  }

  /**
   * @test
   */
  public function inline_returns_glyphicon_by_default(): void
  {
    $result = plugin_icon_inline('star');
    $this->assertSame('<i class="glyphicon glyphicon-star" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_explicit_glyphicon(): void
  {
    $result = plugin_icon_inline('glyphicon', 'heart');
    $this->assertSame('<i class="glyphicon glyphicon-heart" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_font_awesome(): void
  {
    $result = plugin_icon_inline('fa', 'check');
    $this->assertSame('<i class="fa fa-check" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_font_awesome_alias(): void
  {
    $result = plugin_icon_inline('font-awesome', 'times');
    $this->assertSame('<i class="fa fa-times" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_font_awesome_5_solid(): void
  {
    $result = plugin_icon_inline('fas', 'user');
    $this->assertSame('<i class="fas fa-user" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_font_awesome_5_brands(): void
  {
    $result = plugin_icon_inline('fab', 'github');
    $this->assertSame('<i class="fab fa-github" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_font_awesome_5_regular(): void
  {
    $result = plugin_icon_inline('far', 'envelope');
    $this->assertSame('<i class="far fa-envelope" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_font_awesome_size_option(): void
  {
    $result = plugin_icon_inline('fa', '2x', 'home');
    $this->assertSame('<i class="fa fa-home fa-2x" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_font_awesome_lg_option(): void
  {
    $result = plugin_icon_inline('fa', 'lg', 'cog');
    $this->assertSame('<i class="fa fa-cog fa-lg" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_font_awesome_fw_option(): void
  {
    $result = plugin_icon_inline('fa', 'fw', 'bars');
    $this->assertSame('<i class="fa fa-bars fa-fw" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_bootstrap_icons(): void
  {
    $result = plugin_icon_inline('bi', 'alarm');
    $this->assertSame('<i class="bi bi-alarm" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_bootstrap_icons_alias(): void
  {
    $result = plugin_icon_inline('bootstrap-icons', 'calendar');
    $this->assertSame('<i class="bi bi-calendar" aria-hidden="true"></i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_google_material_symbols_outlined(): void
  {
    $result = plugin_icon_inline('gmso', 'home');
    $this->assertSame('<i class="material-symbols-outlined">home</i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_google_material_symbols_rounded(): void
  {
    $result = plugin_icon_inline('gmsr', 'search');
    $this->assertSame('<i class="material-symbols-rounded">search</i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_google_material_symbols_sharp(): void
  {
    $result = plugin_icon_inline('gmss', 'settings');
    $this->assertSame('<i class="material-symbols-sharp">settings</i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_google_material_icons_filled(): void
  {
    $result = plugin_icon_inline('gmif', 'favorite');
    $this->assertSame('<i class="material-icons">favorite</i>', $result);
  }

  /**
   * @test
   */
  public function inline_with_google_material_icons_outlined(): void
  {
    $result = plugin_icon_inline('gmio', 'delete');
    $this->assertSame('<i class="material-icons-outlined">delete</i>', $result);
  }

  /**
   * @test
   */
  public function inline_escapes_html_in_icon_name(): void
  {
    $result = plugin_icon_inline('<script>');
    $this->assertStringContainsString('&lt;script&gt;', $result);
    $this->assertStringNotContainsString('<script>', $result);
  }

  /**
   * @test
   */
  public function inline_returns_material_symbols_span_as_is(): void
  {
    $input = '<span class="material-symbols-outlined">home</span>';
    $result = plugin_icon_inline($input);
    $this->assertSame($input, $result);
  }

  /**
   * @test
   */
  public function inline_returns_material_icons_span_as_is(): void
  {
    $input = '<span class="material-icons">favorite</span>';
    $result = plugin_icon_inline($input);
    $this->assertSame($input, $result);
  }

  /**
   * @test
   */
  public function set_font_awesome_adds_script_to_template(): void
  {
    plugin_icon_set_font_awesome();
    $qt = get_qt();
    $beforescript = $qt->getv('beforescript');
    $this->assertStringContainsString('fontawesome', $beforescript);
  }

  /**
   * @test
   */
  public function set_bootstrap_icons_adds_link_to_template(): void
  {
    plugin_icon_set_bootstrap_icons();
    $qt = get_qt();
    $beforescript = $qt->getv('beforescript');
    $this->assertStringContainsString('bootstrap-icons', $beforescript);
  }
}