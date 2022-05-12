/**
 *   JQuery Plugin ClickPad
 *   -------------------------------------------
 *   jquery.clickpad.js
 *
 *   Copyright (c) 2009 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2009-11-24
 *   modified : 2010-04-08 revise counter button
 *
 *   テキストエリアに様々な拡張機能を付けます
 *   基本は、QHM 用のプラグイン、Wiki 書式挿入エンジンです。
 *   ボタン設定を拡張することで様々なボタンを作成可能です。
 *
 *   Usage :
 *     $("textarea").clickpad([option]);
 *
 *     Option : {buttons:'qhmpro', autoGrow: true, replaces: {}}
 *
 *       buttons <Array> or string: ボタンセット配列か、プリセットボタンセット名を指定します
 *         オプションが無指定の場合、QHM 向けのボタンセットを設定します（qhm）
 *
 *         Array: [{buttonSet1}, {buttonSet2}, ...]
 *           ボタンセット配列を指定する場合、以下の構造に従ってください。
 *           [{ButtonSet1}, {ButtonSet2}, ...]
 *
 *           ButtonSet: {buttons:[buttonArray], margin:[marginArray], css:{css}, backgroundImage:"URL"}
 *             ボタンセットは以下の構造に従ってください。
 *             {buttons:[[buttonArray], [buttonArray], ...], margin:[xMargin,yMargin], css:{property:value}}
 *
 *             buttonArray: [[buttonNameArray1], [buttonNameArray2]]
 *               配列の数 = 行数になります。
 *               ButtonNameArray は以下の構造に従ってください。
 *               ['name', 'name2', 'name3', ...]
 *
 *             margin: [xMargin, yMargin]
 *               ボタンとボタンの間のピクセル数を指定します。
 *               xMargin は 横方向、yMargin は縦方向のマージンです。
 *               マージン指定は配列、あるいは数字で指定します。
 *               配列の場合、[x,y] の様に指定してください。
 *               縦横方向のマージンが同じ場合は、数字のみの指定も可能です。
 *
 *             css: {property:value, property2:value2, ...}
 *               ボタンセットのスタイル指定ができます。
 *
 *             backgroundImage: "URL of background-image"
 *               ボタンセットのボタンが使う背景画像を指定できます。
 *               初めからあるボタンは、デフォルトで「image/hokuken/toolbox2.png」となっていますが、
 *               QHM では画像フォルダは「image」です。
 *               この場合、"image/hokuken/toolbox2.png" とすることで、
 *               正しい指定にすることができます。
 *               ※ 一つの背景画像をポジションでずらしている場合のみ使ってください。
 *
 *             clear: buttonSet has clear:both block?
 *               ボタンセットの後に <div style="clear:both"></div> を追加するかどうかのフラグ
 *               デフォルトで null
 *               CSS などで Float を有効にした場合、true にすると良いかと。
 *
 *         string: "preset of button set"
 *           文字列で指定する場合、あらかじめ定義したボタンセットを入力してください。
 *           現在用意されているのは、qhmpro, commu, qnews, です。
 *           qhmmob の場合、罫線と絵文字パレット（未定義）があるので、
 *           別ファイルに退避したボタン定義を読み込む必要があります。（mobile_buttons.js 予定）
 *           commu や qnews の場合で、イメージマネージャーを使う際は、
 *           必ず thickbox.js と thickbox.css を読み込んでください。
 *
 *       autoGrow <boolean>: テキストエリアが文章の入力量に従い、伸び縮みするかどうか指定します。デフォルトで true です。
 *
 *       minLine <integer>: autoGrow を有効にした場合、この行数以下に縮むことがなくなります。
 *
 *       maxLine <integer>: autoGrow を有効にした場合、この行数以上に伸びることがなくなります。
 *
 *       replaces <Object>: ボタン設定の中で使う、置換文字列のキーと値のセット
 *         Object: {key: value, key2: value2, ...}
 *           置換文字列の中にある、${key} を "value" で置換します。
 *           現在分かっている中で、image （イメージマネージャー）ボタンを使用する際に、sessid:[session id] を指定する必要があります。
 *
 *       css <Object>: ボタン全体を囲む div のスタイル設定
 *
 *       showAtFocus <boolean>: 普段はボタンを隠し、textarea にフォーカスが当たったら表示するようにします。デフォルトで false
 *
 *
 *
 *   Appendix :
 *     ■ ボタン定義の構造について
 *     ボタン定義には決まった構造があります。
 *     新しくボタンを定義する場合、定義したボタンデータを jQuery.clickpad.buttonData にマージしてください。
 *     ※ 既にあるボタン名に被らないよう、注意が必要です。
 *
 *     ボタンデータは、ボタン名:{ボタン定義} の集まりです。
 *     簡略化したものは次のように表せます。
 *       {buttonName: {buttoanDefinition}, buttonName2: {buttonDefinition2}}
 *     ※ このプラグインを読んだ状態で、既に、QHMプロ、モバイル、コミュ、Qニュースのほとんどのボタンが使用可能です。
 *
 *     ■ ボタン定義の構造
 *     まず、英数字のみでボタンの役割を表現してください、それがボタン名となります。
 *       例）header, link など
 *
 *     次に、ボタンの細かい挙動を指定します。
 *     基本的に 6-7 つの項目を設定します。
 *       caption, width, height, background, func, value, replaces
 *     の 7 つです。
 *     ※ replaces はほとんど使用しません
 *
 *     これらを合わした場合、次のような状態となります。
 *     buttonName: {caption:"Caption", width:25, height:25, background:"url(hoge.png)", func:"cpInsert", value:"fuga"}
 *
 *
 *     一つずつ説明します。
 *     caption <string>: ボタンのフルネームです。マウスオーバーした時にポップアップされます
 *     width  <integer>: ボタンの幅です。ピクセル数を指定します。
 *     height <integer>: ボタンの高さです。ピクセル数を指定します。
 *     background <Array or string>: 背景指定のCSS を書きます。配列にし、二つ書いた場合、ホバーイベントが設定されます。
 *     func <string>: ボタンが要求する動作です。cpInsert と cpEnclose, cpDialog, cpEval が用意されています。詳細は後で。
 *     value <Array or string>: func に渡す引数です。こちらも func の詳細説明の項目で説明します。
 *     replaces <Array>: ダイアログなどの入力以外で、文字列を操作したい場合などに使用します。これも詳しくは後々。
 *
 *
 *     ■ 機能
 *     func と value について、説明します。
 *
 *     cpInsert(value<string>): "Insert value"
 *       カーソル位置に文字列 value を挿入します。
 *
 *     cpEnclose(value<Array>): ["enclose start", "enclose end"]
 *       選択範囲を指定した文字列で囲みます。
 *       value には、長さ 2 の配列を指定します。
 *       例）["##", "##"]
 *
 *     cpDialog(value<Array>): [promptSettings, "enclose start", "enclose end"]
 *       ダイアログウィンドウを出し、入力を促します。入力値を適用した文字列を、カーソル位置、または選択範囲を囲むように挿入します。
 *       value には、長さ 2-3 の配列を指定します。
 *       "enclose end" は必要ない場合、指定しなくとも結構です。
 *       例）["Please input font-size", "&size(${1}){", "};"]
 *       例）["Please input style", "#style(${1}){{\n\n}}\n"]
 *
 *       promptSettings <Array or string>: [promptSetting, promptSetting2, ...] or "Prompt message"
 *         入力値の説明を書きます。配列に入れた場合、promptSetting の集合として、複数の入力をさせることができます。
 *         promptSettings の要素数と、start で使う置換マーカーの数は合わせてください。
 *
 *         promptSetting <Object or string>: {msg:"Prompt message", option:{promptOptions}} or "Prompt message"
 *           promptSetting では、プロンプトの種類をテキスト入力欄以外にも、チェックボックスとセレクトボックスにすることができます。
 *           種類を変える場合、promptSetting をオブジェクトにする必要があります。
 *           例）{msg:"Bold Font", option:{type:"checkbox", value:"b"}}
 *
 *           promptOption <Object>: {type:"Prompt type", defval:"default value", value:"insert value", values:"select and radio options", and more...}
 *             promptOption では、プロンプトの種類とそれに伴う必須設定を書きます。
 *             現在では、input:text と、input:checkbox、input:radio、select に対応しています。
 *             type を省略した場合、input:text が自動的に選択されます。
 *
 *             ■ 共通のプロパティ
 *               css: <Object> {css-property: value, ...}
 *                 部品1つを囲む DIV のスタイルを指定することができます。
 *
 *             ■ それぞれの種類ごとの特徴とプロパティ
 *             type:"text"
 *               テキスト入力欄を表示します。デフォルト値を設定できます。
 *               inputWidthRatio を 0.1〜1 で設定することで入力欄の長さを伸縮させることができます。
 *               defval: "Default string"
 *               inputWidthRatio: 0.5
 *
 *             type:"checkbox"
 *               チェックボックスを表示します。チェックされた際の値を設定できます。
 *               value: "Checked string"
 *
 *             type:"radio"
 *               ラジオボタンを表示します。選択された際の値を設定できます。
 *               checked を真にすることで最初から選択されている項目を設定できます。
 *               オブジェクトの配列は以下の構造で指定してください。
 *               values: [{label: "Label of radio", value: "Input String", checked: true}, {label: "", value: ""}, ...]
 *
 *             type:"select"
 *               セレクトボックスを表示します。セレクトオプションを指定できます。
 *               selected を真にすることで最初から選択されている項目を設定できます。
 *               オブジェクトの配列は以下の構造で指定してください。
 *               values: [{key: "Display String", value: "Input String"}, {key: "", value: ""}, ...]
 *
 *
 *       enclose start <string>: "encloseStart"
 *         選択した文字列の前に挿入される文字列です。
 *         テンプレート機能を持ち、プロンプトにて入力された値を差し込むことができます。
 *         一番初めのプロンプトの値を ${1}、それ以降、2, 3 と増えていきます。
 *         例）[["prompt 1", "prompt2", "prompt3"], "&hoge(${1},${2}-${3}){", "};"]
 *
 *       enclose end <string>: "encloseEnd"
 *         選択した文字列の後ろに挿入される文字列です。
 *         囲む必要のない場合、無指定にしてください。
 *         例）["prompt", "#hoge(${1}){{\n\n}}\n"]
 *
 *     cpEval(value<string>): "eval string"
 *       指定した javascript を実行します。
 *       また、buttonData にて replace を指定している場合、置換をしてから実行します。
 *       また、${textarea} という文字列があれば、そこを関連づけられた textarea の id に置換します。
 *
 *
 *
 *     ■ replaces の活用
 *       option.replaces = {"key":"value"}
 *       と指定しておけば、
 *       buttonData の replaces に ["key"] と書くことで、
 *       cpEval に渡す文字列の、${"key"} を "value" に置換できます。
 *       この方法は、セッションID など、CGI 経由でしか渡せない値などを使う際に便利です。
 *
 *       buttonData.replaces = [{"key":"value"}]
 *       の指定では、
 *       cpEval に渡す文字列の、${"key"} を "value" に置換できます。
 *       作った僕もいまいち使いどころがわかりません。
 *
 */

