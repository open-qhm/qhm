<div class="admin qhmupdate">
  <a href="<?php echo h($script)?>">QHMトップ</a> &gt; <a href="<?php echo h($script . '?cmd=qhmsetting')?>">設定一覧</a> &gt; アップデート

  <h2>QHMの手動アップデート</h2>

  <p>
      下記の手順でQHMを最新版へ更新してください。
  </p>
  <ol>
    <li>最新版のパッケージを<a href="<?php echo h($download_url) ?>">こちら</a>からダウンロード</li>
    <li>パッケージ（zip 圧縮ファイル）を展開します</li>
    <li>
      ユーザーデータが格納されているファイルやフォルダを除いてファイルを上書きしてください。<br>
      <a href="#excludes">除外するファイルやフォルダの一覧</a>
    </li>
  </ol>
  <h4 id="excludes">除外するファイル・フォルダ一覧</h4>
    <pre>
        qhm.ini.php qhm_access.ini.txt qhm_users.ini.txt
        attach/ backup/ cache/ cacheqblog/ cacheqhm/ counter/ diff/
        skin/hokukenstyle/ swfu/d/ swfu/data/ trackback/ wiki/
    </pre>
</div>
