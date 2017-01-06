<?php
/**
 *   QBlog Setting Plugin
 *   -------------------------------------------
 *   ./plugin/qblog.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 12/07/31
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */
function plugin_qblog_action()
{
	global $vars, $script, $style_name;
	global $qblog_defaultpage, $qblog_default_cat, $qblog_date_format, $qblog_page_prefix;
	global $qblog_social_widget, $qblog_social_html, $qblog_social_wiki, $qblog_title;
	global $qblog_enable_comment, $qblog_close, $qblog_enable_ping, $qblog_ping;
	global $qblog_comment_notice, $admin_email;

	$qt = get_qt();
	$style_name = '..';
	$vars['disable_toolmenu'] = TRUE;
	$qt->setv('no_menus', TRUE);//メニューやナビ等をconvertしない

	$include_bs = '
<link rel="stylesheet" href="skin/bootstrap/css/bootstrap.min.css" />
<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';
	$qt->appendv_once('include_bootstrap_pub', 'beforescript', $include_bs);

	$beforescript = '
<link rel="stylesheet" href="'. PLUGIN_DIR .'qblog/qblog.css" />
<script type="text/javascript" src="js/jQuery.ajaxQueue.min.js"></script>';
	$qt->appendv('beforescript', $beforescript);


	// 管理者でない場合はブログトップへ移動する
	// 記事の追加のみ、編集権限を後でチェックする
	if ($vars['mode'] !== 'addpost' && ! ss_admin_check())
	{
    	$url = $script.'?'.$qblog_defaultpage;
		header('Location: '.$url);
		exit;
	}

	// モード毎の処理
	if (isset($vars['mode']))
	{
		if ($vars['mode'] == 'delete')
		{
			plugin_qblog_delete_category();
		}
		else if ($vars['mode'] === 'rebuild')
		{
			plugin_qblog_rebuild_posts();
		}
		else if ($vars['mode'] === 'social_widget')
		{
			plugin_qblog_save_social_widget();
		}
		else if ($vars['mode'] === 'move_confirm')
		{
			$ret = plugin_qblog_move_from_ameba_confirm();
			if ($ret)
			{
				return $ret;
			}
		}
		else if ($vars['mode'] == 'move')
		{
			plugin_qblog_move_from_ameba();

		}
		else if ($vars['mode'] == 'move_from_eblog_confirm')
		{
			$ret = plugin_qblog_move_from_eblog_confirm();
			if ($ret !== FALSE)
			{
				return $ret;
			}
		}
		else if ($vars['mode'] == 'move_from_eblog')
		{
			plugin_qblog_move_from_eblog();
		}
		else if ($vars['mode'] == 'start')
		{
			plugin_qblog_start();
		}
		// !新しい記事の作成ページへ飛ばす
		else if ($vars['mode'] == 'addpost')
		{
			$newpage = qblog_get_newpage();
			if (check_editable($newpage, TRUE, FALSE))
			{
				$newpage_url = $script . '?cmd=edit&page=' . $newpage;
				redirect($newpage_url);
			}
			else
			{
		    	$url = $script.'?'.$qblog_defaultpage;
				header('Location: '.$url);
				exit;
			}
		}
		else if ($vars['mode'] == 'edit_title')
		{
			plugin_qblog_edit_title();
		}
		else if ($vars['mode'] == 'enable_comment')
		{
			plugin_qblog_enable_comment();
		}
		else if ($vars['mode'] == 'close')
		{
			plugin_qblog_close();
		}
		else if ($vars['mode'] == 'rename_category')
		{
			plugin_qblog_rename_category();
		}
		else if ($vars['mode'] == 'update_ping')
		{
			plugin_qblog_update_ping();
		}
		else if ($vars['mode'] == 'comment_notice')
		{
			plugin_qblog_update_comment_notice();
		}
	}

	// ! お知らせをセットする
	$qblog_info = '';
	if (isset($vars['phase']))
	{
		switch ($vars['phase'])
		{
			case 'set_title':
				$qblog_info = '
<div class="qblog_info alert alert-success">
	<button class="close" data-dismiss="alert">×</button>
	<p>
		<b>ブログに名前を付けましょう！</b><br />
		<a href="#qblog_title" data-tab="move" id="go_qblog_title" class="btn">ブログタイトル設定</a>へ進んでください。
	</p>
</div>
';
				break;
			case 'edit_title':
				$qblog_info = '
<div class="qblog_info alert alert-success">
	<button class="close" data-dismiss="alert">×</button>
	<p>
		ブログタイトルを変更しました。
	</p>';
				if ( ! glob(DATA_DIR . encode($qblog_page_prefix) . '*'))
				{
					$vars['hash'] = 'misc';
					$qblog_info .= '
	<p>
		<b>アメブロをお持ちの方：</b><br />
		<a href="#qblog_move_ameba" data-tab="move" class="btn">アメブロからの引越し</a>に進んでください。
	</p>
	<p>
		<b>QHMプロの簡易ブログをご利用の方：</b><br />
		<a href="#qblog_move_eblog" data-tab="move" class="btn">簡易ブログからの引越し</a>に進んでください。
	</p>
	<p>
		<b>ブログを利用されていない方：</b><br />
		<a href="'.$script.'?cmd=qblog&mode=addpost" class="btn">さっそく、新しい記事を投稿しましょう！</a>
	</p>';
				}
				$qblog_info .= '
</div>
';
				break;
			case 'social_widget':
				$qblog_info = '
<div class="qblog_info alert alert-success">
<button class="close" data-dismiss="alert">×</button>
	ソーシャルウィジェット設定を保存しました。
</div>
';
				break;
			case 'rebuild':
				$qblog_info = '
<div class="qblog_info alert alert-success">
<button class="close" data-dismiss="alert">×</button>
	ブログの修復が完了しました。
</div>
';
				break;
			case 'delete_category':
				$qblog_info = '
<div class="qblog_info alert alert-success">
<button class="close" data-dismiss="alert">×</button>
	カテゴリー：'. h($vars['category']) .' を削除しました。
</div>
';
				break;
			case 'enable_comment':
				$commentmsg = $qblog_enable_comment ? '表示する' : '表示しない';
				$qblog_info = '
<div class="qblog_info alert alert-success">
<button class="close" data-dismiss="alert">×</button>
	コメントの表示を「'.$commentmsg.'」にしました。
</div>
';
				break;

			case 'close':
				$msg = $qblog_close ? '閉鎖' : '公開';
				$qblog_info = '
<div class="qblog_info alert alert-success">
<button class="close" data-dismiss="alert">×</button>
	ブログを「'.$msg.'」しました。
</div>
';
				break;

			case 'rename_category':
				$qblog_info = '
<div class="qblog_info alert alert-success">
<button class="close" data-dismiss="alert">×</button>
	カテゴリー名を変更しました。
</div>
';
				break;

			case 'ping':
				$msg = $qblog_enable_ping ? '有効' : '無効';
				$qblog_info = '
<div class="qblog_info alert alert-success">
<button class="close" data-dismiss="alert">×</button>
	Ping送信を「'.$msg.'」にしました。
</div>
';
				break;

			case 'comment_notice':
				$msg = $qblog_comment_notice ? '通知する' : '通知しない';
				$qblog_info = '
<div class="qblog_info alert alert-success">
<button class="close" data-dismiss="alert">×</button>
	コメントを「'.$msg.'」にしました。
</div>
';
				break;
			default:
		}
	}
	else
	{
		if ($qblog_close)
		{
			$qblog_info = '
<div class="qblog_info alert alert-danger">
<button class="close" data-dismiss="alert">×</button>
	ブログは<strong>閉鎖中</strong>です。<br />
	公開する場合、「その他」タブを開いて、ブログの閉鎖設定を変更してください。
</div>
';
		}
	}


	// エラーがあればエラーをセットする
	$qblog_error = '';
	if (isset($vars['qblog_error']) && $vars['qblog_error'] != '')
	{
		$qblog_error = $vars['qblog_error'];
	}

	//開くタブをセットする
	$hash = isset($vars['hash']) ? $vars['hash'] : '';

	// ブログの初期設定
	// ! ブログの記事が存在するかチェック
	$files = glob(DATA_DIR . encode($qblog_page_prefix) . '*');

	// ! アメブロからの引越を表示するかしないか
	$move_amebro = (FALSE && count($files) == 0);

	// !カテゴリ一覧の取得
	$categories_file = CACHEQBLOG_DIR. 'qblog_categories.dat';
	$categories = array();
	if (file_exists($categories_file))
	{
		$categorydata = explode("\n", file_get_contents($categories_file));
		foreach ($categorydata as $data)
		{
			if (strlen(trim($data)) > 0)
			{
				list($name, $num) = explode("\t", trim($data));
				$categories[$name] = array('name'=>$name, 'num'=>$num);
			}
		}
	}

	// !未承認コメントの一覧
	$pending_comments = unserialize(file_get_contents(CACHEQBLOG_DIR . 'qblog_pending_comments.dat'));
	$pending_comments = ($pending_comments === FALSE) ? array() : $pending_comments;
	foreach ($pending_comments as $i => $comment)
	{
		$pending_comments[$i]['post_title'] = mb_strimwidth(get_page_title($comment['page']), 0, 16, '...');
		$pending_comments[$i]['title'] = mb_strimwidth($comment['title'], 0, 16, '...');
		$pending_comments[$i]['name'] = mb_strimwidth($comment['name'], 0, 12, '...');
	}

	// !RSSのURL
	$rss_url = '';
	if (exist_plugin('rss'))
	{
		$rss_url = $script . '?cmd=rss&qblog_rss=1';
	}

	// !Ping
	if (trim($qblog_ping) === '')
	{
		$qblog_ping = plugin_qblog_get_default_ping();
	}

	//! テンプレートを読み込む
	$html = '';
	ob_start();
	include(PLUGIN_DIR . 'qblog/qblog_index_template.html');
	$html .= ob_get_clean();


	return array('msg'=>'ブログ設定', 'body'=>$html);
}

