HAIK
====

HAIK は、[PukiWiki][pukiwiki] をベースにしたウェブサイト作成システムです。

## Description

HAIK はPHPの動作する環境へ設置するだけですぐに使えるウェブサイト作成システムです。
初期設定がほとんど必要ないため、素早いサイト作成が可能です。


## Requirement

PHP 5.3 以上

## Usage

PHP の動作するサーバーへ設置後、ログイン画面より下記の初期ユーザー名とパスワードで管理者ログインしてください。

- 初期ユーザー名： `homepage`
- パスワード： `makeit`

ログイン画面は初期画面の右下にある **HAIK** リンクをクリックすると行けます。


## Install

最新版をサーバーへ展開してください。
PHPの実行ユーザーと設置場所のオーナーが異なる場合、PHPの実行ユーザーが下記のファイルやフォルダを変更できるようパーミッションを変更してください。

```
qhm.ini.php, qhm_access.ini.txt, qhm_users.ini.txt,
attach/, attach/*, backup/, backup/*, cache/, cache/*,
cacheqblog/, cacheqblog/*, cacheqhm/, cacheqhm/*,
counter/, counter/*, diff/, diff/*, wiki/, wiki/*
```

## Document

[こちらのサイト](https://haik-cms.jp/)でドキュメントを公開しています。


## Contribution

1. Fork (<https://github.com/open-qhm/qhm/fork>)
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create new Pull Request


## Issues

改善要望や不具合報告はこのレポジトリではなく、 [HAIK-Team](https://haik-cms.jp/) のお問い合わせ窓口をご利用ください。

- [HAIK-CLUB 会員の方](http://club.haik-cms.jp/index.php?report-form)
- [一般の方](https://haik-cms.jp/index.php?contact)

## Licence

[GPL v2][license]


[pukiwiki]: http://pukiwiki.osdn.jp/
[hokuken]: http://www.hokuken.com/
[license]: https://github.com/open-qhm/qhm/blob/master/LICENSE
[haik-cms]: https://haik-cms.jp/
[haik-cms-man]: http://manual.haik-cms.jp/
[qhm-installer]: https://github.com/open-qhm/qhm-installer


## [BrowserStack](https://www.browserstack.com/) - Cross-browser testing tool

We appreciate the support of BrowserStack for providing cross browser test service to our projects!

[![BrowserStack](https://user-images.githubusercontent.com/808888/38138970-68707288-3468-11e8-904d-907049942f19.png)](https://www.browserstack.com/)


