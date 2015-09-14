QHM v6 haik
====

QHM v6 haik は、[PukiWiki][pukiwiki] をベースにしたウェブサイト作成システムです。

## Description

QHM v6 haik はPHPの動作する環境へ設置するだけですぐに使えるウェブサイト作成システムです。
初期設定がほとんど必要ないため、素早いサイト作成が可能です。


## Requirement

PHP 5.1.6 以上

## Usage

PHP の動作するサーバーへ設置後、ログイン画面より下記の初期ユーザー名とパスワードで管理者ログインしてください。

- 初期ユーザー名： `homepage`
- パスワード： `makeit`

ログイン画面は初期画面の右下にある **QHM** リンクをクリックすると行けます。


## Install

最新版をサーバーへ展開してください。
PHPの実行ユーザーと設置場所のオーナーが異なる場合、PHPの実行ユーザーが下記のファイルやフォルダを変更できるようパーミッションを変更してください。

```
qhm.ini.php, qhm_access.ini.txt, qhm_users.ini.txt,
attach/, attach/*, backup/, backup/*, cache/, cache/*,
cacheqblog/, cacheqblog/*, cacheqhm/, cacheqhm/*,
counter/, counter/*, diff/, diff/*, wiki/, wiki/*
```

### QHM Installer

1 ファイルインストーラーを提供しています。
[Open QHM](openqhm)からダウンロードしてください。


## Document

[Open QHM][openqhm]でドキュメントを公開しています。


## Contribution

1. Fork (<https://github.com/open-qhm/qhm/fork>)
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create new Pull Request


## Licence

[GPL v2][license]


## Author

QHMは[株式会社北摂情報学研究所][hokuken]が作成、管理しています。


[pukiwiki]: http://pukiwiki.osdn.jp/
[hokuken]: http://www.hokuken.com/
[license]: https://github.com/open-qhm/qhm/blob/master/LICENSE
[openqhm]: http://www.open-qhm.net/