function plugin_qblog_rebuild_posts()
{
	global $vars, $script;
	global $qblog_page_prefix, $qblog_page_re;

	$vars['hash'] = 'misc';

	//ポストデータが壊れているポスト用に、
	//全カテゴリーキャッシュを網羅して、
	//どのページがどのカテゴリーか調べる
	$files = glob(CACHEQBLOG_DIR . '*.qbc.dat');

	//ページとカテゴリのペア配列
	$page_cat_list = array();
	foreach ($files as $file)
	{
		$category = decode(basename($file, '.qbc.dat'));

		//ページ名をキー、カテゴリ名を値とした連想配列を作る
		$tmp_pages = array_flip(explode("\n", file_get_contents($file)));
		$tmp_pages = array_combine(array_keys($tmp_pages), array_pad(array(), count($tmp_pages), $category));

		$page_cat_list = array_merge($page_cat_list, $tmp_pages);
	}

	//ブログポスト（QBlog-）のwikiファイルを全チェックし、
	//ポストデータがないものについてはカテゴリーのみ修復する

	$files = glob(DATA_DIR . encode($qblog_page_prefix) . '*');
	foreach ($files as $file)
	{
		$pagename = decode(basename($file, '.txt'));
		if (preg_match($qblog_page_re, $pagename))
		{
			$data = get_qblog_post_data($pagename);

			if ($data === FALSE)
			{
				$option = array(
					'category' => $page_cat_list[$pagename],
					'image' => ''
				);
			}
			else
			{
				$option = array(
					'category' => $data['category'],
					'image' => $data['image']
				);
			}
			qblog_update_post(TRUE, $pagename, $option);
		}
	}

	//強制アップデート
	qblog_update(TRUE);

	redirect($script . '?cmd=qblog&phase=rebuild#misc');
}