$(document).ready(function(){

	//ブラウザを判定する
	var browser = 2;
	var $textarea = $("textarea[id]");
	var id;
	if ($textarea.length > 0) {
		id = $textarea.eq(0).attr("id");
		if( document.getElementById(id).setSelectionRange ){

		} else if( document.selection.createRange ){
			browser=1;
		}
		$.clickpad.browser = browser;
	}
});

(function() {
	if (!jQuery.clickpad) jQuery.clickpad = {};

	jQuery.clickpad.total = 0;
	jQuery.clickpad.b_total = 0;
	jQuery.clickpad.bs_total = 0;

	// !qhm buttons
	jQuery.clickpad.buttonData = {
		'header': {
			caption: '見出し',
			width: 47,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat 0 0',
			func: 'cpInsert',
			value: '\n* 見出し１\n'
		},
		'contents': {
			caption: '目次',
			width: 47,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -47px 0',
			func: 'cpInsert',
			value: '\n#contents\n'
		},
		'link': {
			caption: 'リンク',
			width: 47,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -94px 0',
			func: 'cpDialog',
			value: [
				[
					{msg:"リンク名を入力してください。",option:{
						type:'text',
						useSelection:true
					}},
					{msg:"リンク先（ページ名, URL）を入力してください。",option:{
						type:'text'
					}}
				],
				'[[${1}', '>${2}]]']
		},
		'htmllink': {// link のクローン
			caption: 'リンク',
			width: 47,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -94px 0',
			func: 'cpDialog',
			value: [['リンク名を入力してください。', {msg:'URL を入力してください',option:{defval:'http://'}}], '[[${1}>${2}]]']
		},
		'title': {
			caption: 'タイトル変更',
			width: 47,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -141px 0',
			func: 'cpInsert',
			value: '\nTITLE:ここにタイトルを入れる\n'
		},
		'counter': {
			caption: 'アクセスカウンター',
			width: 47,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -188px 0',
			func: 'cpInsert',
			value: '&deco(gray,12){a:&counter(total); t:&counter(today); y:&counter(yesterday);};'
		},
		'comment': {
			caption: 'コメント機能',
			width: 47,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -235px 0',
			func: 'cpInsert',
			value: '\n#comment2\n'
		},
		'ul': {
			caption: '箇条書き',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -282px 0',
			func: 'cpInsert',
			value: '\n- 箇条書き1\n-- 箇条書き2\n--- 箇条書き3\n- 箇条書き1\n'
		},
		'ol': {
			caption: '番号付き箇条書き',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -310px 0',
			func: 'cpInsert',
			value: '\n+ 箇条書き1\n+ 箇条書き2\n+ 箇条書き3\n'
		},
		'attach': {
			caption: '添付（画像など）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -338px 0',
			func: 'cpInsert',
			value: '&show(,,画像の説明);'
		},
		'br': {
			caption: '改行',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -366px 0',
			func: 'cpInsert',
			value: '&br;'
		},
		'b': {
			caption: '太字',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -394px 0',
			func: 'cpEnclose',
			value: ['\'\'', '\'\'']
		},
		'u': {
			caption: '下線',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -450px 0',
			func: 'cpEnclose',
			value: ['%%%', '%%%']
		},
		'i': {
			caption: '斜体',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -422px 0',
			func: 'cpEnclose',
			value: ['\'\'\'', '\'\'\'']
		},
		'handline': {
			caption: '手書き下線',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -450px 0',
			func: 'cpEnclose',
			value: ['##', '##']
		},
		'size': {
			caption: '文字サイズ',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -478px 0',
			func: 'cpDialog',
			value: ['文字サイズを入力してください。(少し大きく:18、小さく:12)', '&size(${1}){', '};']
		},
		'sizeD': {//deco
			caption: '文字サイズ',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -478px 0',
			func: 'cpDialog',
			value: ['文字サイズを入力してください。(少し大きく:18、小さく:12)', '&deco(${1}){', '};']
		},
		'sizeM': {
			caption: '文字サイズ',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -478px 0',
			func: 'cpDialog',
			value: [
				[
					{msg:"文字サイズ",option:{
						type:'select',
						values:[
							{key:"xx-small", value:"xx-small"},
							{key:"x-small", value:"x-small"},
							{key:"small", value:"small"},
							{key:"medium", value:"medium", selected:true},
							{key:"large", value:"large"},
							{key:"x-large", value:"x-large"},
							{key:"xx-large", value:"xx-large"}
						]}
					}
				],
				'&size(${1}){', '};'
			]
		},
		'penYellow': {
			caption: '蛍光ペン（黄）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -506px 0',
			func: 'cpEnclose',
			value: ['&color(,yellow){\'\'', '\'\'};']
		},
		'penYellowD': {
			caption: '蛍光ペン（黄）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -506px 0',
			func: 'cpEnclose',
			value: ['&deco(b,,yellow){', '};']
		},
		'penRed': {
			caption: '蛍光ペン（赤）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -534px 0',
			func: 'cpEnclose',
			value: ['&color(,pink){\'\'', '\'\'};']
		},
		'penRedD': {
			caption: '蛍光ペン（赤）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -534px 0',
			func: 'cpEnclose',
			value: ['&deco(b,,pink){', '};']
		},
		'penBlue': {
			caption: '蛍光ペン（青）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -562px 0',
			func: 'cpEnclose',
			value: ['&color(,paleturquoise){\'\'', '\'\'};']
		},
		'penBlueD': {
			caption: '蛍光ペン（青）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -562px 0',
			func: 'cpEnclose',
			value: ['&deco(b,,paleturquoise){', '};']
		},
		'penGreen': {
			caption: '蛍光ペン（緑）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -590px 0',
			func: 'cpEnclose',
			value: ['&color(,palegreen){\'\'', '\'\'};']
		},
		'penGreenD': {
			caption: '蛍光ペン（緑）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -590px 0',
			func: 'cpEnclose',
			value: ['&deco(b,,palegreen){', '};']
		},
		'left': {
			caption: '左揃え',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -618px 0',
			func: 'cpInsert',
			value: 'LEFT:'
		},
		'center': {
			caption: '中央揃え',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -646px 0',
			func: 'cpInsert',
			value: 'CENTER:'
		},
		'right': {
			caption: '右揃え',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -674px 0',
			func: 'cpInsert',
			value: 'RIGHT:'
		},
		'table': {
			caption: '表',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -702px 0',
			func: 'cpInsert',
			value: '\n|~項目名1 |~項目名2 |~項目名3 |\n| 項目1 | 項目2 | 項目3 |\n'
		},
		'HTML': {
			caption: 'HTMLタグ',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -730px 0',
			func: 'cpInsert',
			value: '\n#html{{\n(ここにHTMLタグを挿入)\n}}\n'
		},
		'stylebox': {
			caption: '枠',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -758px 0',
			func: 'cpInsert',
			value: '\n#style(class=bluebox2){{\n(ここに内容を書く)\n}}\n'
		},
		'styleboxp': {
			caption: '枠',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -758px 0',
			func: 'cpDialog',
			value: [
				[
					{
						msg: '枠の色',
						option: {
							type:"radio",
							values:[
								{value:'blue', label:'<span style="color:blue;">青</span>', checked:true},
								{value:'purple', label: '<span style="color:purple;">紫</span>'},
								{value:'red', label: '<span style="color:red;">赤</span>'},
								{value:'brown', label: '<span style="color:brown;">茶</span>'},
								{value:'orange', label: '<span style="color:orange;">橙</span>', br: true},
								{value:'yellow', label: '<span style="color:#F3DF81;">黄</span>'},
								{value:'green', label: '<span style="color:green;">緑</span>'},
								{value:'black', label: '<span style="color:black;">黒</span>'},
								{value:'gray', label: '<span style="color:gray;">灰</span>'}
					]}},
					{msg: "線の種類（直線、破線）", option: {type: "select", values:[{key:'────',value:"s"}, {key:'-------',value:"d"}]}},
					{msg: "背景色", option: {type: "radio", values: [{label:"同系色", value:"s", checked:true},{label:"白色", value:"w"}]}},
					{msg: "枠のサイズ", option: {type: "radio", values: [{label:"100%", value:"l"},{label:"80%", value:"m", checked:true},{label:"60%", value:"s"}]}}
				],
				'\n#style(class=box_${1}_${2}${3}${4}){{\n（ここに内容を書く）\n}}\n'
			]
		},
		'onepage': {
			caption: 'セールスレター型デザイン',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -786px 0',
			func: 'cpDialog',
			value: [
				[
					{msg:'見出し色',option:{
						type: 'text',
						defval:'red',
						inputWidthRatio: 0.3,
						css:{clear:"both"}
					}},
					{msg:"見出しフォント", option: {type: "radio", values: [{label:"ゴシック", value:"g", checked:true},{label:"明朝", value:"m"}]}},
					{msg:"本文フォント", option: {type: "radio", values: [{label:"ゴシック", value:"g", checked:true},{label:"明朝", value:"m"}]}},
					{msg:'背景色',option:{
						type: 'text',
						defval:'gray',
						inputWidthRatio: 0.3,
						css:{clear:"both"}
					}}
				],
				'\nKILLERPAGE2:${1}${2}${3},${4}\n'
			]
		},
		'bullet': {
			caption: 'ブレット',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -814px 0',
			func: 'cpInsert',
			value: '\n:>>|ここにブレットを入れる\n'
		},
		'check': {
			caption: 'レ注目',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -842px 0',
			func: 'cpInsert',
			value: '&check;'
		},
		'strike': {
			caption: '取り消し線',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -870px 0',
			func: 'cpEnclose',
			value: ['%%', '%%']
		},
		'whiteRed': {
			caption: '白抜き文字（赤）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -898px 0',
			func: 'cpEnclose',
			value: ['&color(white,red){', '};']
		},
		'whiteRedD': {
			caption: '白抜き文字（赤）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -898px 0',
			func: 'cpEnclose',
			value: ['&deco(white,red){', '};']
		},
		'whiteBlack': {
			caption: '白抜き文字（黒）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -926px 0',
			func: 'cpEnclose',
			value: ['&color(white,black){', '};']
		},
		'whiteBlackD': {
			caption: '白抜き文字（黒）',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -926px 0',
			func: 'cpEnclose',
			value: ['&deco(white,black){', '};']
		},
		'deco': {
			caption: '文字装飾',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -1104px 0',
			func: 'cpDialog',
			value: [["装飾オプションをカンマ区切りで書いてください"], '&deco(${1}){', '};']
		},
		'decop': {//deco ダイアログ
			caption: '装飾',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -1104px 0',
			func: 'cpDialog',
			value: [
				[
					{msg:"<b>太字</b>",option:{
						type:'checkbox',
						value:'b,',
						css:{float:"left", width:"30%"}
					}},
					{msg:"<i>斜体</i>",option:{
						type:'checkbox',
						value:'i,',
						css:{float:"left", width:"30%"}
					}},
					{msg:"<u>下線</u>",option:{
						type:'checkbox',
						value:'u,',
						css:{float:"left", width:"auto", marginRight:10}
					}},
					{msg:'<span style="color:#2C48BF">文字色</span>を入力してください',option:{
						type: 'text',
						inputWidthRatio: 0.3,
						css:{clear:"both"}
					}},
					{msg:'<span style="background-color:#2C48BF;color:#fff">背景色</span>を入力してください',option:{
						type: 'text',
						inputWidthRatio: 0.3
					}},
					{msg:'文字サイズ<span style="font-size:11px">（数値、単位付き指定（ex:2em）、small、largeなど）</span>',option:{
						type: 'text',
						inputWidthRatio: 0.3
					}}
				],
				'&deco(${1}${2}${3}${4},${5},${6}){', '};']
		},
		'plugins': {
			caption: 'その他の機能',
			width: 102,
			height: 26,
			background: 'url(image/hokuken/otherplugin.png) no-repeat 0 0',
			func: 'cpEval',
			value: 'otherplugin()'
		},
    // !qhm-haik buttons
    'haikHeader': {
      caption: '見出し',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm',
      func: 'cpInsert',
      value: '\n* 見出し１\n'
    },
    'haikLink': {
      caption: '<i class="glyphicon glyphicon-link"></i><span class="sr-only">リンク</span>',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm',
      func: 'cpDialog',
      value: [
        [
          {msg:"リンク名を入力してください。",option:{
            type:'text',
            useSelection:true
          }},
          {msg:"リンク先（ページ名, URL）を入力してください。",option:{
            type:'text'
          }}
        ],
        '[[${1}', '>${2}]]']
    },
		'haikBr': {
			caption: '改行',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm',
			func: 'cpInsert',
			value: '&br;'
		},
		'haikB': {
			caption: '<span style="font-weight:bold">太字</span>',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm',
			func: 'cpEnclose',
			value: ['\'\'', '\'\'']
		},
		'haikHandline': {
			caption: '<span style="background-color: yellow">強調</span>',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm',
			func: 'cpEnclose',
			value: ['##', '##']
		},
		'haikDecop': {//deco ダイアログ
			caption: '装飾',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm',
			func: 'cpDialog',
			value: [
				[
					{msg:"<b>太字</b>",option:{
						type:'checkbox',
						value:'b,',
						css:{float:"left", width:"30%"}
					}},
					{msg:"<i>斜体</i>",option:{
						type:'checkbox',
						value:'i,',
						css:{float:"left", width:"30%"}
					}},
					{msg:"<u>下線</u>",option:{
						type:'checkbox',
						value:'u,',
						css:{float:"left", width:"auto", marginRight:10}
					}},
					{msg:'<span style="color:#2C48BF">文字色</span>を入力してください',option:{
						type: 'text',
						inputWidthRatio: 0.3,
						css:{clear:"both"}
					}},
					{msg:'<span style="background-color:#2C48BF;color:#fff">背景色</span>を入力してください',option:{
						type: 'text',
						inputWidthRatio: 0.3
					}},
					{msg:'文字サイズ<span style="font-size:11px">（数値、単位付き指定（ex:2em）、small、largeなど）</span>',option:{
						type: 'text',
						inputWidthRatio: 0.3
					}}
				],
				'&deco(${1}${2}${3}${4},${5},${6}){', '};']
		},
    'haikUl': {
      caption: '<i class="glyphicon glyphicon-list"></i><span class="sr-only">箇条書き</span>',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-left',
      func: 'cpInsert',
      value: '\n- 箇条書き1\n-- 箇条書き2\n--- 箇条書き3\n- 箇条書き1\n'
    },
    'haikHr': {
      caption: '水平線',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm',
      func: 'cpInsert',
      value: '\n----\n'
    },
		'haikAttach': {
			caption: '<i class="glyphicon glyphicon-picture"></i><span class="sr-only">添付</span>',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-left qhm-btn-separate-right',
			func: 'cpInsert',
			value: '&show(,,画像の説明);'
		},
    'haikOl': {
      caption: '番号付き箇条書き',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm',
      func: 'cpInsert',
      value: '\n+ 箇条書き1\n+ 箇条書き2\n+ 箇条書き3\n'
    },
		'haikLeft': {
			caption: '<i class="glyphicon glyphicon-align-left"></i><span class="sr-only">左揃え</span>',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-right',
			func: 'cpInsert',
			value: 'LEFT:'
		},
		'haikCenter': {
			caption: '<i class="glyphicon glyphicon-align-center"></i><span class="sr-only">中央揃え</span>',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm',
			func: 'cpInsert',
			value: 'CENTER:'
		},
		'haikRight': {
			caption: '<i class="glyphicon glyphicon-align-right"></i><span class="sr-only">右揃え</span>',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-left',
			func: 'cpInsert',
			value: 'RIGHT:'
		},
    'haikColors': {
      caption: '<i class="glyphicon glyphicon-th"></i> 色',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-right',
      func: 'cpDialog',
      value: [function(){
        if (typeof jQuery.clickpad.haikColors === "undefined") {
          var date = new Date();
          if (typeof localStorage.qhmHaikColors === "undefined" || (typeof localStorage.qhmHaikColorsUpdated !== "undefined" && localStorage.qhmHaikColorsUpdated != date.getMonth() + "/" + date.getDate())) {
            jQuery.clickpad.haikColors = $.ajax({
              url: "plugin/skin_customizer/colors.json",
              async: false,
              dataType: "json"
            }).responseJSON;
            localStorage.qhmHaikColors = JSON.stringify(jQuery.clickpad.haikColors);
            localStorage.qhmHaikColorsUpdated = date.getMonth() + "/" + date.getDate();
          }
          else {
            jQuery.clickpad.haikColors = JSON.parse(localStorage.qhmHaikColors);
          }
        }

        var colorSets = jQuery.clickpad.haikColors;
        var content = '<div>';
        for (var i in colorSets) {
          var colorSet = colorSets[i];
          content += '<div style="text-align:center;">';
          for (var j in colorSet.color) {
            var color = colorSet.color[j];
            content += '<button type="button" class="qhm-btn qhm-btn-default qhm-haik-color-btn" style="background-color: #'+color+';border-color:#'+darken(color)+';margin-right:2px;margin-bottom: 2px;" onclick="$(\'#cpPromptHaikColor\').val(this.dataset.color);$(\'#cp_popup_ok\').click()" data-color="#'+color+'">&nbsp;&nbsp;</button>';
          }
          content += '</div>';
        }
        content += '</div>';
        content += '<input type="hidden" name="color_0" id="cpPromptHaikColor" class="cp_popup_prompt">';
        return content;
      },
      function(sel_length){
        var value = '${1}';
        if (sel_length > 0) {
          value = '&deco('+ value + '){';
        }
        return value;
      },
      function(sel_length){
        var value = '';
        if (sel_length > 0) {
          value = '};';
        }
        return value;
      }]
    },
    'haikParts': {
      caption: '<i class="glyphicon glyphicon-cog"></i> パーツ',
      classAttribute: 'qhm-btn qhm-btn-default qhm-btn-sm qhm-btn-separate-left',
      func: 'cpEval',
      value: 'showHaikParts()'
    },

		// !mobile buttons
		'tel': {
			caption: '電話番号',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -954px 0',
			func: 'cpDialog',
			value: ["電話番号を入力してください　（※ 「-（ハイフン）」は省いてください）", '&tel(${1}', ');']
		},
		'mailto': {
			caption: 'メールアドレス',
			width: 28,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -982px 0',
			func: 'cpDialog',
			value: [['メールアドレスを入力してください', '件名を入力してください', '本文を入力してください', 'メールアドレスなど画面に表示する文字を入力してください'], "&mailto(${1},${2},${3}){${4}", "};"]
		},
		'marquee': {
			caption: 'マーキー',
			width: 47,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -1010px 0',
			func: 'cpEnclose',
			value: ['&scroll(){', '};']
		},
		'marquee2': {
			caption: 'マーキー（背景）',
			width: 47,
			height: 28,
			background: 'url(image/hokuken/toolbox2.png) no-repeat -1057px 0',
			func: 'cpDialog',
			value: [['背景色を入力してください(ex. black yellow pink blue)', 'スクロールスピードを入力してください（slow,normal,fast）\n（省略した場合は標準スピード（normal）に設定します。）'], '&scroll(${1},${2}){', '};']
		},

		// !mobile ruled lines buttons
		'lineLT': {
			caption: '┏',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat 0 0',
			func: 'cpInsert',
			value: '┏'
		},
		'lineCT': {
			caption: '┳',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat -13px 0',
			func: 'cpInsert',
			value: '┳'
		},
		'lineRT': {
			caption: '┓',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat -26px 0',
			func: 'cpInsert',
			value: '┓'
		},
		'lineLM': {
			caption: '┣',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat 0 -13px',
			func: 'cpInsert',
			value: '┣'
		},
		'lineCM': {
			caption: '╋',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat -13px -13px',
			func: 'cpInsert',
			value: '╋'
		},
		'lineRM': {
			caption: '┫',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat -26px -13px',
			func: 'cpInsert',
			value: '┫'
		},
		'lineLB': {
			caption: '┗',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat 0 -26px',
			func: 'cpInsert',
			value: '┗'
		},
		'lineCB': {
			caption: '┻',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat -13px -26px',
			func: 'cpInsert',
			value: '┻'
		},
		'lineRB': {
			caption: '┛',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat -26px -26px',
			func: 'cpInsert',
			value: '┛'
		},
		'lineH': {
			caption: '━',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat -39px 0',
			func: 'cpInsert',
			value: '━'
		},
		'lineV': {
			caption: '┃',
			width: 13,
			height: 13,
			background: 'url(img/btn_lines.png) no-repeat -39px -13px',
			func: 'cpInsert',
			value: '┃'
		},

		// !commu, qnews 用、特殊タグ挿入ボタン
		'lastname': {
			caption: '姓',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat 0 0',
			func: 'cpInsert',
			value: '<%lastname%>'
		},
		'firstname': {
			caption: '名',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -28px 0',
			func: 'cpInsert',
			value: '<%firstname%>'
		},
		'email': {
			caption: 'メールアドレス',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -56px 0',
			func: 'cpInsert',
			value: '<%email%>'
		},
		'encLastname': {
			caption: '姓（URLエンコード）',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -84px 0'	,
			func: 'cpInsert',
			value: '<%enc_lastname%>'
		},
		'encFirstname': {
			caption: '名（URLエンコード）',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -112px 0',
			func: 'cpInsert',
			value: '<%enc_firstname%>'
		},
		'encEmail': {
			caption: 'メールアドレス（URLエンコード）',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -140px 0',
			func: 'cpInsert',
			value: '<%enc_email%>'
		},
		'zip': {
			caption: '郵便番号',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -168px 0',
			func: 'cpInsert',
			value: '<%zip%>'
		},
		'state': {
			caption: '都道府県',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -196px 0',
			func: 'cpInsert',
			value: '<%state%>'
		},
		'address': {
			caption: '住所',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -224px 0',
			func: 'cpInsert',
			value: '<%address1%>'
		},
		'telnum': {
			caption: '電話番号',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -252px 0',
			func: 'cpInsert',
			value: '<%tel%>'
		},
		'job': {
			caption: '職業',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -280px 0',
			func: 'cpInsert',
			value: '<%job%>'
		},
		'birthday': {
			caption: '生年月日',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -308px 0',
			func: 'cpInsert',
			value: '<%birthday%>'
		},
		'custom1': {
			caption: 'カスタム1',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -336px 0',
			func: 'cpInsert',
			value: '<%custom1%>'
		},
		'custom2': {
			caption: 'カスタム2',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -364px 0',
			func: 'cpInsert',
			value: '<%custom2%>'
		},
		'custom3': {
			caption: 'カスタム3',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -392px 0',
			func: 'cpInsert',
			value: '<%custom3%>'
		},
		'custom4': {
			caption: 'カスタム4',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -420px 0',
			func: 'cpInsert',
			value: '<%custom4%>'
		},
		'custom5': {
			caption: 'カスタム5',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -448px 0',
			func: 'cpInsert',
			value: '<%custom5%>'
		},
		'regist': {
			caption: '本登録URL',
			width: 47,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -476px 0',
			func: 'cpInsert',
			value: '<%regist_url%>'
		},
		'quit': {
			caption: '退会URL',
			width: 47,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -523px 0',
			func: 'cpInsert',
			value: '<%quit%>'
		},
		'cancel': {
			caption: '解除URL',
			width: 47,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -570px 0',
			func: 'cpInsert',
			value: '<%cancel%>'
		},
		'cancelAll': {
			caption: '一発解除URL',
			width: 47,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -617px 0',
			func: 'cpInsert',
			value: '<%cancel%>'
		},
		'userInfo': {
			caption: 'ユーザー情報URL',
			width: 47,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -664px 0',
			func: 'cpInsert',
			value: '<%info%>'
		},
		'scenario': {
			caption: 'シナリオタイトル',
			width: 47,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -711px 0',
			func: 'cpInsert',
			value: '<%title%>'
		},
		'password': {
			caption: 'パスワード',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -758px 0',
			func: 'cpInsert',
			value: '<%password%>'
		},
		'expiration': {
			caption: '有効期間',
			width: 28,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -786px 0',
			func: 'cpInsert',
			value: '<%expiration%>'
		},
		'privacypolicy': {
			caption: 'プライバシーポリシーURL',
			width: 62,
			height: 28,
			background: 'url(img/commu_icons.png) no-repeat -814px 0',
			func: 'cpInsert',
			value: '<%privacypolicy%>'
		},

		// !forum 用、特殊タグ挿入ボタン
		'nickname': {
			caption: 'ニックネーム',
			width: 47,
			height: 28,
			background: 'url(img/forum_icons.png) no-repeat 0 0',
			func: 'cpInsert',
			value: '%nickname%'
		},
		'forumtitle': {
			caption: 'フォーラムタイトル',
			width: 47,
			height: 28,
			background: 'url(img/forum_icons.png) no-repeat -47px 0',
			func: 'cpInsert',
			value: '%title%'
		},
		'posturl': {
			caption: '質問表示URL',
			width: 47,
			height: 28,
			background: 'url(img/forum_icons.png) no-repeat -94px 0',
			func: 'cpInsert',
			value: '%url%'
		},
		'postediturl': {
			caption: '質問編集URL',
			width: 47,
			height: 28,
			background: 'url(img/forum_icons.png) no-repeat -141px 0',
			func: 'cpInsert',
			value: '%url_edit%'
		},
		'resbody': {
			caption: '返信の内容（全文）',
			width: 47,
			height: 28,
			background: 'url(img/forum_icons.png) no-repeat -188px 0',
			func: 'cpInsert',
			value: '%body%'
		},

		// !ThickBox が必要なボタン
		//replaces オプションの指定必須
		'fwd': {//replaces:{htmlmail:<boolean> isHTMLMail, relatedId:<string> relatedMailId}
			caption: 'クリック計測URL',
			width: 117,
			height: 33,
			background: ['url(img/btn_fwd_maneger_117x33.gif) no-repeat 0 0', 'url(img/btn_fwd_maneger_117x33.gif) no-repeat 0 -33px'],
			func: 'cpEval',
			value: "tb_show('ForwardingURLManager', 'fwd_manager.php?taid=${textarea}&h=${htmlmail}${relatedId}${query}&TB_iframe=true&height=480&width=640');",
			replaces: ['htmlmail', 'relatedId', 'query']
		},
		'ot': {//replaces:{htmlmail:<boolea> isHTMLMail}
			caption: 'ワンタイムオファー',
			width: 117,
			height: 33,
			background: ['url(img/btn_ot_maneger_117x33.gif) no-repeat 0 0', 'url(img/btn_ot_maneger_117x33.gif) no-repeat 0 -33px'],
			func: 'cpEval',
			value: "tb_show('OneTimeOfferManager', 'ot_manager.php?taid=${textarea}&h=${htmlmail}${query}&TB_iframe=true&height=480&width=640');",
			replaces: ['htmlmail', 'query']
		},
		'image': {
			caption: 'イメージマネージャー',
			width: 117,
			height: 33,
			background: ['url(img/btn_image_manager_117x33.gif) no-repeat 0 0', 'url(img/btn_image_manager_117x33.gif) no-repeat 0 -33px'],
			func: 'cpEval',
			value: "tb_show('ImageManager', 'image_manager.php?taid=${textarea}${query}&TB_iframe=true&height=480&width=640');",
			replaces: ['query']
		},
		'forumImage': {
			caption: 'イメージマネージャー',
			width: 117,
			height: 33,
			background: ['url(../commu/img/btn_image_manager_117x33.gif) no-repeat 0 0', 'url(../commu/img/btn_image_manager_117x33.gif) no-repeat 0 -33px'],
			func: 'cpEval',
			value: "tb_show('ImageManager', '../commu/image_manager.php?taid=${textarea}${query}&&TB_iframe=true&height=480&width=640');",
			replaces: ['query']
		},
		'preview': {//replaces:{prvFunc:<string> javascriptFunctionName}
			caption: 'プレビュー',
			width: 117,
			height: 33,
			background: ['url(img/btn_preview_117x33.gif) no-repeat 0 0', 'url(img/btn_preview_117x33.gif) no-repeat 0 -33px'],
			func: 'cpEval',
			value: "${prvFunc}",
			replaces: ['prvFunc']
		},
		'forumPreview': {//replaces:{prvFunc:<string> javascriptFunctionName}
			caption: 'プレビュー',
			width: 117,
			height: 33,
			background: ['url(../commu/img/btn_preview_117x33.gif) no-repeat 0 0', 'url(../commu/img/btn_preview_117x33.gif) no-repeat 0 -33px'],
			func: 'cpEval',
			value: "${prvFunc}",
			replaces: ['prvFunc']
		},

		//特殊な用途に使うもの
		'-': {

		}

	};

	// !color palette init
	//デフォルトカラーパレット
	jQuery.clickpad.colors = [
		'black', 'gray', 'silver', 'lightgray','white',
		'navy', 'blue', 'cyan', 'green', 'lime', 'lightgreen',
		'purple', 'magenta', 'pink', 'red', 'orange', 'yellow'
	];
	for (var i = 0; i < jQuery.clickpad.colors.length; i++) {
		var color = jQuery.clickpad.colors[i];
		addButton(color, {
			caption: color,
			width: 13,
			height: 13,
			background: color,
			func: 'cpEnclose',
			value: ["&deco("+color+"){", "};"]
		});
	}


	// !button palette presets init
	jQuery.clickpad.palettes = {

		//default color palette
		color : [
			['black', 'gray', 'silver', 'lightgray','white', 'white'],
			['navy', 'blue', 'cyan', 'green', 'lime', 'lightgreen'],
			['purple', 'magenta', 'pink', 'red', 'orange', 'yellow']
		],

		//QHM toolbox
		qhm : [
			['header', 'contents', 'ul', 'ol', 'attach', 'br', 'link', 'b', 'i', 'handline', 'size', 'penYellow', 'penRed', 'penBlue', 'penGreen', 'decop'],
			['left', 'center', 'right', 'table', 'HTML', 'styleboxp', 'title', 'onepage', 'bullet', 'check', 'strike', 'whiteRed', 'whiteBlack', 'counter', 'comment']
		],

		qhmHaik : [
			['haikHeader', 'haikB', 'haikHandline', 'haikDecop', 'haikBr', 'haikHr', 'haikLink', 'haikUl', 'haikLeft', 'haikCenter', 'haikRight', 'haikAttach', 'haikColors', 'haikParts']
		],

		//QBlog
		qblog : [
			['header', 'ul', 'ol', 'attach', 'br', 'link', 'b', 'i', 'handline', 'penYellow', 'penRed', 'decop'],
			['left', 'center', 'right', 'table', 'styleboxp', 'check', 'strike', 'whiteRed', 'counter', 'plugins']
		],
		qblogcolors : [
			['black', 'gray', 'silver', 'lightgray','white', 'white', 'navy', 'blue', 'cyan'],
			['green', 'lime', 'lightgreen', 'purple', 'magenta', 'pink', 'red', 'orange', 'yellow']
		],
		qhmHaikQBlog : [
			['haikHeader', 'haikB', 'haikHandline', 'haikDecop', 'haikBr', 'haikHr', 'haikLink', 'haikUl', 'haikLeft', 'haikCenter', 'haikRight', 'haikAttach'],
			['haikColors', 'haikParts']
		],

		//QHM minimize for commu and qnews
		qhmmin : [
			['header', 'ul', 'ol', 'br', 'link', 'b', 'u', 'sizeD', 'penYellowD', 'penRedD', 'penBlueD', 'penGreenD', 'decop'],
			['left', 'center', 'right', 'table', 'HTML', 'styleboxp', 'strike', 'whiteRedD', 'whiteBlackD']
		],
		qhmminL : [
			['header', 'ul', 'ol', 'br', 'link', 'b', 'u', 'sizeD', 'penYellowD', 'penRedD', 'penBlueD', 'penGreenD', 'decop',
			 'left', 'center', 'right', 'table', 'HTML', 'styleboxp', 'strike', 'whiteRedD', 'whiteBlackD']
		],

		//mobile toolbox
		mobile : [
			['link', 'tel', 'mailto', 'attach', 'br', 'marquee', 'marquee2'],
			['left', 'center', 'right', 'HTML', 'counter', 'comment']
		],

		//ruled lines
		lines : [
			['lineLT', 'lineCT', 'lineRT', 'lineH'],
			['lineLM', 'lineCM', 'lineRM', 'lineV'],
			['lineLB', 'lineCB', 'lineRB']
		],

		//commu
		commu : [
			['lastname', 'firstname', 'email', 'encLastname', 'encFirstname', 'encEmail', 'privacypolicy']
		],
		forum:  [
			['nickname', 'forumtitle', 'posturl', 'postediturl', 'resbody']
		],

		//qnews
		qnews :[
			['lastname', 'firstname', 'email', 'encLastname', 'encFirstname', 'encEmail']
		],

		//thickbox
		commuTB : [
			['image', 'preview']
		],
		qnewsTB : [
			['image', 'fwd', 'ot', 'preview']
		],
		qnewsTextTB : [
			['fwd', 'ot', 'preview']
		]
	};

	// !button set init
	jQuery.clickpad.buttonSetData = {
		'qhm': [
			{//toolbox
				buttons: jQuery.clickpad.palettes.qhm,
				margin: 1,
				css: {}
			},
			{//color palette
				buttons: jQuery.clickpad.palettes.color,
				margin: 0,
				css: {float:"left", marginBottom: 1, marginRight: 3}
			},
			{//other plugins
				buttons: [['plugins']],
				margin: 0,
				css: {float:"left", marginBottom: 13},
				clear: true
			}
		],
		'qhmHaik': [
			{//toolbox
				buttons: jQuery.clickpad.palettes.qhmHaik,
				margin: 1,
				css: {},
				clear: true
			}
		],
		'qhmHaikQBlog': [
			{//toolbox
				buttons: jQuery.clickpad.palettes.qhmHaikQBlog,
				margin: 1,
				css: {},
				clear: true
			}
		],
		'qblog': [
			{//toolbox
				buttons: jQuery.clickpad.palettes.qblog,
				margin: 1,
				css: {float:"left"},
				clear: false
			},
			{//color palette
				buttons: jQuery.clickpad.palettes.color,
				margin: 0,
				css: {float:"left", marginBottom: 1, marginLeft: 3},
				clear: false
			}
		],
		'mobile': [//絵文字のためのケアが必要
			{//toolbox
				buttons: jQuery.clickpad.palettes.mobile,
				margin: 1,
				css: {}
			},
			{//ruled lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {float:"left", marginRight: 3}
			},
			{//color palette
				buttons: jQuery.clickpad.palettes.color,
				margin: 0,
				css: {float:"left", marginBottom: 1},
				clear:true
			}
		],
		'qnewsText': [
			{//special tag
				buttons: jQuery.clickpad.palettes.qnews,
				margin: 1,
				css: {float: "left"}
			},
			{//lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {float: "left", marginLeft: 5},
				clear: true
			},
			{//thick box
				buttons: jQuery.clickpad.palettes.qnewsTextTB,
				margin: [5, 0],
				css: {
					margin: "10px 0"
				}
			}
		],
		'qnewsTextLinear': [
			{//special tag
				buttons: jQuery.clickpad.palettes.qnews,
				margin: 1,
				css: {float: "left"}
			},
			{//lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {float: "left", marginLeft: 5}
			},
			{//thick box
				buttons: jQuery.clickpad.palettes.qnewsTextTB,
				margin: [10, 0],
				css: {
					float: "left",
					marginLeft: 5
				}
			}
		],
		'qnewsHTML':[
			{//special tag
				buttons: jQuery.clickpad.palettes.qnews,
				margin: 1
			},
			{//QHM
				buttons: jQuery.clickpad.palettes.qhmmin,
				margin: 1
			},
			{//color palette
				buttons: jQuery.clickpad.palettes.color,
				margin: 0,
				css: {float:"left", marginBottom: 1},
				clear:true
			},
			{//thick box
				buttons: jQuery.clickpad.palettes.qnewsTB,
				margin: [5, 0],
				css: {
					margin: "10px 0"
				}
			}
		],
		'qnewsHTMLLinear':[
			{//special tag
				buttons: jQuery.clickpad.palettes.qnews,
				margin: 1
			},
			{//QHM
				buttons: jQuery.clickpad.palettes.qhmminL,
				margin: 1,
				css: {float:"left"},
				clear: true
			},
			{//color palette
				buttons: jQuery.clickpad.palettes.color,
				margin: 0,
				css: {float:"left"}
			},
			{//thick box
				buttons: jQuery.clickpad.palettes.qnewsTB,
				margin: [5, 0],
				css: {
					float: "left",
					margin: "5px 0 5px 10px"
				}
			}
		],
		'qnewsSubject':[//メールの件名で使う
			{
				buttons: [['lastname', 'firstname', 'email']],
				margin: [1, 0]
			}
		],
		'commuHTML': [
			{//QHM
				buttons: jQuery.clickpad.palettes.qhmmin,
				margin: 1
			},
			{//thick box
				buttons: [['image','preview']],
				margin: [5, 1],
				css: {
					margin: "5px 0 0"
				}
			}
		],
		'commuAdmin': [
			{//toolbox
				buttons: jQuery.clickpad.palettes.qhmmin,
				margin: 1
			},
			{//toolbox
				buttons: [
					['lastname', 'firstname', 'email']
				],
				margin: 1,
				css: {}
			},
			{//thick box
				buttons: [['image','preview']],
				margin: [5, 1],
				css: {
					margin: "5px 0"
				}
			}
		],
		'commuAdminRegist': [
			{//toolbox
				buttons: jQuery.clickpad.palettes.qhmmin,
				margin: 1
			},
			{//toolbox
				buttons: [
					['lastname', 'firstname', 'email','password']
				],
				margin: 1,
				css: {}
			},
			{//thick box
				buttons: [['image','preview']],
				margin: [5, 1],
				css: {
					margin: "5px 0"
				}
			}
		],
		'commuMobile': [//絵文字のためのケアが必要
			{//toolbox
				buttons: [
					['link', 'tel', 'mailto', 'br', 'sizeM', 'decop', 'marquee', 'marquee2','left', 'center', 'right', 'HTML']
				],
				margin: 1
			},
			{//ruled lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {float:"left", marginRight: 3}
			},
			{//color palette
				buttons: jQuery.clickpad.palettes.color,
				margin: 0,
				css: {float:"left", marginBottom: 1},
				clear:true
			}
		],
		'commuMobileUser': [//絵文字のためのケアが必要
			{//toolbox
				buttons: [
					['link', 'tel', 'mailto', 'br', 'sizeM', 'decop', 'marquee', 'marquee2','left', 'center', 'right', 'HTML']
				],
				margin: 1
			},
			{//commutoolbox
				buttons: [
					['lastname', 'firstname', 'email']
				],
				margin: 1
			},
			{//ruled lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {float:"left", marginRight: 3}
			},
			{//color palette
				buttons: jQuery.clickpad.palettes.color,
				margin: 0,
				css: {float:"left", marginBottom: 1},
				clear:true
			}
		],
		'commuMobileUserRegist': [//絵文字のためのケアが必要
			{//toolbox
				buttons: [
					['link', 'tel', 'mailto', 'br', 'sizeM', 'decop', 'marquee', 'marquee2','left', 'center', 'right', 'HTML']
				],
				margin: 1
			},
			{//commutoolbox
				buttons: [
					['lastname', 'firstname', 'email','password']
				],
				margin: 1
			},
			{//ruled lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {float:"left", marginRight: 3}
			},
			{//color palette
				buttons: jQuery.clickpad.palettes.color,
				margin: 0,
				css: {float:"left", marginBottom: 1},
				clear:true
			}
		],
		'commuSubject': [
			{//toolbox
				buttons: [
					['lastname', 'firstname', 'email']
				],
				margin: 1,
				css: {}
			}
		],
		'commuMail':[
			{//toolbox
				buttons: jQuery.clickpad.palettes.commu,
				margin: 1,
				css: {float: "left"}
			},
			{//lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {float: "left", marginLeft: 5},
				clear: true
			},
		],
		'commuMailUser':[
			{//toolbox
				buttons: [
					['lastname', 'firstname', 'email', 'encLastname', 'encFirstname', 'encEmail', 'cancelAll', 'quit', 'userInfo', 'privacypolicy']
				],
				margin: 1,
				css: {float: "left"}
			},
			{//lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {float: "left", marginLeft: 5},
				clear: true
			}
		],
		'commuMailRegist':[
			{//toolbox
				buttons: [
					['lastname', 'firstname', 'email', 'password', 'encLastname', 'encFirstname', 'encEmail', 'cancelAll', 'quit', 'userInfo', 'privacypolicy']
				],
				margin: 1,
				css: {float: "left"}
			},
			{//lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {float: "left", marginLeft: 5},
				clear: true
			}
		],
		'forumSubject':[
			{//toolbox
				buttons: [
					['nickname', 'forumtitle']
				],
				margin: 1,
				css: {
					margin:"2px 0"
				}
			}
		],
		'forumMail':[
			{//toolbox
				buttons: jQuery.clickpad.palettes.forum,
				margin: 1,
				css: {
					float: "left",
					margin:"2px 0"
				}
			},
			{//lines
				buttons: jQuery.clickpad.palettes.lines,
				margin: 0,
				css: {
					float: "left",
					margin: "2px 2px"
				},
				clear: true
			}
		],
		'forumHTML':[
			{//QHM
				buttons: jQuery.clickpad.palettes.qhmmin,
				margin: 1
			},
			{//thick box
				buttons: [['forumImage','forumPreview']],
				margin: [5, 1],
				css: {margin: "5px 0"}
			}
		]
	};

	function addButton(name, data) {

		data = jQuery.extend({
			caption:    name,
			width:      13,
			height:     13,
			background: "#fff",
			classAttribute: "",
			func:       "cpEval",
			value:      ""
		}, data);

		jQuery.clickpad.buttonData[name] = data;
	}

	function getBrowser() {
		return jQuery.clickpad.browser;
	}

	/**
	 *   プロンプト（ダイアログ）を表示し、入力を促す
	 */
//	function cpPrompt(msgs, options, callback){
	var cpPrompt = jQuery.clickpad.cpPrompt = function(msgs, options, callback){
		//init
		var removeCpPrompt = function(){
			$("#cp_popup_overlay").remove();
			$("#cp_popup_container").remove();
		};
		removeCpPrompt();

		var content = '';
		//param init
		if (typeof msgs === "function") {
      content = msgs.call();
		}
		else {
      msgs = msgs instanceof Array? msgs : [msgs];
      options = options instanceof Array? options : [options];
      if (msgs.length > options.length) {
        for (var i = options.length; i < msgs.length; i++) {
          options[i] = {};
        }
      }
		}

		//overlay
		$("body").append('<div id="cp_popup_overlay"></div>');
		$("#cp_popup_overlay").css({
			position: 'absolute',
			zIndex: 99998,
			top: '0px',
			left: '0px',
			width: '100%',
			height: $(document).height(),
			backgroundColor: "rgba(255,255,255,.5)",
		})
		//If click overlay, cancel prompt
		.click(function(){
			removeCpPrompt();
			return false;
		});

		//popup block
		$("body").append(
		  '<div id="cp_popup_container">' +
		    '<div id="cp_popup_content"></div>' +
		  '</div>');

		// IE6 Fix
		var pos = "fixed", w = "auto";

		var $popup = $("#cp_popup_container").css({
			position: pos,
			zIndex: 99999,
			padding: "1em",
			margin: 0,
			fontSize: 12,
			width: w,
			minWidth: 300,
			maxWidth: 600,
			background: "#FFF",
			border: "solid 5px #999",
			color: "#000",
			"-moz-border-radius": 5,
			"-webkit-border-radius": 5,
			"border-radius": 5
		});

//		$("#popup_content").addClass(type);
		//message box を msgs 分追加する

    if (content.length === 0) {
      for (var i =0; i < msgs.length; i++) {
        content +=
          '<div class="cp_popup_message" style="line-height:1.5em;text-align:left">' +
          msgs[i].replace(/\n/g, '<br />') +
          '</div>';
      }
    }
		$("#cp_popup_content", $popup).html(content);

		$popup.css({
			minWidth: $popup.outerWidth(),
			maxWidth: $popup.outerWidth()
		});

		//position
		var top = (($(window).height() / 2) - ($popup.outerHeight() / 2)) + 0;
		var left = (($(window).width() / 2) - ($popup.outerWidth() / 2)) + 0;
		if( top < 0 ) top = 0;
		if( left < 0 ) left = 0;

		$popup.css({
			top: top + 'px',
			left: left + 'px'
		});
		$("#cp_popup_overlay").height( $(document).height() );

		//show prompt
		$msgs = $("div.cp_popup_message", $popup)
			.append('<br />')
			.css({
				marginBottom: "10px"
			});
		$("#cp_popup_content")
			.append('<div id="cp_popup_panel"><input type="button" value="OK" id="cp_popup_ok" class="qhm-btn-primary" /> <input type="button" value="Cancel" id="cp_popup_cancel" class="btn-link" /></div>');
		$("#cp_popup_panel").css({
			textAlign: "center",
			margin: "1em 0em 0em 1em"
		});
		$msgs.each(function(i){
			var opt = options[i];
			var $$ = $(this);

			switch (opt.type){
			case 'checkbox':
				var checkbox = '<input type="checkbox" value="'+opt.value+'" name="cpPopupPrompt_'+i+'" class="cp_popup_prompt" />' +
								'<input type="hidden" value="" name="cpPopupPrompt_'+i+'" class="cp_popup_prompt" />&nbsp;';
				$$.prepend(checkbox).html('<label>'+$$.html()+'</label>');
				break;
			case 'select':
				var select = '&nbsp;&nbsp;<select class="cp_popup_prompt" name="cpPopupPrompt_'+i+'">';
				for (var j in opt.values) {
					if ( ! $.isPlainObject(opt.values[j])) continue;
					var value = opt.values[j];
					if (typeof value == 'string') {
						value = {key: value, value: value, selected: false};
					} else if (typeof value.key == "undefined") {
						value.key = value.value;
					}
					select += '<option value="'+value.value+'"' + (value.selected? ' selected="selected"': '') + '>'+value.key+'</option>';
				}
				select += '</select>';
				$$.append(select).html('<label>'+$$.html()+'</label>');
				break;
			case 'radio':
				var rdname = 'cpPopupPrompt_' + i;
				var radio = '&nbsp;&nbsp;';
				for (var j in opt.values) {
					if ( ! $.isPlainObject(opt.values[j])) continue;
					var value = opt.values[j];
					if (typeof value == 'string') {
						value = {label: value, value: value, checked: false};
					} else if (typeof value.label == 'undefined') {
						value.label = value.value;
					}
					radio += '<label style="display:inline;padding:0;"><input type="radio" class="cp_popup_prompt" name="'+rdname+'" value="'+ value.value +'"'+ (value.checked? ' checked="checked"': '') +' />&nbsp;'+value.label+'</label>&nbsp;&nbsp;';
					if (value.br) {
						radio += '<br />&nbsp;&nbsp;';
					}
				}
				$$.append(radio);
				break;
			default://input:text
				var defval = opt.defval || '';
				var size = opt.size || 30;
				var inputWidth = opt.inputWidthRatio * $$.width() || $$.width() * 0.9;
				$$.append('&nbsp;&nbsp;<input type="text" size="'+size+'" class="cp_popup_prompt" name="cpPopupPrompt_'+i+'" value="'+ defval +'" />')
				.html('<label>'+$$.html()+'</label>')
					.find("input.cp_popup_prompt")
					.width(inputWidth)
					.val(opt.defval)
					.on('keydown', (e) => { e.stopPropagation() });
			}

			var css = opt.css || {};
			if (i < $msgs.length - 1) {
				css = jQuery.extend({
					marginBottom: 10
				}, css);
			}
//				$$.css({marginBottom: "10px"});

			$$.css(css);
		});

		$("#cp_popup_ok").click( function() {
			var values = [];
			var cbskip = false;
			$(".cp_popup_prompt", $popup).each(function(){
				var $$ = $(this);
				var i = parseInt($$.attr("name").split("_")[1]);

				//checkbox がチェックされていない場合、次のhidden を採用
				//チェックされてたら、次のhidden を飛ばす
				if ($$.attr("type") == "checkbox") {
					if ($$.is(":checked")) {
						cbskip = true;
						values[i] = $$.val();
					}
				}
				else if ($$.attr("type") == "radio") {
					if ($$.is(":checked")) {
						values[i] = $$.val();
					}
				}
				else {
					if (!cbskip) {
						values[i] = $$.val();
					} else {
						cbskip = false;
					}
				}

			});
			removeCpPrompt();
			if( callback ) callback( values );
		});
		$("#cp_popup_cancel").click( function() {
			removeCpPrompt();
		});
		$("input.cp_popup_prompt, #cp_popup_ok, #cp_popup_cancel", $popup).keypress( function(e) {
			if( e.keyCode == 13 ) {$("#cp_popup_ok").trigger('click');return false;}
			if( e.keyCode == 27 ) $("#cp_popup_cancel").trigger('click');
		});
		$("input.cp_popup_prompt:first", $popup).focus().select();

	}

	// !plugin start
    jQuery.fn.clickpad = function(option) {

		var name    = "clickpad"; //name space of this plugin

		option = jQuery.extend({
			buttons: 'qnews',
			autoGrow: true,
			minLine: 5,
			maxLine: 20,
			replaces: {},
			css: {},
			wrappercss: {},
			showAtFocus: false
		}, option);

		if (typeof option.buttons == 'string') {
			option.buttons = jQuery.clickpad.buttonSetData[option.buttons];
		}

		return this.each(function(i){
			//define params
			var sel_length = 0, end_length = 0, start_length = 0, start_length2 = 0;
			var scrollPos = 0;
			var eventObj = this;
			var $$ = jQuery(this);
			var total = ++jQuery.clickpad.total; //total of clickpad
			var isTextInput = false;

			if ($$.is("input:text")) {
				isTextInput = true;
			}

			//define functions
//			function cpEval(value, replaces) {
			var cpEval = jQuery.clickpad.cpEval = function (value, replaces) {
				for (var i in replaces) {
					var rep = replaces[i];
					//文字列の場合 option.replaces.key を参照
					if (typeof rep == 'string') {
						var str = option.replaces[rep] || '';
						value = value.replace('${'+rep+'}', str);
					}
					//オブジェクトの場合、key と value
					else if (typeof rep.key != "undefined"){

						value = value.replace('${' + rep.key + '}', rep.value);
					}
					//その他は無視
					else {}
				}
				value = value.replace('${textarea}', $$.attr("id"));
				//まだ置換されていないテンプレートがあれば空白に
				value = value.replace(/\$\{\w+?\}/, '');
				if (document.selection) {
					$$.focus();
				}
				eval(value);
			}

//			function cpInsert(value) {
			var cpInsert = jQuery.clickpad.cpInsert = function(value) {
//				if( !eventObj) return;
//				eventObj.focus();

				var browser = getBrowser(),
				s = value;

				if( browser == 2 ){
					scrollPos = eventObj.scrollTop;
				}

				var itext=eventObj.value;
				var slen = 0;

				if( browser == 4 ){
					eventObj.value = itext + s;
				} else if (browser == 1 && isTextInput){

					var r=eventObj.createTextRange();
					r.collapse();
					r.moveStart("character",eventObj.value.length-sel_length);
					r.text=value;
					return;

				} else if( browser ){
					var len = start_length2 == itext.length? start_length2: start_length;
					var click_s=itext.substr(0, len);
					var click_m=itext.substr(start_length, sel_length);
					var click_e=itext.substr(start_length+sel_length, end_length);
					if (click_s == '' && click_m == '' && click_e == '') {
						click_e = itext;
					}
					eventObj.value=click_s + s + click_m + click_e;

					// for IE　最後の改行挿入対応
					if ('v'=='\v') {
						var sarr = s.split('\n');
						if ((sarr.length - 1) > 0) {
							slen = sarr.length;
							slen = (sarr[slen - 1] == '') ? slen - 2 : slen - 1;
						}
					}
				}

				cpAttachFocus(s.length + slen + len + sel_length);

			}
			var cpEnclose = jQuery.clickpad.cpEnclose = function(values) {

//				if( !eventObj) return;

				var s = values[0],
					e = values[1],
					browser = getBrowser();

				if( browser == 2 ){
					scrollPos = eventObj.scrollTop;
				}

				var itext=eventObj.value;

				if( browser == 4 ){
					eventObj.value = itext + s + e;
				} else if( browser ){
					var len = start_length2 == itext.length? start_length2: start_length;
					var click_s=itext.substr(0, len);
					var click_m=itext.substr(len, sel_length);
					var click_e=itext.substr(len+sel_length, end_length);
					if (click_s == '' && click_m == '' && click_e == '') {
						click_e = itext;
					}
					eventObj.value=click_s + s + click_m + e + click_e;
				}

				cpAttachFocus(s.length + e.length + len + sel_length);

			}
//			function cpDialog(values) {
			var cpDialog = jQuery.clickpad.cpDialog = function(values) {
				var browser = getBrowser(),
					prompts = values[0],
					tmpl = values[1],
					closer = values[2] || '';

				if( browser == 2 ){
					scrollPos = eventObj.scrollTop;
				}

        if (typeof prompts == 'function') {
          var msgs = prompts;
          var options = [];
        }
        else {
  				if (typeof prompts == 'string') {
  					prompts = [prompts];
  				} else {
  					prompts = Array.apply(null, prompts);
  				}
  				var values = [];
  				var cnt = 0;

  				//prompts を msgs と options に分ける
  				var msgs = [], options = [];
  				for (var i = 0; i < prompts.length; i++) {
  					var promptmsg = prompts[i];
  					if (typeof promptmsg == 'string') {
  						msgs.push(promptmsg);
  						options.push({});
  					} else {
  						msgs.push(prompts[i].msg || "error: prompt message undefined");
  						options.push(prompts[i].option || {});
  					}
  				}

  				var useSelection = false;
  				//type:text かつ、useSelection が真の場合、選択したテキストを使う
  				for (var i = 0; i < options.length; i++)
  				{
  					if (typeof options[i] !== "string" &&
  						typeof options[i].type !== "undefined" && options[i].type === "text" &&
  						typeof options[i].useSelection !== "undefined" && options[i].useSelection)
  					{
  						options[i].defval = eventObj.value.substr(start_length, sel_length);
  						useSelection = true;
  					}
  				}
        }

        if (typeof tmpl == 'function') {
          tmpl = tmpl.call(this, sel_length);
        }

        if (typeof closer == 'function') {
          closer = closer.call(this, sel_length);
        }

				cpPrompt(msgs, options, function(values){
					//template を置換
					for (var i = 0; i < values.length; i++) {
						var cnt = i + 1;
						tmpl = tmpl.replace('${'+ cnt +'}', values[i]);
						closer = closer.replace('${'+ cnt +'}', values[i]);
					}

					var s = tmpl,
						e = closer;

					var slen = 0;
					var itext=eventObj.value;

					if( browser == 4 ){
						eventObj.value = itext + s + e;
					} else if( browser ){
						var click_s=itext.substr(0, start_length);
						var click_m=itext.substr(start_length, sel_length);
						var click_e=itext.substr(start_length+sel_length, end_length);
						if (click_s == '' && click_m == '' && click_e == '') {
							click_e = itext;
						}
						//useSelection が真の場合、選択文字列は一旦消す
						if (useSelection)
						{
							click_m = "";
							sel_length = 0;
						}
						eventObj.value=click_s + s + click_m + e + click_e;


						// for IE　最後の改行挿入対応
						if ('v'=='\v') {
							var sarr = s.split('\n');
							if ((sarr.length - 1) > 0) {
								slen = sarr.length;
								slen = (sarr[slen - 1] == '') ? slen - 2 : slen - 1;
							}
						}

					}
					cpAttachFocus(s.length + slen + e.length+start_length + sel_length);

				});

			}

//			function cpAttachFocus(ln){
			var cpAttachFocus = jQuery.clickpad.cpAttachFocus = function(ln){
				var browser = getBrowser();
				if( browser == 1 ){
					var e  = eventObj.createTextRange();
					var tx = eventObj.value.substr(0, ln);
					var pl = tx.split(/\n/);
					e.collapse(true);
					e.moveStart("character",ln-pl.length+1);
					e.text=e.text+"";
					e.collapse(false);
					e.select();
					eventObj.focus();
				} else if( browser == 2 ){
					eventObj.setSelectionRange(ln, ln);
					eventObj.focus();
					eventObj.scrollTop = scrollPos;
				} else if( browser == 3 ){

				}

			}


//			function cpGetPos() {
			var cpGetPos = jQuery.clickpad.cpGetPos = function() {
				var d = eventObj;
//				if( d ) eventObj = d;
				var ret = 0,
					browser = getBrowser();

				if( browser == 1 ) {
					if (isTextInput) {
						var r=document.selection.createRange();
						r.moveEnd("textedit");
						sel_length=r.text.length;
						return;
					}


					var sel=document.selection.createRange();
					sel_length = sel.text.length;
					var r=d.createTextRange();

					var all=r.text.length;
					var all2=d.value.length;
					var ol = sel.offsetLeft, ot = sel.offsetTop;
					//r.moveToPoint(sel.offsetLeft,sel.offsetTop);
					try {
						r.moveToPoint(ol, ot);
					} catch(e) {
						r.move('textedit');
					}
					r.moveEnd("textedit");

					end_length=r.text.length;
					start_length=all -end_length;
					start_length2=all2 -end_length;


				} else if( browser==2 ) {
					start_length=d.selectionStart;
					end_length=d.value.length - d.selectionEnd;
					sel_length=d.selectionEnd-start_length;
				} else if( browser==3 ){
					var ln=new String(d.value);
					start_length=ln.length;
					end_length=start_length;
					sel_length=0;
				}
			}

			// !plugin main logic
			var $doc = $(document);
			if ( typeof $doc.data("buttonTotal."+name) == 'undefined') {
				$doc.data("buttonTotal."+name, 0);
			}

			//button initialize
			var toolbox = '<div id="cpWrapper_'+ total +'" class="cpWrapper">',
				hoverables = [];//背景ホバー指定のID リスト
			for (var setIdx in option.buttons) {
				var btnSetData = option.buttons[setIdx],
					btnSetCss = btnSetData.css || {},
					btnSet = btnSetData.buttons,
					btnMargin = btnSetData.margin || [0,0],
					bs_total = ++jQuery.clickpad.bs_total,
					bs_html = '',
					backgroundImage = btnSetData.backgroundImage || false,
					clear = btnSetData.clear || false;

				//マージン指定を配列に
				if (typeof btnMargin == 'number') {
					btnMargin = [btnMargin, btnMargin];
				}

				bs_html += '<div><div id="cpButtonSet_'+ bs_total +'" class="cpButtonSet">';//open div.cpButtonSet

				for (var lineIdx in btnSet) {
					var btnLine = btnSet[lineIdx];
					for (var btnIdx in btnLine) {
						try {
              var total = ++jQuery.clickpad.b_total,
                btnName = btnLine[btnIdx],
                button = jQuery.clickpad.buttonData[btnName],
                id = id = 'cpButton_' + btnName + '_' + total;

              if (typeof button.classAttribute !== "undefined" && button.classAttribute.length > 0) {
                var title = button.caption.replace(/<.*?>/g, "");
                bs_html += '<button type="button" id="'+id+'" class="cpButton '+button.classAttribute+'" title="'+title+'">'+button.caption+'</button>';
              }
              else {
                var bg;
                if (typeof button.background == 'string') {
                  bg = button.background;
                }
                else {
                  bg = button.background[0];
                  hoverables.push({id:id, backgrounds: button.background});
                }
                bs_html += '<div id="'+id+'" class="cpButton" title="'+button.caption+'" style="width:'+button.width+'px;height:'+button.height+'px;background:'+ bg +';float:left;margin:0;margin-right:'+btnMargin[0]+'px;margin-bottom:'+btnMargin[1]+'px;padding:0;line-height:'+button.height+'px;"></div>';
              }
            }
						catch (e) {
							alert(btnLine[btnIdx] + ' is undefined');
						}

					}

					bs_html += '<div style="clear:both"></div>\n';
				}

				bs_html += '</div></div>';//close div.cpButtonSet

				toolbox += $(bs_html)
					.children("div.cpButtonSet").css(btnSetCss)
					.each(function(){
						if (backgroundImage) {
							$(this)
								.find(".cpButton").css({
									backgroundImage: 'url('+backgroundImage+')'
								});
						}
					})
				.end().html();

				if (clear) {
					toolbox += '<div style="clear:both;"></div>\n';
				}

			}
			toolbox += '</div>';
			//set click event
			var $toolbox = $(toolbox).css(option.css)
//				.children("div.cpContainer").css(option.css)
				.find(".cpButton")
					.each(function(){
						var $div = $(this),
							btnName = $div.attr("id").split("_")[1];
						var btnData = jQuery.clickpad.buttonData[btnName];

						$div
							.click(function(){
								$$.data("continue", true);
								if (typeof btnData.replaces != 'undefined') {
									eval(btnData.func + '(btnData.value, btnData.replaces)');
								} else {
									eval(btnData.func + '(btnData.value)');
								}
								setTimeout(function(){$$.data("continue", false);}, 500);
								return false;
							});
					})
					.css({
						cursor: "pointer"
					})
				.end();

			// !remove existing clickpad
			if ($$.next("div.cpWrapper").length) {
				$$.next("div.cpWrapper").remove();
				$$.unbind(".clickpad");
			}

			$$
				//set DOM
/* 				.closest("div").after($toolbox).end() */
			.after($toolbox)
				//set event
				.bind("focus.clickpad", function(){cpGetPos(this)})
				.bind("mouseup.clickpad", function(){cpGetPos(this)})
				.bind("mouseup.clickpad", function(){cpGetPos(this)})
				.bind("keyup.clickpad", function(){cpGetPos(this)})
				.bind("keydown.clickpad", function(){cpGetPos(this)});


			//hover setting
			for (var i in hoverables) {
				var hoverId = hoverables[i].id,
					backgrounds = hoverables[i].backgrounds;

				$("#"+hoverId)
				.data("mouseenter.clickpad", backgrounds[1] + '')
				.data("mouseleave.clickpad", backgrounds[0] + '')
				.hover(
					function(){$(this).css({background: $(this).data("mouseenter.clickpad")})},
					function(){$(this).css({background: $(this).data("mouseleave.clickpad")})}
				);
			}

			// !Auto Grow
			if (option.autoGrow) {
				$$
				.bind("fit.clickpad", function(ev){
					var id = $$.attr("id");
					if (ev == null) {
						var textarea = $('#'+id).get(0);
					} else {
						var textarea = ev.target || ev.srcElement;
					}
					var value = textarea.value;
					var lines = value.split('\n');
					var lineNum = lines.length + 1;
					var cols = textarea.getAttributeNode("cols") ? textarea.getAttributeNode("cols").nodeValue : 60;
					for (var i in lines) {
						var line = lines[i];
						if (line.length > cols)
							lineNum += Math.ceil(line.length / cols) - 1;
					}
					if (option.minLine >= 0 && lineNum < option.minLine) {
						lineNum = option.minLine;
					} else if (option.maxLine > 0 && lineNum > option.maxLine) {
						lineNum = option.maxLine;
					}
					textarea.setAttribute("rows", lineNum);
				})
				.bind("keydown.clickpad", function(){$$.trigger("fit.clickpad")})
				.bind("focus.clickpad", function(){$$.trigger("fit.clickpad")})
				.trigger("fit.clickpad");
			}

			// !Show at Focus
			if (option.showAtFocus) {
				$$
					.bind("focus.clickpad", function(){if ($toolbox.is(":not(:visible)")) {$toolbox.fadeIn()}})
					.bind("blur.clickpad", function(){
						setTimeout(function(){
							if (!$$.data("continue")) {$toolbox.fadeOut();};
						},500);
					});
				$toolbox.hide();
			}

		});
	};
})(jQuery);
