<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: convert_html.php,v 1.18 2006/05/13 07:29:58 henoheno Exp $
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// function 'convert_html()', wiki text parser
// and related classes-and-functions

function convert_html($lines, $noPara = FALSE)
{
	global $vars, $digest;
	static $contents_id = 0;

	// Set digest
	$digest = md5(join('', get_source($vars['page'])));

	if (! is_array($lines)) $lines = explode("\n", $lines);

	$body = new Body(++$contents_id);
	$body->noPara = $noPara;
	$body->parse($lines);

	return $body->toString();
}

// Block elements
class Element
{
	var $parent;
	var $elements; // References of childs
	var $last;     // Insert new one at the back of the $last

	function Element()
	{
		$this->elements = array();
		$this->last     = & $this;
	}

	function setParent(& $parent)
	{
		$this->parent = & $parent;
	}

	function & add(& $obj)
	{
		if ($this->canContain($obj)) {
			return $this->insert($obj);
		} else {
			return $this->parent->add($obj);
		}
	}

	function & insert(& $obj)
	{
		$obj->setParent($this);
		$this->elements[] = & $obj;

		$this->last = & $obj->last;
		return $this->last;
	}

	function canContain($obj)
	{
		return TRUE;
	}

	function wrap($string, $tag, $param = '', $canomit = TRUE)
	{
		return ($canomit && $string == '') ? '' :
			'<' . $tag . $param . '>' . $string . '</' . $tag . '>';
	}

	function toString()
	{
		$ret = array();
		foreach (array_keys($this->elements) as $key)
			$ret[] = $this->elements[$key]->toString();
		return join("\n", $ret);
	}

	function dump($indent = 0)
	{
		$ret = str_repeat(' ', $indent) . get_class($this) . "\n";
		$indent += 2;
		foreach (array_keys($this->elements) as $key) {
			$ret .= is_object($this->elements[$key]) ?
				$this->elements[$key]->dump($indent) : '';
				//str_repeat(' ', $indent) . $this->elements[$key];
		}
		return $ret;
	}
}

// Returns inline-related object
function & Factory_Inline($text)
{
	// Check the first letter of the line
	if (substr($text, 0, 1) == '~') {
		return new Paragraph(' ' . substr($text, 1));
	} else {
		return new Inline($text);
	}
}

function & Factory_DList(& $root, $text)
{
	$out = explode('|', ltrim($text), 2);
	if (count($out) < 2) {
		return Factory_Inline($text);
	} else {
		return new DList($out);
	}
}

// '|'-separated table
function & Factory_Table(& $root, $text)
{
	if (! preg_match('/^\|(.+)\|([hHfFcC]?)$/', $text, $out)) {
		return Factory_Inline($text);
	} else {
		return new Table($out);
	}
}

// Comma-separated table
function & Factory_YTable(& $root, $text)
{
	if ($text == ',') {
		return Factory_Inline($text);
	} else {
		return new YTable(csv_explode(',', substr($text, 1)));
	}
}