/**
 * アメーバブログ移行の確認画面
 * 移行記事リストを表示する。
 */
function plugin_qblog_move_from_ameba_confirm()
{
	global $vars, $script;
	global $qblog_defaultpage;

	$vars['hash'] = 'misc';

	require_once(PLUGIN_DIR.'qblog/phpQuery-onefile.php');

	$qt = get_qt();

	$ameba_id = $vars['ameba_id'];
	$ameba_password = $vars['ameba_password'];

	// アメブロへ認証する
	$atomapi_url = "http://atomblog.ameba.jp/servlet/_atom/blog";
	$created = date('Y-m-d\TH:i:s\Z');
	$nonce = sha1(md5(time()));
	$pass_digest = base64_encode(pack('H*', sha1($nonce.$created.strtolower(md5($ameba_password)))));
	$wsse =
	    'UsernameToken Username="'.$ameba_id.'", '.
	    'PasswordDigest="'.$pass_digest.'", '.
	    'Nonce="'.base64_encode($nonce).'", '.
	    'Created="'.$created.'"';
	$headers = "X-WSSE: {$wsse}\r\nContent-Type: application/x.atom+xml\r\n";
	$res = http_request($atomapi_url, 'GET', $headers);

	if ($res['rc'] !== 200)
	{
		// アクセスエラー
		$vars['qblog_error'] = 'アメブロにログインできませんでした。<br />アメーバIDとパスワードに間違いがないかご確認ください。';
		return FALSE;
	}
	$_SESSION['amebaid'] = $ameba_id;

	//全公開記事のリストを取得する
	$blogdata = array();
	$amebro_entries_file = CACHEQBLOG_DIR.$ameba_id.'_ameblo_entries.dat';
	if (! file_exists($amebro_entries_file))
	{
		$data = file_get_contents('http://ameblo.jp/'.$ameba_id.'/entrylist-1.html');

		// デザインチェック
		if (strpos(substr($data, 0, 1500), ',skin_code:wu_pf_gray,') === FALSE)
		{
			$vars['qblog_error'] = 'アメブロのデザインを<b>ベーシックグレー</b>に設定してください。';
			return FALSE;
		}

		$pq = phpQuery::newDocument($data);

		$checkdate = $pq->find('ul.contentsList li:first div.contentTime')->text();
		if ( ! preg_match('/\d{4}-\d{2}-\d{2}/',$checkdate))
		{
			$vars['qblog_error'] = 'アメブロの「ブログ管理 - 基本設定」で日付の表示方法を<b>XXXX-XX-XX</b>に設定してください。';
			return FALSE;
		}

		$pq_li = $pq->find('ul.contentsList li');
		foreach($pq_li as $list)
		{
			$url = trim(pq($list)->find('div.contentTitleArea a')->attr('href'));
			$blogdata[basename($url, '.html')] = array(
				'title'=> trim(pq($list)->find('div.contentTitleArea a')->text()),
				'date'=> trim(pq($list)->find('div.contentTime')->text()),
				'url'=> $url,
				'complete'=> 0,
				'amember' => 0
			);
		}
		while ($pq->find('a.pagingNext')->length())
		{
			// 次のページを読み込む
			$data = file_get_contents($pq->find('a.pagingNext')->attr('href'));
			$pq = phpQuery::newDocument($data);

			$pq_li = $pq->find('ul.contentsList li');
			foreach($pq_li as $list)
			{
				$url = trim(pq($list)->find('div.contentTitleArea a')->attr('href'));
				$blogdata[basename($url, '.html')] = array(
					'title'=> trim(pq($list)->find('div.contentTitleArea a')->text()),
					'date'=> trim(pq($list)->find('div.contentTime')->text()),
					'url'=> $url,
					'complete'=> 0,
					'amember' => 0
				);
			}
		}

		file_put_contents($amebro_entries_file, serialize($blogdata), LOCK_EX);
	}
	else
	{
		$blogdata = unserialize(file_get_contents($amebro_entries_file));
	}

	$blogdata = array_reverse($blogdata, TRUE);

	foreach ($blogdata as $key => $blog)
	{
		if ($blog['complete'])
		{
			unset($blogdata[$key]);
		}
	}

	if (count($blogdata) == 0)
	{
		$vars['qblog_error'] = 'ご指定されたアメブロは既に引越し済みです。';
		return FALSE;
	}

	//! テンプレートを読み込む
	$html = '';
	ob_start();
	include(PLUGIN_DIR . 'qblog/qblog_ameblo_template.html');
	$html .= ob_get_clean();

	return array('msg'=>'アメブロからの引越し', 'body'=>$html);
}

