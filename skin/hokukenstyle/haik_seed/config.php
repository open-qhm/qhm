<?php
/**
*  haik テーマでカスタマイズ可能にするための定義
*/

return array (
	'bootstrap'      => true,
	'default_layout' => "default",
	'nomenu_layout'  => "nomenu",
	'wide_layout'    => "wide",
	'landing_layout' => "landing",
	'article_layout' => "article",
	'fullpage_layout' => "fullpage",

	'custom_options' => array(

		'spacer1' => array('type' => 'spacer'),

		'palette_color' => array(
			'type'        => 'theme_color',
			'value'       => 'cyan',
			'title'       => 'テーマカラー',
			'description' => 'テーマカラーを選びます',
		),		

		'divider1' => array('type' => 'divider'),

		'logo_text' =>	array(
			'type'       => 'text',
			'value'       => 'HAIK',
			'title'       => 'ロゴテキスト',
			'description' => 'ロゴ部分に使うテキスト（任意）です',
		),

		'logo_type' => array(
			'type'        => 'if',
			'value'       => FALSE,

			'title'       => 'ロゴ画像を使う',
			'description' => 'ロゴ画像を使用する設定になります'
		),
		'logo_img' => array(
			'type'        => 'img',
			'value'       => '',
			'title'       => 'ロゴ画像',
			'description' => 'ロゴ部分に使う画像（任意）です',
			'follow'      => 'logo_type',
		),

		'divider2' => array('type' => 'divider'),

		'default_eyecatch' => array(
			'type'        => 'if',
			'value'       => TRUE,
			'title'       => 'アイキャッチを使う',
			'description' => 'ページタイトルを入れたアイキャッチを自動で表示する場合、有効にしてください',
			),
		'eyecatch_title_type' => array(
			'type'        => 'hidden',
			'value'       => 'site',
			'title'       => 'アイキャッチのタイトル',
			'description' => 'アイキャッチのタイトルにページ名(page)を使うかサイトタイトル(site)を使うか設定します',
			),
		'eyecatch_color' => array(
			'type'        => 'color',
			'value'       => '#494949',
			'title'       => 'アイキャッチ文字色',
			'description' => 'デフォルトアイキャッチの文字色を指定できます',
		),
		'enable_eyecatch_bgimage' => array(
			'type'        => 'if',
			'value'       => FALSE,
			'title'       => 'アイキャッチ画像を使う',
			'description' => 'ページタイトルを入れたアイキャッチを自動で表示する場合、有効にしてください',
		),
		'eyecatch_bgimage' => array(
			'type'        => 'select_img',
			'value'       => '',
			'title'       => 'アイキャッチ画像選択',
			'description' => 'デフォルトアイキャッチの背景画像に使用する画像を指定できます',
			'follow'      => 'enable_eyecatch_bgimage',
		),

		'divider3' => array('type' => 'divider'),

		'blog_list_type' => array(
			'type'        => 'select',
			'value'       => 'grid',
			'title'       => 'ブログリストの表示',
			'options'     => array(
				'grid' => 'グリッドデザイン',
				'list' => 'リストデザイン',
			),
			'description' => 'ブログリストの表示を切り替えます',
		),

		'divider5' => array('type' => 'divider'),

		'nav_fixed' => array(
			'type'        => 'if',
			'value'       => FALSE,
			'title'       => 'ナビを固定する',
			'description' => 'ナビを固定する設定になります'
		),
		
		'body_font'	=> array(
			'type'        => 'font',
			'value'       => '"游ゴシック体", "Yu Gothic", YuGothic,"ヒラギノ角ゴ ProN","Hiragino Kaku Gothic ProN","メイリオ","Meiryo",sans-serif',
			'options'     => array(
        '"游ゴシック体", "Yu Gothic", YuGothic,"ヒラギノ角ゴ ProN","Hiragino Kaku Gothic ProN","メイリオ","Meiryo",sans-serif',
				'"ヒラギノ角ゴ ProN","Hiragino Kaku Gothic ProN","メイリオ","Meiryo","MS ゴシック","MS Gothic","MS Pゴシック","MS PGothic",sans-serif',
				'"游明朝", YuMincho,"ヒラギノ明朝 ProN W3","Hiragino Mincho ProN","HG明朝E","ＭＳ Ｐ明朝","MS PMincho","MS 明朝",serif',
				'"ヒラギノ丸ゴ ProN","Hiragino Maru Gothic ProN","HG丸ｺﾞｼｯｸM-PRO","Verdana","Osaka",sans-serif',
			),
			'title'       => 'フォント',
			'description' => 'ロゴ部分に使う画像（任意）です',
		),

		'spacer2' => array('type' => 'spacer'),
	)
);