function & Factory_Div(& $root, $text)
{
	$matches = array();

	// Seems block plugin?
	if (PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK) {
		// Usual code
		if (preg_match('/^\#([^\(]+)(?:\((.*)\))?/', $text, $matches) &&
			exist_plugin_convert($matches[1])) {
			return new Div($matches);
		}
	} else {
		// Hack code
		if(preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(\{*)/', $text, $matches) &&
			exist_plugin_convert($matches[1])) {
			$len  = strlen($matches[3]);
			$body = array();
			if ($len == 0) {
				return new Div($matches); // Seems legacy block plugin
			} else if (preg_match('/\{{' . $len . '}\s*\r(.*)\r\}{' . $len . '}/', $text, $body)) {
				$matches[2] .= "\r" . $body[1] . "\r";
				return new Div($matches); // Seems multiline-enabled block plugin
			}
		}
	}

	return new Paragraph($text);
}

// Inline elements
class Inline extends Element
{
	function Inline($text)
	{
		parent::Element();
		$this->elements[] = trim((substr($text, 0, 1) == "\n") ?
			$text : make_link($text));
	}

	function & insert(& $obj)
	{
		$this->elements[] = $obj->elements[0];
		return $this;
	}

	function canContain($obj)
	{
		return is_a($obj, 'Inline');
	}

	function toString()
	{
		global $line_break;
		return join(($line_break ? '<br />' . "\n" : "\n"), $this->elements);
	}

	function & toPara($class = '')
	{
		$obj = new Paragraph('', $class);
		$obj->insert($this);
		return $obj;
	}
}

// Paragraph: blank-line-separated sentences
class Paragraph extends Element
{
	var $param;

	function Paragraph($text, $param = '')
	{
		parent::Element();
		$this->param = $param;
		if ($text == '') return;

		if (substr($text, 0, 1) == '~')
			$text = ' ' . substr($text, 1);

		$this->insert(Factory_Inline($text));
	}

	function canContain($obj)
	{
		return is_a($obj, 'Inline');
	}

	function toString()
	{
		return $this->wrap(parent::toString(), 'p', $this->param);
	}
}

// * Heading1
// ** Heading2
// *** Heading3
class Heading extends Element
{
	var $level;
	var $id;
	var $msg_top;

	function Heading(& $root, $text)
	{
		parent::Element();

	if (strspn($text, '!') > 0)
	{
		$this->level = 0;
	}
	else
	{
		$this->level = min(3, strspn($text, '*'));
	}
		list($text, $this->msg_top, $this->id) = $root->getAnchor($text, $this->level);
		$this->insert(Factory_Inline($text));
		$this->level++; // h2,h3,h4
	}

	function & insert(& $obj)
	{
		parent::insert($obj);
		return $this->last = & $this;
	}

	function canContain(& $obj)
	{
		return FALSE;
	}

	function wrap($string, $tag, $param = '', $canomit = TRUE)
	{
		$secedit = plugin_secedit_wrap($string, $tag, $param, $this->id);
		return $secedit ? $secedit : parent::wrap($string, $tag, $param, $canomit);
	}

	function toString()
	{
		return $this->msg_top .  $this->wrap(parent::toString(),
			'h' . $this->level, ' id="' . $this->id . '"');
	}
}

// ----
// Horizontal Rule
class HRule extends Element
{
	function HRule(& $root, $text)
	{
		parent::Element();
	}

	function canContain(& $obj)
	{
		return FALSE;
	}

	function toString()
	{
		global $hr;
		return $hr;
	}
}

// Lists (UL, OL, DL)
class ListContainer extends Element
{
	var $tag;
	var $tag2;
	var $level;
	var $style;
	var $margin;
	var $left_margin;

	function ListContainer($tag, $tag2, $head, $text)
	{
		parent::Element();

		$var_margin      = '_' . $tag . '_margin';
		$var_left_margin = '_' . $tag . '_left_margin';
		global $$var_margin, $$var_left_margin;

		$this->margin      = $$var_margin;
		$this->left_margin = $$var_left_margin;

		$this->tag   = $tag;
		$this->tag2  = $tag2;
		$this->level = min(3, strspn($text, $head));
		$text = ltrim(substr($text, $this->level));

		parent::insert(new ListElement($this->level, $tag2));
		if ($text != '')
			$this->last = & $this->last->insert(Factory_Inline($text));
	}

	function canContain(& $obj)
	{
		return (! is_a($obj, 'ListContainer')
			|| ($this->tag == $obj->tag && $this->level == $obj->level));
	}

	function setParent(& $parent)
	{
		parent::setParent($parent);

		$step = $this->level;
		if (isset($parent->parent) && is_a($parent->parent, 'ListContainer'))
			$step -= $parent->parent->level;

		$margin = $this->margin * $step;
		if ($step == $this->level)
			$margin += $this->left_margin;

		if ($this->tag !== 'dl')
		{
			global $_list_pad_str;
			$this->style = sprintf($_list_pad_str, $this->level, $margin, $margin);
		}
		else
		{
			global $_dlist_pad_str;
			$this->style = sprintf(' class="list%d dl-horizontal" ', $this->level, $margin, $margin);
		}
	}

	function & insert(& $obj)
	{
		if (! is_a($obj, get_class($this)))
			return $this->last = & $this->last->insert($obj);

		// Break if no elements found (BugTrack/524)
		if (count($obj->elements) == 1 && empty($obj->elements[0]->elements))
			return $this->last->parent; // up to ListElement

		// Move elements
		foreach(array_keys($obj->elements) as $key)
			parent::insert($obj->elements[$key]);

		return $this->last;
	}

	function toString()
	{
		return $this->wrap(parent::toString(), $this->tag, $this->style);
	}
}

class ListElement extends Element
{
	function ListElement($level, $head)
	{
		parent::Element();
		$this->level = $level;
		$this->head  = $head;
	}

	function canContain(& $obj)
	{
		return (! is_a($obj, 'ListContainer') || ($obj->level > $this->level));
	}

	function toString()
	{
		return $this->wrap(parent::toString(), $this->head);
	}
}

// - One
// - Two
// - Three
class UList extends ListContainer
{
	function UList(& $root, $text)
	{
		parent::ListContainer('ul', 'li', '-', $text);
	}
}

// + One
// + Two
// + Three
class OList extends ListContainer
{
	function OList(& $root, $text)
	{
		parent::ListContainer('ol', 'li', '+', $text);
	}
}

// : definition1 | description1
// : definition2 | description2
// : definition3 | description3
class DList extends ListContainer
{
	function DList($out)
	{
		parent::ListContainer('dl', 'dt', ':', $out[0]);
		$this->last = & Element::insert(new ListElement($this->level, 'dd'));
		if ($out[1] != '')
			$this->last = & $this->last->insert(Factory_Inline($out[1]));
	}
}

// > Someting cited
// > like E-mail text
class BQuote extends Element
{
	var $level;

	function BQuote(& $root, $text)
	{
		parent::Element();

		$head = substr($text, 0, 1);
		$this->level = min(3, strspn($text, $head));
		$text = ltrim(substr($text, $this->level));

		if ($head == '<') { // Blockquote close
			$level       = $this->level;
			$this->level = 0;
			$this->last  = & $this->end($root, $level);
			if ($text != '')
				$this->last = & $this->last->insert(Factory_Inline($text));
		} else {
			$this->insert(Factory_Inline($text));
		}
	}

	function canContain(& $obj)
	{
		return (! is_a($obj, get_class($this)) || $obj->level >= $this->level);
	}

	function & insert(& $obj)
	{
		// BugTrack/521, BugTrack/545
		if (is_a($obj, 'inline'))
			return parent::insert($obj->toPara(' class="quotation"'));

		if (is_a($obj, 'BQuote') && $obj->level == $this->level && count($obj->elements)) {
			$obj = & $obj->elements[0];
			if (is_a($this->last, 'Paragraph') && count($obj->elements))
				$obj = & $obj->elements[0];
		}
		return parent::insert($obj);
	}

	function toString()
	{
		return $this->wrap(parent::toString(), 'blockquote');
	}

	function & end(& $root, $level)
	{
		$parent = & $root->last;

		while (is_object($parent)) {
			if (is_a($parent, 'BQuote') && $parent->level == $level)
				return $parent->parent;
			$parent = & $parent->parent;
		}
		return $this;
	}
}

class TableCell extends Element
{
	var $tag = 'td'; // {td|th}
	var $colspan = 1;
	var $rowspan = 1;
	var $style; // is array('width'=>, 'align'=>...);

	function TableCell($text, $is_template = FALSE)
	{
		parent::Element();
		$this->style = $matches = array();

		while (preg_match('/^(?:(LEFT|CENTER|RIGHT)|(BG)?COLOR\(([#\w]+)\)|SIZE\((\d+)\)):(.*)$/', $text, $matches)) {
			if ($matches[1]) {
				$this->style['align'] = 'text-align:' . strtolower($matches[1]) . ';';
				$text = $matches[5];
			} else if ($matches[3]) {
				$name = $matches[2] ? 'background-color' : 'color';
				$this->style[$name] = $name . ':' . htmlspecialchars($matches[3]) . ';';
				$text = $matches[5];
			} else if ($matches[4]) {
				$this->style['size'] = 'font-size:' . htmlspecialchars($matches[4]) . 'px;';
				$text = $matches[5];
			}
		}
		if ($is_template && is_numeric($text))
			$this->style['width'] = 'width:' . $text . 'px;';

		if ($text == '>') {
			$this->colspan = 0;
		} else if ($text == '~') {
			$this->rowspan = 0;
		} else if (substr($text, 0, 1) == '~') {
			$this->tag = 'th';
			$text      = substr($text, 1);
		}

		if ($text != '' && $text{0} == '#') {
			// Try using Div class for this $text
			$obj = & Factory_Div($this, $text);
			if (is_a($obj, 'Paragraph'))
				$obj = & $obj->elements[0];
		} else {
			$obj = & Factory_Inline($text);
		}

		$this->insert($obj);
	}

	function setStyle(& $style)
	{
		foreach ($style as $key=>$value)
			if (! isset($this->style[$key]))
				$this->style[$key] = $value;
	}

	function toString()
	{
		if ($this->rowspan == 0 || $this->colspan == 0) return '';

		$param = ' class="style_' . $this->tag . '"';
		if ($this->rowspan > 1)
			$param .= ' rowspan="' . $this->rowspan . '"';
		if ($this->colspan > 1) {
			$param .= ' colspan="' . $this->colspan . '"';
			unset($this->style['width']);
		}
		if (! empty($this->style))
			$param .= ' style="' . join(' ', $this->style) . '"';

		return $this->wrap(parent::toString(), $this->tag, $param, FALSE);
	}
}

// | title1 | title2 | title3 |
// | cell1  | cell2  | cell3  |
// | cell4  | cell5  | cell6  |
class Table extends Element
{
	var $type;
	var $types;
	var $col; // number of column
	var $css_style;
	var $css_class;
	var $css_enable;

	function Table($out)
	{
		parent::Element();

		// --------------------------------
		// customize by hokuken.com
		// enable table setting
		if( preg_match('/^\|STYLE:(.+)\|$/', $out[0], $ms) ){

			$params = explode(',', trim($ms[1], ", \t\n\r") );

			$style = array();
			foreach($params as $p){

				$pp = explode('=', $p);

				switch($pp[0]){
					case 'right'  :
					case 'left'   :
					case 'center' :
						$style['align'] = $p;
						break;
					case 'around' :
						$style['float'] = true;
						break;
					case 'sortable' :
						$style['sortable'] = true;
						break;
					case 'class' :
						if(isset($pp[1])) $style['class'] = $pp[1];
						break;
					case 'responsive':
						$style['responsive'] = true;
						break;
				}
			}


			//装飾に関する設定
			$addstyle = '';
			if( isset($style['align']) ) {
				if( isset($style['float']) ){
					$addstyle .= 'float:'.$style['align'].';';
					$addstyle .= ($style['align']=='left') ? 'margin-right:1em;' : 'margin-left:1em;';
				}
				else{
					switch ($style['align'])
					{
						case 'left':
							$addstyle .= 'margin-left:0;margin-right:auto;';
							break;
						case 'right':
							$addstyle .= 'margin-left:auto;margin-right:10px;';
							break;
						case 'center':
						default:
							$addstyle .= 'margin-left:auto;margin-right:auto;';
					}
				}
			}

			$this->css_class = 'style_table';
			if( isset($style['class']) ){
				$this->css_class = $style['class'];
			}
			else if($style != ''){
				$this->css_style = ' style="'.$addstyle.'" ';
			}


			//sortable設定
			if( isset($style['sortable']) ){
				$this->css_class .= ' tablesorter';
				$qt = get_qt();
				$qt->setv('jquery_include', true);
				$qt->appendv_once('lib_convert_html_table1', 'beforescript', '<link rel="stylesheet" media="screen" href="js/jquery.tablesorter.min.css" type="text/css" />');
				$qt->appendv_once('lib_convert_html_table2', 'beforescript', '<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){ $("table.tablesorter").tablesorter(); });
</script>');

			}

			# Responsive Table
			# @see http://getbootstrap.com/css/#tables
			if (isset($style['responsive']))
			{
				$this->css_class .= ' table qhm-table-responsive';
				$qt = get_qt();
				$qt->setv('jquery_include', true);
				$addscript = <<< EOS
					<script>
					$(function(){
						$("table.qhm-table-responsive").wrap('<div class="table-responsive qhm-table-responsive-wrapper"></div>');
					});
					</script>
EOS;
				$qt->appendv_once('lib_convert_html_table_responsive', 'lastscript', $addscript);
			}

			$this->css_enable = ($this->css_class != '') || ($this->css_style !='');
		}
		else{ //normal table process
			$cells       = explode('|', $out[1]);
			$this->col   = count($cells);
			$this->type  = strtolower($out[2]);
			$this->types = array($this->type);
			$is_template = ($this->type == 'c');
			$row = array();
			foreach ($cells as $cell)
				$row[] = new TableCell($cell, $is_template);
			$this->elements[] = $row;
		}
	}

	function canContain(& $obj)
	{
		if (is_a($obj, 'Table')) {
			if ($obj->col == $this->col) {
				return TRUE;
			} else if ($this->css_enable) {
				if (is_null($this->col)) $this->col = $obj->col;
				return TRUE;
			}
		}
		return FALSE;
	}

	function & insert(& $obj)
	{
		$this->elements[] = $obj->elements[0];
		$this->types[]    = $obj->type;
		return $this;
	}

	function toString()
	{
		static $parts = array('h'=>'thead', 'f'=>'tfoot', ''=>'tbody');

		// Set rowspan (from bottom, to top)
		for ($ncol = 0; $ncol < $this->col; $ncol++) {
			$rowspan = 1;
			foreach (array_reverse(array_keys($this->elements)) as $nrow) {
				$row = & $this->elements[$nrow];
				if ($row[$ncol]->rowspan == 0) {
					++$rowspan;
					continue;
				}
				$row[$ncol]->rowspan = $rowspan;
				// Inherits row type
				while (--$rowspan)
					$this->types[$nrow + $rowspan] = $this->types[$nrow];
				$rowspan = 1;
			}
		}

		// Set colspan and style
		$stylerow = NULL;
		foreach (array_keys($this->elements) as $nrow) {
			$row = & $this->elements[$nrow];
			if ($this->types[$nrow] == 'c')
				$stylerow = & $row;
			$colspan = 1;
			foreach (array_keys($row) as $ncol) {
				if ($row[$ncol]->colspan == 0) {
					++$colspan;
					continue;
				}
				$row[$ncol]->colspan = $colspan;
				if ($stylerow !== NULL) {
					$row[$ncol]->setStyle($stylerow[$ncol]->style);
					// Inherits column style
					while (--$colspan)
						$row[$ncol - $colspan]->setStyle($stylerow[$ncol]->style);
				}
				$colspan = 1;
			}
		}

		// toString
		$string = '';
		foreach ($parts as $type => $part)
		{
			$part_string = '';
			foreach (array_keys($this->elements) as $nrow) {
				if ($this->types[$nrow] != $type)
					continue;
				$row        = & $this->elements[$nrow];
				$row_string = '';
				foreach (array_keys($row) as $ncol)
					$row_string .= $row[$ncol]->toString();
				$part_string .= $this->wrap($row_string, 'tr');
			}
			$string .= $this->wrap($part_string, $part);
		}

		$class = $this->css_class == '' ? 'style_table' : $this->css_class;
		$string = $this->wrap($string, 'table', ' class="'.$class.'" cellspacing="1" border="0"'.$this->css_style);

		return $this->css_enable ? $string : $this->wrap($string, 'div', ' class="ie5" ');
	}
}

// , title1 , title2 , title3
// , cell1  , cell2  , cell3
// , cell4  , cell5  , cell6
class YTable extends Element
{
	var $col;

	function YTable($_value)
	{
		parent::Element();

		$align = $value = $matches = array();
		foreach($_value as $val) {
			if (preg_match('/^(\s+)?(.+?)(\s+)?$/', $val, $matches)) {
				$align[] =($matches[1] != '') ?
					((isset($matches[3]) && $matches[3] != '') ?
						' style="text-align:center"' :
						' style="text-align:right"'
					) : '';
				$value[] = $matches[2];
			} else {
				$align[] = '';
				$value[] = $val;
			}
		}
		$this->col = count($value);
		$colspan = array();
		foreach ($value as $val)
			$colspan[] = ($val == '==') ? 0 : 1;
		$str = '';
		$count = count($value);
		for ($i = 0; $i < $count; $i++) {
			if ($colspan[$i]) {
				while ($i + $colspan[$i] < $count && $value[$i + $colspan[$i]] == '==')
					$colspan[$i]++;
				$colspan[$i] = ($colspan[$i] > 1) ? ' colspan="' . $colspan[$i] . '"' : '';
				$str .= '<td class="style_td"' . $align[$i] . $colspan[$i] . '>' . make_link($value[$i]) . '</td>';
			}
		}
		$this->elements[] = $str;
	}

	function canContain(& $obj)
	{
		return is_a($obj, 'YTable') && ($obj->col == $this->col);
	}

	function & insert(& $obj)
	{
		$this->elements[] = $obj->elements[0];
		return $this;
	}

	function toString()
	{
		$rows = '';
		foreach ($this->elements as $str)
			$rows .= "\n" . '<tr class="style_tr">' . $str . '</tr>' . "\n";
		$rows = $this->wrap($rows, 'table', ' class="style_table" cellspacing="1" border="0"');
		return $this->wrap($rows, 'div', ' class="ie5"');
	}
}

// ' 'Space-beginning sentence
// ' 'Space-beginning sentence
// ' 'Space-beginning sentence
class Pre extends Element
{
	function Pre(& $root, $text)
	{
		global $preformat_ltrim;
		parent::Element();
		$this->elements[] = htmlspecialchars(
			(! $preformat_ltrim || $text == '' || $text{0} != ' ') ? $text : substr($text, 1));
	}

	function canContain(& $obj)
	{
		return is_a($obj, 'Pre');
	}

	function & insert(& $obj)
	{
		$this->elements[] = $obj->elements[0];
		return $this;
	}

	function toString()
	{
		return $this->wrap(join("\n", $this->elements), 'pre');
	}
}

// Block plugin: #something (started with '#')
class Div extends Element
{
	var $name;
	var $param;

	function Div($out)
	{
		parent::Element();
		list(, $this->name, $this->param) = array_pad($out, 3, '');
	}

	function canContain(& $obj)
	{
		return FALSE;
	}

	function toString()
	{
		// Call #plugin
		return do_plugin_convert($this->name, $this->param);
	}
}

// LEFT:/CENTER:/RIGHT:/LEFT2:/CENTER2:/RIGHT2:
class Align extends Element
{
	var $align;
	var $ptag;

	function Align($align, $ptag=true)
	{
		parent::Element();
		$this->align = $align;
		$this->ptag = $ptag;
	}

	function canContain(& $obj)
	{
		return is_a($obj, 'Inline') OR is_a($obj, 'Heading') OR is_a($obj, 'Paragraph');
	}

	function toString()
	{
		$str = parent::toString();
		return $this->wrap($str, 'div', ' class="qhm-align-'.$this->align.'" style="text-align:' . $this->align . '"');
	}
}

// Body
class Body extends Element
{
	var $id;
	var $count = 0;
	var $contents;
	var $contents_last;
	var $classes = array(
		'-' => 'UList',
		'+' => 'OList',
		'>' => 'BQuote',
		'<' => 'BQuote');
	var $factories = array(
		':' => 'DList',
		'|' => 'Table',
		',' => 'YTable',
		'#' => 'Div');
	var $noPara = FALSE;

	function Body($id)
	{
		$this->id            = $id;
		$this->contents      = new Element();
		$this->contents_last = & $this->contents;
		parent::Element();
	}

	function parse(& $lines)
	{
		$this->last = & $this;
		$matches = array();

		while (! empty($lines)) {
			$line = array_shift($lines);

			// Escape comments
			if (substr($line, 0, 2) == '//') continue;

			if (preg_match('/^(LEFT|CENTER|RIGHT)(2?):(.*)$/', $line, $matches)) {
				// <div style="text-align:...">
				$ptag = ($matches[2] == '') ? true : false;
				$this->last = & $this->last->add(new Align(strtolower($matches[1]),$ptag));
				if ($matches[3] == '') continue;
				$line = $matches[3];
			}

			$line = rtrim($line, "\r\n");

			// Empty
			if ($line == '') {
				$this->last = & $this;
				continue;
			}

			// Horizontal Rule
			if (substr($line, 0, 4) == '----') {
				$this->insert(new HRule($this, $line));
				continue;
			}

			// Multiline-enabled block plugin
			if ( ! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK &&
				preg_match('/^#[^{]+(\{\{+)\s*$/', $line, $matches)) {
				$len = strlen($matches[1]);
				$line .= "\r"; // Delimiter
				while (! empty($lines)) {
					$next_line = preg_replace("/[\r\n]*$/", '', array_shift($lines));
					if (preg_match('/\A\}{' . $len . '}\s*\z/', $next_line)) {
						$line .= $next_line;
						break;
					} else {
						$line .= $next_line .= "\r"; // Delimiter
					}
				}
			}

			// The first character
			$head = $line{0};

			// Heading
			if ($head === '*' OR $head === '!') {
				if (is_a($this->last, 'Align'))
					$this->last->add(new Heading($this, $line));
				else
					$this->insert(new Heading($this, $line));
				continue;
			}

			// Pre
			if ($head == ' ' || $head == "\t") {
				$this->last = & $this->last->add(new Pre($this, $line));
				continue;
			}

			// Line Break
			if (substr($line, -1) == '~')
				$line = substr($line, 0, -1) . "\r";

			// Other Character
			if (isset($this->classes[$head])) {
				$classname  = $this->classes[$head];
				$this->last = & $this->last->add(new $classname($this, $line));
				continue;
			}

			// Other Character
			if (isset($this->factories[$head])) {
				$factoryname = 'Factory_' . $this->factories[$head];
				$this->last  = & $this->last->add($factoryname($this, $line));
				continue;
			}

			// Extend TITLE by hokuken
			if (preg_match('/^(TITLE):(.*)$/',$line,$matches))
			{
				global $page_title;
				$title = h($matches[2]);

				$qt = get_qt();
				$qt->setv_once('this_page_title', $title. " - ". $page_title);
				$qt->setv_once('this_right_title', $title);

				continue;
			}

			// Extend FREETITLE by hokuken
			if (preg_match('/^(FREETITLE):(.*)$/',$line,$matches))
			{
				$t = h($matches[2]);

				$qt = get_qt();
				$qt->setv('this_page_title', $t);
				$qt->setv('this_right_title', $t);

				continue;
			}

			// Extend TITLE by miko
			if (preg_match('/^(HEAD):(.*)$/',$line,$matches))
			{
				global $headcopy;
				$headcopy = $matches[2];
				continue;
			}

			// Extend KILLERPAGE by hokuken
			if (preg_match('/^(KILLERPAGE):(.*)$/', $line, $matches)){
				global $autolink, $killer_fg, $killer_bg;
				$autolink = 0;
				$tmpstr = htmlspecialchars($matches[2]);
				list($killer_fg, $killer_bg) = preg_split('/,/', $tmpstr);
				continue;
			}

			// Extend KILLERPAGE2 by hokuken 2008 12/1
			if (preg_match('/^(KILLERPAGE2):(.*)$/', $line, $matches)){
				global $autolink, $killer_page2;
				$autolink = 0;
				$killer_page2 = array();
				$tmpstr = htmlspecialchars($matches[2]);
				list($fg, $bg, $width, $padding, $bg_body, $fg_body)
					= array_pad(preg_split('/,/', $tmpstr),6,'');
				$width = ($width!='' && preg_match('/^[0-9]+$/',$width)) ? $width : 720;
				$padding = ($padding!='' && preg_match('/^[0-9]+$/',$width))
						? $padding : '60';
				$bg_body = ($bg_body =='') ? '#fff' : $bg_body;
				$fg_body = ($fg_body =='') ? '#111' : $fg_body;

				$killer_page2['fg'] = $fg;
				$killer_page2['bg'] = $bg;
				$killer_page2['width'] = $width;
				$killer_page2['padding'] = $padding;
				$killer_page2['bg_body'] = $bg_body;
				$killer_page2['fg_body'] = $fg_body;

				continue;
			}

			// Extend KILLERPAGE2IMG by hokuken 2008 12/1
			if (preg_match('/^(KILLERPAGE2IMG):(.*)$/', $line, $matches)){
				global $autolink, $killer_page2;

				$autolink = 0;
				$killer_page2['img'] = htmlspecialchars($matches[2]);
				continue;
			}

			// Extend KILLERPAGE2BG by hokuken 2008 12/1
			if (preg_match('/^(KILLERPAGE2BG):(.*)$/', $line, $matches)){
				global $autolink, $killer_page2;

				$autolink = 0;
				$killer_page2['body_bg_img'] = htmlspecialchars($matches[2]);
				continue;
			}


			// Extend AUTOLINK SETTING by hokuken
			if (preg_match('/^(NOAUTOLINK):(.*)$/', $line, $matches)){
				global $autolink;
				$autolink = 0;
				continue;
			}

			// Extend NOINDEX by hokuken
			if (preg_match('/^(NOINDEX):(.*)$/', $line, $matches)){
				global $noindex;
				$noindex = ' <meta name="robots" content="NOINDEX,NOFOLLOW" /><meta name="googlebot" content="noindex,nofollow" />';
				continue;
			}

			if (preg_match('/^STYLE:(.*)$/', $line, $matches))
			{
				global $block_style;
				$block_style = $matches[1];
				continue;
			}

			if (preg_match('/^CLASS:(.*)$/', $line, $matches))
			{
				global $block_class;
				$block_class = $matches[1];
				continue;
			}

			if (preg_match('/^IMAGE:(.*)$/', $line, $matches))
			{
				global $block_image;
				$block_imagefile = '';
				if (preg_match('/\.(gif|png|jpe?g)$/i', $matches[1]))
				{
					$matches[1] = trim($matches[1]);
					if (is_url($matches[1]) || is_file($matches[1]))
					{
						$block_imagefile = $matches[1];
					}
					else
					{
						if (is_file(UPLOAD_DIR.$matches[1]))
						{
							$block_imagefile = UPLOAD_DIR.$matches[1];
						}
					}
				}

				if ($block_imagefile != '')
				{
					$block_image = '<img src="'.h($block_imagefile).'">';
				}
				continue;
			}

			if (is_a($this->last, 'Align') && $this->last->ptag)
			{
				$this->last = $this->last->add(Factory_Inline('~'.$line));
				continue;
			}

			// Default
			$this->last = & $this->last->add(Factory_Inline($line));
		}
	}

	function getAnchor($text, $level)
	{
		global $top, $_symbol_anchor;

		// Heading id (auto-generated)
		$autoid = 'content_' . $this->id . '_' . $this->count;
		$this->count++;

		// Heading id (specified by users)
		$id = make_heading($text, FALSE); // Cut fixed-anchor from $text
		if ($id == '') {
			// Not specified
			$id     = & $autoid;
			$anchor = '';
		} else {
			$anchor = ' &aname(' . $id . ');';
		}

		$text = ' ' . $text;

		// Add 'page contents' link to its heading
		$this->contents_last = & $this->contents_last->add(new Contents_UList($text, $level, $id));

		// Add heding
		return array($text . $anchor, $this->count > 1 ? "\n" . $top : '', $autoid);
	}

	function & insert(& $obj)
	{
		if (is_a($obj, 'Inline') && ! $this->noPara) $obj = & $obj->toPara();
		return parent::insert($obj);
	}

	function toString()
	{
		global $vars;

		$text = parent::toString();

		// #contents
		$text = preg_replace_callback('/<#_contents_>/',
			array(& $this, 'replace_contents'), $text);

		return $text . "\n";
	}

	function replace_contents($arr)
	{
		$contents  = '<div class="contents">' . "\n" .
				'<a id="contents_' . $this->id . '"></a>' . "\n" .
				$this->contents->toString() . "\n" .
				'</div>' . "\n";
		return $contents;
	}
}

class Contents_UList extends ListContainer
{
	function Contents_UList($text, $level, $id)
	{
		// Reformatting $text
		// A line started with "\n" means "preformatted" ... X(
		make_heading($text);
		$text = "\n" . '<a href="#' . $id . '">' . $text . '</a>' . "\n";
		parent::ListContainer('ul', 'li', '-', str_repeat('-', $level));
		$this->insert(Factory_Inline($text));
	}

	function setParent(& $parent)
	{
		global $_list_pad_str;

		parent::setParent($parent);
		$step   = $this->level;
		$margin = $this->left_margin;
		if (isset($parent->parent) && is_a($parent->parent, 'ListContainer')) {
			$step  -= $parent->parent->level;
			$margin = 0;
		}
		$margin += $this->margin * ($step == $this->level ? 1 : $step);
		$this->style = sprintf($_list_pad_str, $this->level, $margin, $margin);
	}
}