function plugin_qblog_move_from_ameba()
{
	global $vars, $script;
	global $qblog_page_format;

	$ameba_id = isset($_SESSION['amebaid']) ? $_SESSION['amebaid'] : FALSE;
	if ( ! $ameba_id)
	{
		header("Content-Type: application/json; charset=UTF-8");
		echo '{success:0}';
		exit;
	}

	require_once(PLUGIN_DIR.'qblog/phpQuery-onefile.php');
	$move_id = $vars['move_id'];

	// アメブロのブログリストを取得
	$amebro_entries_file = CACHEQBLOG_DIR.$ameba_id.'_ameblo_entries.dat';
	$blogdata = unserialize(file_get_contents($amebro_entries_file));

	if ($blogdata[$move_id]['complete'] === 1)
	{
		header("Content-Type: application/json; charset=UTF-8");
		echo '{success:0}';
		exit;
	}

	// ブログタイトル
	$title = $blogdata[$move_id]['title'];
	$eye_catch = '';

	// ブログにアクセスして保存するデータを取得
	// 文章、カテゴリーの取得
	$data = file_get_contents($blogdata[$move_id]['url']);
	$pq = phpQuery::newDocument($data);
	$category = $pq->find('span.articleTheme a')->text();

	$postdata = $pq->find('div.articleText')->html();

	//HTMLタグを削除して、改行と画像、リンクだけにする
	$postdata = preg_replace('/<br\s*\/?>/i', "\n", $postdata);
	$postdata = str_replace('((', '&#40;(', $postdata);
	$postdata = strip_tags($postdata, '<a><img>');

	//リンクタグを取得し、画像へのリンクであれば、&show に、
	//通常のリンクであれば、QHM書式へ書き直す
	$patterns = array();
	$replaces = array();
	if (preg_match_all('/<a .*?href="(.*?)".*?>(.*?)<\/a>/i', $postdata, $mts))
	{
		$ameblo_image_prefix = 'http://ameblo.jp/'. $ameba_id .'/'. str_replace('entry', 'image', $move_id) . '-';

		foreach ($mts[1] as $i => $url)
		{
			$url = str_replace('&amp;', '&', $url);

			//画像を &show(); に置換
			if (strpos(trim($url), $ameblo_image_prefix) === 0)
			{
				$pq2 = phpQuery::newDocument(file_get_contents($url));//#imgLink
				$pq_img = $pq2->find("#centerImg");
				$imgurl = $pq_img->attr('src');
				$alt_text = $pq_img->attr('alt');

				//インラインプラグイン内で使用不可な文字を変換
				$alt_text = str_replace(array(',', '(', ')', ';'), array('，', '（', '）', '；'), $alt_text);

				//SWFUに保存
				$imgname = basename($imgurl);
				file_put_contents(SWFU_IMAGE_DIR . $imgname, file_get_contents($imgurl));

				if ($eye_catch === '')
				{
					$eye_catch = $imgname;
				}

				//画像のサイズを取得
				$size = '';
				if (preg_match('/src="(.*?)"/i', $mts[2][$i], $matches))
				{
					if (preg_match('/width="(.*?)"/i', $mts[2][$i], $matches))
					{
						$width = $matches[1];
						if (preg_match('/height="(.*?)"/i', $mts[2][$i], $matches))
						{
							$height = $matches[1];
							$size = "{$width}x{$height}";
						}
					}
				}
				$replaces[] = '&show('. $imgname .',colorbox=qblog,'. $size .','. $alt_text.');';
			}
			//リンクを [[]] に置換
			else
			{
				//使っちゃダメな文字を消す無慈悲に
				$text = str_replace(array("\n", "\r", '[', ']', '>'), '', trim($mts[2][$i]));
				$replaces[] = '[['. $text .'>'. $url .']]';
			}
			$patterns[] = $mts[0][$i];
		}

	}

	$postdata = str_replace($patterns, $replaces, $postdata);


	//リンクに囲まれてない <img />（おそらく絵文字）を取得し、&show に変換
	$patterns = array();
	$replaces = array();
	if (preg_match_all('/<img ([^>]+)>/i', $postdata, $mts))
	{
		foreach ($mts[1] as $i => $imgattrs)
		{
			preg_match('/alt="(.*?)"/i', $imgattrs, $mts2);
			$imgalt = $mts2[1];
			preg_match('/src="(.*?)"/i', $imgattrs, $mts2);
			$imgsrc = $mts2[1];

			//サムネイルに利用されないよう、nolinkオプションを付ける
			$replaces[] = '&show('.$imgsrc.',nolink,'.$imgalt.');';
			$patterns[] = $mts[0][$i];
		}
	}
	$postdata = str_replace($patterns, $replaces, $postdata);

	//行頭の半角スペースを除去
	$postdata = preg_replace('/^ +/m', '', $postdata);

	//postdata が空の場合、アメンバー限定記事と見なす
	if (trim($postdata) === '')
	{
		$blogdata[$move_id]['amember'] = 1;;
	}

	if ( ! $blogdata[$move_id]['amember'])
	{
		$postdata = 'TITLE:'. $title . "\n" . $postdata;

		// ページの書込み
		$newpage = qblog_get_newpage($blogdata[$move_id]['date']);
		page_write($newpage, $postdata);
		$blogdata[$move_id]['pagename'] = $newpage;

		// ブログのキャッシュファイルの作成
		$options = array(
			'category' => $category,
			'image' => $eye_catch
		);
		qblog_update_post(TRUE, $newpage, $options);

		$comments = array();
		$commentdata = $pq->find('div.blogComment');
		foreach ($commentdata as $comment)
		{
			$comment = pq($comment);
			list($id, $title) = explode('.', $comment->find('div.commentHeader')->text(), 2);
			$adminflg = ($comment->hasClass('ownerComment')) ? 1 : 0;
			$comments[trim($id)] = array(
				'id'       => trim($id),
				'title'    => trim($title),
				'msg'      => preg_replace('/<br(?: \/)?>/', "\n", $comment->find('div.commentBody')->html()),
				'name'     => $comment->find('.commentAuthor')->text(),
				'datetime' => $comment->find('span.commentTime')->text(),
				'accepted' => 1,
				'show'     => 1,
				'admin'    => $adminflg,
			);
		}
		while ($pq->find('a.textPagingNext')->length())
		{
			$url = $pq->find('a.textPagingNext')->attr('href');
			$pq = phpQuery::newDocument(file_get_contents($url));
			$commentdata = $pq->find('div.blogComment');
			foreach ($commentdata as $comment)
			{
				$comment = pq($comment);
				list($id, $title) = explode('.', $comment->find('div.commentHeader')->text(), 2);
				$adminflg = ($comment->hasClass('ownerComment')) ? 1 : 0;
				$comments[trim($id)] = array(
					'id'       => trim($id),
					'title'    => trim($title),
					'msg'      => preg_replace('/<br(?: \/)?>/', "\n", $comment->find('div.commentBody')->html()),
					'name'     => $comment->find('.commentAuthor')->text(),
					'datetime' => $comment->find('span.commentTime')->text(),
					'accepted' => 1,
					'show'     => 1,
					'admin'    => $adminflg,
				);
			}
		}

		// 昇順でリストを作成
		ksort($comments);

		// コメントキャッシュファイルの作成
		$commentcachefile = CACHEQBLOG_DIR . encode($newpage).'.qbcm.dat';
		file_put_contents($commentcachefile, serialize($comments), LOCK_EX);
	}

	// アメブロリストファイルに完了フラグをたてる
	$blogdata[$move_id]['complete'] = 1;
	file_put_contents($amebro_entries_file, serialize($blogdata), LOCK_EX);

	// 成功
	$blogdata[$move_id]['success'] = 1;
	header("Content-Type: application/json; charset=UTF-8");
	echo json_encode($blogdata[$move_id]);
	exit;
}

