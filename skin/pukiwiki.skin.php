<head>
 #{$meta_content_type}
 <title>#{$this_page_title}</title>
 <meta name="keywords" content="#{$keywords}" />
 <meta name="description" content="#{$description}" />
 <meta name="viewport" content="width=780" />
 <link rel="alternate" type="application/rss+xml" title="RSS" href="#{$rss_link}" />
 #{$default_css}
 <script type="text/javascript" src="js/jquery.js" ></script>
 <script type="text/javascript" src="js/jquery.tablesorter.min.js" ></script>
 #{$jquery_cookie_script}
 #{$custom_meta}#{$noindex}#{$external_link}#{$clickpad_js}
 #{$head_tag}
 #{$beforescript}
</head>
<body>
#{$toolkit_upper}
<div id="wrapper"><!-- ■BEGIN id:wrapper -->
<!-- ◆ Content ◆ ========================================================= -->
<div id="main"><!-- ■BEGIN id:main -->
<div id="wrap_content"><!-- ■BEGIN id:wrap_content -->
<div id="content"><!-- ■BEGIN id:content -->
<h2 class="title">#{$this_right_title}</h2>
<div id="body"><!-- ■BEGIN id:body -->
#{$qp_here_start}
#{$body}
#{$qp_here_end}
</div><!-- □END id:body -->
#{$summary}
</div><!-- □END id:content -->
</div><!-- □ END id:wrap_content -->
</div><!-- □END id:main -->
<!-- ◆ Footer ◆ ========================================================== -->
<div id="footer"><!-- ■BEGIN id:footer -->
<div id="copyright"><!-- ■BEGIN id:copyright -->
<p>powered by Quick Homepage Maker </p>
</div><!-- □END id:copyright -->
</div><!-- □END id:footer -->
<!-- ◆ END ◆ ============================================================= -->
</div><!-- □END id:wrapper -->
</body>
</html>
