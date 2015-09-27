  <head>
      #{$meta_content_type}
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>#{$this_page_title}</title>
      <meta name="keywords" content="#{$keywords}" />
      <meta name="description" content="#{$description}" />
      <link rel="alternate" type="application/rss+xml" title="RSS" href="#{$rss_link}" />
      <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
      #{$bootstrap}
      #{$default_css}
      #{$jquery_script}#{$jquery_cookie_script}
      #{$custom_meta}#{$noindex}#{$external_link}#{$clickpad_js}
      #{$head_tag}
      #{$beforescript}
  </head>
  <body class="#{$palette_color_class}">
      #{$toolkit_upper}
      <div class="haik-headcopy">
        <div class="container">
          #{$head_copy_tag}
        </div>
      </div>
      <!-- ◆ Navbar ◆ ======================================================= -->
      <nav id="navigator" class="navbar-default haik-nav" role="navigation" <?php if (hv('nav_fixed', true)) : ?>
      data-spy="affix" data-offset-top="20"<?php endif; ?>>
        <div class="container">
          <div class="navbar-header">
            <a class="navbar-brand" href="#{$qhm_dir}">
              <?php if (hv('logo_type', true)) : ?>
                <img src="<?php echo hv('logo_img'); ?>">
              <?php else: ?>
                <?php echo hv('logo_text'); ?>
              <?php endif ; ?>
            </a>
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar-menu">MENU</span>
            </button>
          </div>
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            #{$site_navigator}
          </div>
        </div>
      </nav>
      <!-- ◆ Eyecatch ◆ ========================================================= -->
      <div class="haik-eyecatch-top haik-fullpage-eyecatch">
        #{$main_visual}
      </div>
      <!-- ◆ Content ◆ ========================================================= -->
      <div class="container-fluid">
        <main class="haik-container haik-fullpage-content" role="main">
          #{$body}
        </main>
        #{$summary}
      </div>
      <?php if ( ! $site_navigator2_is_empty):?>
      <!-- ◆ Footer ◆ ========================================================== -->
      <footer class="haik-footer haik-fullpage-footer" role="contentinfo">
        <div class="container haik-footer-border">
          #{$site_navigator2}
        </div>
      </footer>
      <?php endif ?>
      <!-- ◆ Licence ◆ ========================================================== -->
      <div class="container-fluid haik-licence" role="contentinfo">
        <div class="container">
          <div class="text-center haik-copyright">
            <p> Copyright © #{$today_year} <a href="#{$modifierlink}">#{$modifier}</a> All Rights Reserved.
            #{$owneraddr} #{$ownertel}</p>
          </div>
          <?php if ($licence_tag !== ''): ?>
            <div>
              <p>powered by <strong>Quick Homepage Maker</strong> #{$version} based on PukiWiki 1.4.7 License is GPL. #{$auth_link}</p>
            </div>
          <?php endif ?>
        </div>
      </div>
      <!-- ■　アクセスタグ■ ============================================== -->
      #{$accesstag_tag}
      #{$lastscript}
      <script src="#{$style_path}skin.js"></script>
  </body>
</html>