function plugin_qblog_delete_category()
{
	global $vars;
	global $qblog_default_cat;

	$vars['hash'] = 'category';

	$category = $vars['category'];
	$category_file = CACHEQBLOG_DIR. encode($category) . '.qbc.dat';

	// *.qbp.dat のcategory をデフォルトカテゴリーに変更する
	$pages = explode("\n", file_get_contents($category_file));
	foreach ($pages as $page)
	{
		$data = get_qblog_post_data($page);
		$data['category'] = $qblog_default_cat;
		qblog_save_post_data($page, $data);
	}

	// カテゴリのキャッシュファイルを削除
	if (file_exists($category_file))
	{
		unlink($category_file);
	}

	// カテゴリファイルのアップデート
	qblog_update_categories(TRUE);

	$vars['phase'] = 'delete_category';
}

function plugin_qblog_save_social_widget()
{
	global $vars;
	global $qblog_social_widget, $qblog_social_html, $qblog_social_wiki;
	$widget = $vars['qblog_social_widget'];
	$insert_html = $vars['qblog_social_html'];
	$insert_wiki = $vars['qblog_social_wiki'];

	$widget = in_array($widget, array('default', 'html', 'wiki', 'none')) ? $widget : 'default';

	if (exist_plugin("qhmsetting"))
	{
		$params = array();
		$qblog_social_widget = $params['qblog_social_widget'] = $widget;
		$qblog_social_html   = $params['qblog_social_html'] = $insert_html;
		$qblog_social_wiki   = $params['qblog_social_wiki'] = $insert_wiki;

		$_SESSION['qhmsetting'] = $params;
		plugin_qhmsetting_update_ini();

		$vars['phase'] = 'social_widget';
	}
	else
	{
		$vars['qblog_error'] = 'qhmsetting プラグインが見つかりません。';
	}
}

