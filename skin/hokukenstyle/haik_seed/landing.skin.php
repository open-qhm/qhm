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
    <!-- ◆ Header ◆ ========================================================= -->
    <header class="haik-eyecatch-top" role="banner">
      #{$main_visual}
    </header>
    <!-- ◆ Content ◆ ========================================================= -->
    <div class="container">
      <main class="haik-container" role="main">
        #{$body}
      </main>
      #{$summary}
    </div>
    <!-- ◆ Licence ◆ ========================================================== -->
    <div class="haik-licence" role="contentinfo">
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
  </body>
</html>