function plugin_qblog_move_from_eblog_confirm()
{
	global $vars, $script;
	global $qblog_defaultpage;

	$vars['hash'] = 'misc';

	$qt = get_qt();
	$qm = get_qm();

	$eblog_page = $vars['eblog_page'];
	$pages = glob(DATA_DIR.encode($eblog_page.'/').'*');

	// 簡易ブログページのチェック
	if (count($pages) == 0)
	{
		//error やでー
		$vars['qblog_error'] = 'ページが見つかりません。正しい簡易ブログ設置ページ名をご入力ください。';
		return FALSE;
	}

	// 簡易ブログ移行ファイル
	$eblog_entries_file = CACHEQBLOG_DIR . encode($eblog_page).'_eblog_entries.dat';
	if (file_exists($eblog_entries_file))
	{
		// ファイルがあれば読み込む
		$eblog_datas = unserialize(file_get_contents($eblog_entries_file));
	}
	else
	{
		// ファイルなければ作成
		$eblog_datas = array();
		foreach ($pages as $data)
		{
			$pagename = decode(basename($data, '.txt'));
			list($tmp, $date) = explode('/', $pagename, 2);

			// ファイル名の日付が正しくない場合は、スルー
			if ( ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
			{
				continue;
			}

			$eblog_datas[$pagename] = array(
				'title'    => $pagename,
				'date'     => $date,
				'url'      => $script.'?'.rawurldecode($pagename),
				'complete' => 0,
			);
		}
		file_put_contents($eblog_entries_file, serialize($eblog_datas), LOCK_EX);
	}

	ksort($eblog_datas);

	foreach ($eblog_datas as $pagename => $data)
	{
		if ($data['complete'])
		{
			unset($eblog_datas[$pagename]);
		}
	}

	if (count($eblog_datas) == 0)
	{
		$vars['qblog_error'] = 'ご指定された簡易ブログは存在しないか、既に引越し済みです。';
		return FALSE;
	}

	//! テンプレートを読み込む
	$html = '';
	ob_start();
	include(PLUGIN_DIR . 'qblog/qblog_eblog_template.html');
	$html .= ob_get_clean();


	return array('msg'=>'簡易ブログからの引越し', 'body'=>$html);
}

function plugin_qblog_move_from_eblog()
{
	global $vars, $script;
	global $ignore_plugin, $strip_plugin;
	global $qblog_default_cat;

	$eblog_page = $vars['eblog_page'];
	$pagename = $vars['pagename'];

	// 簡易ブログページの読み込み
	$eblog_entries_file = CACHEQBLOG_DIR . encode($eblog_page).'_eblog_entries.dat';
	if ( ! file_exists($eblog_entries_file))
	{
		header("Content-Type: application/json; charset=UTF-8");
		echo '{success:0}';
		exit;
	}

	$eblog_datas = unserialize(file_get_contents($eblog_entries_file));
	if ( ! isset($eblog_datas[$pagename]))
	{
		header("Content-Type: application/json; charset=UTF-8");
		echo '{success:0}';
		exit;
	}

	// コメントの作成
	$source = get_source($pagename);

	$comment_datas = array();
	$comment_start = FALSE;
	$comment_count = 1;
	$eye_catch = '';
	$header = '';
	$title = '';
	foreach ($source as $i => $line)
	{
		if (preg_match($ignore_plugin, $line))
		{	// リストから省く
			$source = array();
			break;
		}

		if (preg_match('/^#blog_comment/', $line))
		{
			$comment_start = TRUE;
			unset($source[$i]);
		}
		else if ($comment_start && preg_match('/^(-{1,3})([^-].*)$/', $line, $ms))
		{
			list($msg, $info) = explode('--', $ms[2], 2);
			$msg = str_replace('&br;', "\n", $msg);
			if (preg_match('/^(.*)\s&new\{(\d{4}-\d{2}-\d{2}).*(\d{2}:\d{2}:\d{2})\};$/', trim($info), $ms2))
			{
				$comment_name = $ms2[1];
				if (isset($ms2[2]))
				{
					$comment_datetime = $ms2[2].' '.$ms2[3];
				}
				else
				{
					$comment_datetime = date('Y-m-d H:i:s');
				}
			}

			$comments[$comment_count] = array(
				'id'       => $comment_count++,
				'msg'      => $msg,
				'title'    => '',
				'datetime' => $comment_datetime,
				'name'     => $comment_name,
				'accepted' => 1,
				'show'     => 1,
				'admin'    => 0,
			);
			unset($source[$i]);
		}
		else if ($comment_start)
		{
			unset($source[$i]);
		}
		//タイトルのセット
		else if (preg_match('/^TITLE:(.*)/', $line, $ms))
		{
			$title = $ms[1];
			unset($source[$i]);
		}
		//見出し
		else if (preg_match('/^(\*{1,3})(.*)\[#\w+\]\s?/', $line, $ms))
		{
			if ($ms[1] == '*' && $header == '')
			{
				$header = trim($ms[2]);
				unset($source[$i]);
			}
		}
		//使わないブロックプラグインやキーワード
		else if (preg_match($strip_plugin, $line))
		{
			unset($source[$i]);
		}
		//ブロックプラグインを除く
		else if (preg_match('/^(#topicpath|#blog_)/', $line))
		{
			unset($source[$i]);
		}
		// 画像検索　アイキャッチにする画像を探す
		else if ($eye_catch === '' && preg_match('/(?:^#show|&show)\(([^,]+).*\)/', $line, $mts))
		{
			$eye_catch = $mts[1];
		}
	}

	// TITLE指定がなくて見出し1があったら見出し1をタイトルにする
	// 何も指定がなかったら、日付を挿入する
	if ($title == '')
	{
		$title = ($header != '') ? $header : $eblog_datas[$pagename]['date'];
	}
	array_unshift($source, "TITLE:{$title}\n");

	// QBlogへ書き込む
	$newpage = qblog_get_newpage($eblog_datas[$pagename]['date']);
	page_write($newpage, join($source));

	// ブログのキャッシュファイルの作成
	$options = array(
		'category' => $qblog_default_cat,
		'image' => $eye_catch
	);
	qblog_update_post(TRUE, $newpage, $options);

	// コメントの投稿
	// 昇順でリストを作成
	ksort($comments);

	// コメントキャッシュファイルの作成
	$commentcachefile = CACHEQBLOG_DIR . encode($newpage).'.qbcm.dat';
	file_put_contents($commentcachefile, serialize($comments), LOCK_EX);

	// アメブロリストファイルに完了フラグをたてる
	$eblog_datas[$pagename]['complete'] = 1;
	file_put_contents($eblog_entries_file, serialize($eblog_datas), LOCK_EX);

	// 簡易ブログの記事にQBlogの記事へredirect
	// 302リダイレクトで検索エンジン対策にも
	$pagedata = get_source($pagename);
	array_unshift($pagedata, "#redirect({$newpage}, 302)\n");
	page_write($pagename, join("", $pagedata), TRUE);

	// 成功
	$eblog_datas[$pagename]['success'] = 1;
	header("Content-Type: application/json; charset=UTF-8");
	echo json_encode($eblog_datas[$pagename]);
	exit;

	// !タグをどうするこうする
}

function plugin_qblog_start()
{
	global $qblog_defaultpage, $qblog_menubar, $vars, $script;

	if (is_page($qblog_defaultpage))
	{
		$vars['qblog_error'] = 'ブログトップページ（'.$qblog_defaultpage.'）は既に存在します。<br />
ブログ以外で使用している場合は、別のページ名に変更してください。
';
		return;
	}

	if (is_page($qblog_menubar))
	{
		$vars['qblog_error'] = 'ブログメニューページ（'.$qblog_menubar.'）は既に存在します。<br />
ブログ以外で使用している場合は、別のページ名に変更してください。
';
		return;
	}

	// ブログトップ
	$source = '
#qblog_list

';
	page_write($qblog_defaultpage, $source);


	// ブログメニュー
	$source = "
* 最新の記事
#qblog_recent(5)

* カテゴリ
#qblog_category

* 最近のコメント
#qblog_recent_comments

* ブログ　アーカイブ
#qblog_archives

";
	page_write($qblog_menubar, $source);

	redirect($script.'?'.'cmd=qblog&phase=set_title', "ブログを開始しました\n次の設定に進みましょう");

}

function plugin_qblog_edit_title()
{
	global $vars, $script, $qblog_title;

	$title = $vars['title'];

	if (exist_plugin("qhmsetting"))
	{
		$params = array();
		$params['qblog_title'] = $title;
		$_SESSION['qhmsetting'] = $params;
		plugin_qhmsetting_update_ini();

		$vars['phase'] = 'edit_title';

		$qblog_title = $title;
	}

	return array('msg' => '', 'body' => '');

}

function plugin_qblog_enable_comment()
{
	global $vars, $script, $qblog_enable_comment;

	$commentflg = (isset($vars['qblog_enable_comment']) AND $vars['qblog_enable_comment'] == 1) ? $vars['qblog_enable_comment'] : 0;

	if (exist_plugin("qhmsetting"))
	{
		$params = array();
		$params['qblog_enable_comment'] = $commentflg;
		$_SESSION['qhmsetting'] = $params;
		plugin_qhmsetting_update_ini();

		$vars['phase'] = 'enable_comment';
		$qblog_enable_comment = $commentflg;
	}

	return array('msg' => '', 'body' => '');
}

function plugin_qblog_close()
{
	global $vars, $script, $qblog_close;

	$vars['hash'] = 'misc';

	$closed = (isset($vars['qblog_close']) AND $vars['qblog_close'] == 1) ? $vars['qblog_close'] : 0;

	if (exist_plugin("qhmsetting"))
	{
		$params = array();
		$params['qblog_close'] = $closed;
		$_SESSION['qhmsetting'] = $params;
		plugin_qhmsetting_update_ini();

		$vars['phase'] = 'close';
		$qblog_close = $closed;
	}

	return array('msg' => '', 'body' => '');
}


function plugin_qblog_rename_category()
{
	global $vars,$qblog_default_cat;

	$vars['hash'] = 'category';

	// カテゴリの変更元と変更する名前を取得
	$newname = trim($vars['cat_name']);
	$orgname = trim($vars['org_cat_name']);

	if ($newname == '')
	{
		$vars['qblog_error'] = '新しいカテゴリー名を指定してください。';
		return FALSE;
	}

	$orgfile= CACHEQBLOG_DIR . encode($orgname) . '.qbc.dat';
	$newfile= CACHEQBLOG_DIR . encode($newname) . '.qbc.dat';

	if (file_exists($newfile))
	{
		$vars['qblog_error'] = '指定したカテゴリーは、既に存在します。';
		return FALSE;
	}


	// デフォルトカテゴリの登録
	if ($orgname == $qblog_default_cat)
	{
		if (exist_plugin("qhmsetting"))
		{
			$params = array();
			$params['qblog_default_cat'] = $newname;
			$_SESSION['qhmsetting'] = $params;
			plugin_qhmsetting_update_ini();
			$qblog_default_cat = $newname;
		}
		else
		{
			$vars['qblog_error'] = 'このバージョンでは、初期カテゴリー名が変更できません。';
			return FALSE;
		}
	}

	// カテゴリの変更元の記事を取得
	$pages = array();
	if (file_exists($orgfile))
	{
		$pages = explode("\n", file_get_contents($orgfile));
	}

	// ページ名.qbp.datのカテゴリ名を変更
	foreach ($pages as $page)
	{
		$file = CACHEQBLOG_DIR.encode($page).'.qbp.dat';
		if (file_exists($file))
		{
			$data = unserialize(file_get_contents($file));
			$data['category'] = $newname;
			file_put_contents($file, serialize($data), LOCK_EX);
		}
	}

	// カテゴリファイルのrename
	rename($orgfile, $newfile);

	// qblog_categories.dat の変更
	qblog_update_categories(TRUE);

	$vars['phase'] = 'rename_category';

	return TRUE;

}

function plugin_qblog_get_default_ping()
{
	$ping = <<< EOP
http://blog.goo.ne.jp/XMLRPC
http://blogsearch.google.co.jp/ping/RPC2
http://ping.bloggers.jp/rpc/
http://ping.blogranking.net/
http://ping.fc2.com/
http://ping.namaan.net/rpc/
http://pingoo.jp/ping/
EOP;
	return trim($ping);
}

function plugin_qblog_update_ping()
{
	global $vars, $qblog_enable_ping, $qblog_ping;

	$vars['hash'] = 'external';

	$enable_ping = $vars['qblog_enable_ping'];
	$pingstr = $vars['ping'];

	$params = array();
	$params['qblog_enable_ping'] = $enable_ping;
	$vars['phase'] = 'ping';
	$qblog_enable_ping = $enable_ping;

	if ( ! $enable_ping)
	{
		if (exist_plugin("qhmsetting"))
		{
			$_SESSION['qhmsetting'] = $params;
			plugin_qhmsetting_update_ini();

			$vars['phase'] = 'ping';
		}
		return array('msg'=>'', 'body'=>'');
	}


	$default_ping = plugin_qblog_get_default_ping();

	//初期Ping から変更があれば保存
	if (md5($default_ping) !== md5($pingstr))
	{
		//ping 保存
		if (exist_plugin("qhmsetting"))
		{
			$params['qblog_ping'] = $pingstr;
			$_SESSION['qhmsetting'] = $params;
			plugin_qhmsetting_update_ini();

			$qblog_ping = $pingstr;

			//ping 送信
			send_qblog_ping();
		}
	}

	return array('msg'=>'', 'body'=>'');

}

function plugin_qblog_update_comment_notice()
{
	global $script, $vars, $qblog_comment_notice, $admin_email;

	if (trim($admin_email) === '')
	{
		$vars['qblog_error'] = '管理者メールアドレスが設定されていません。';
		return FALSE;
	}

	$notice = (isset($vars['qblog_comment_notice']) && $vars['qblog_comment_notice']) ? 1 : 0;

	if (exist_plugin("qhmsetting"))
	{
		$params = array();
		$params['qblog_comment_notice'] = $notice;
		$_SESSION['qhmsetting'] = $params;
		plugin_qhmsetting_update_ini();

		$vars['phase'] = 'comment_notice';
		$qblog_comment_notice = $notice;
	}

	return TRUE;
}

/* End of file qblog.inc.php */
/* Location: ./plugin/qblog.inc.php */
