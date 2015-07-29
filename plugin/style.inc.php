<?php
// $Id$

function plugin_style_convert()
{
    $args = func_get_args();
    $last = func_num_args() - 1;
    if (strpos($args[$last], 'style=') === 0) {
    } elseif (strpos($args[$last], 'class=') === 0) {
    } else {
        $body = array_pop($args);
    }
    $options = array();
    foreach ($args as $arg) {
        list($key, $val) = explode('=', $arg, 2);
        $options[$key] = htmlspecialchars($val);
    }

    if (isset($body)) {
        $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
        $lines = explode("\n", $body);
        $body = convert_html($lines);
    } else {
        $body = '';
    }

    $ret = '<div class="ie5s">';
    $ret .= '<div';
    if( isset($options['class'])){
        $sty = plugin_style_getStyle($options['class']);
        if($sty){
            $ret .= ' style="'.$sty['style'].'" '.$sty['class'];
        }
        else{
            $ret .= ' class="qhm-plugin-style ' . $options['class'] . '"';
        }
    }
    else {
        $ret .= ' class="qhm-plugin-style"';
    }
    $ret .= isset($options['style']) ? ' style="' . $options['style'] . '"' : '';
    $ret .= '>';
    $ret .= $body;
    $ret .= '</div>';
    $ret .= '</div>';
    return $ret;
}

//半分、下位互換。CSSの記述を減らす役割も
function plugin_style_getStyle($name){
    $ms = array();
    $class_name = 'style_plugin';
    $class = 'class="qhm-plugin-style qhm-block ';
    if(preg_match('/(.*)box[0-5]$/', $name))
    {
        switch($name){
            case 'bluebox1':
                $border = 'solid 1px #33a';
                $bg_c = 'transparent';
                $class .= $class_name;
                break;
            case 'bluebox2':
                $border = 'solid 1px #33a';
                $bg_c = '#eef';
                break;
            case 'bluebox3':
                $border = 'solid 1px #33a';
                $bg_c = '#ffe';
                break;
            case 'bluebox4':
                $border = 'none';
                $bg_c = '#eef';
                break;
            case 'bluebox5':
                $border = 'none';
                $bg_c = '#ddf';
                break;

            case 'redbox1':
                $border = 'solid 1px #f00';
                $bg_c = 'transparent';
                $class .= $class_name;
                break;
            case 'redbox2':
                $border = 'solid 1px #f00';
                $bg_c = '#fee';
                break;
            case 'redbox3':
                $border = 'solid 1px #f00';
                $bg_c = '#ffe';
                break;
            case 'redbox4':
                $border = 'none';
                $bg_c = '#fee';
                break;
            case 'redbox5':
                $border = 'none';
                $bg_c = '#fdd';
                break;

            case 'graybox1':
                $border = 'solid 1px #333';
                $bg_c = 'transparent';
                $class .= $class_name;
                break;
            case 'graybox2':
                $border = 'solid 1px #333';
                $bg_c = '#eee';
                break;
            case 'graybox3':
                $border = 'solid 1px #333';
                $bg_c = '#ffe';
                break;
            case 'graybox4':
                $border = 'none';
                $bg_c = '#eee';
                break;
            case 'graybox5':
                $border = 'none';
                $bg_c = '#ddd';
                break;

            default:
                return false;
        }
        return array(
            'class'=>$class.'"',
            'style'=>"max-width:100%;border:{$border};background-color:{$bg_c};text-align:left;padding: 0px 10px;"
            );

    }
    else if( preg_match('/^box_(.*)_(...)$/', $name, $ms) )
    {
        //base color setting
        $base = $ms[1];
        switch($base){
            case 'red':
                $b_color = "#f00";
                $bg_color_s = "#fee";
                break;

            case 'blue':
                $b_color = "#00e";
                $bg_color_s = "#eef";
                break;

            case 'black':
                $b_color = "#000";
                $bg_color_s = "#eee";
                break;

            case 'yellow':
                $b_color = "#fe9";
                $bg_color_s = "#ffe";
                break;

            case 'green':
                $b_color = "#6a6";
                $bg_color_s = "#efe";
                break;

            case 'gray':
                $b_color = "#aaa";
                $bg_color_s = "#f6f6f6";
                break;

            case 'orange':
                $b_color = "#FF8300";
                $bg_color_s = "#FFE3BD";
                break;

            case 'brown':
                $b_color = "#960";
                $bg_color_s = "#FFE0BA";
                break;

            case 'purple':
                $b_color = "#c9c";
                $bg_color_s = "#fef";
                break;

            default:
                return false;
        }


        $tmpstr = $ms[2];

        //border setting
        switch($tmpstr{0}){
            case 's':
                $b_type = 'border:solid 1px '.$b_color;
                break;
            case 'd':
                $b_type = 'border:dashed 2px '.$b_color;
                break;
            case 'n':
            default:
                $b_type = 'border:solid 1px '.$bg_color_s;
        }

        //background color
        if($tmpstr{1}=='s'){
            $bg_style = 'background-color:'.$bg_color_s;
        }
        else{
            $bg_style = 'background-color:transparent';
            $class .= $class_name;
        }

        //width
        switch($tmpstr{2}){
            case 's':
                $bg_w = '60%'; break;
            case 'm':
                $bg_w = '80%'; break;
            case 'l':
            default:
                $bg_w = 'auto';
        }
        $width = "max-width:{$bg_w};width:{$bg_w}";

        $output_style = $b_type.';'.$bg_style.';'.$width.';text-align:left;padding:0 1.5em;margin:1em auto;';

        return array(
            'class'=>$class.'"',
            'style'=>$output_style
            );
    }
    return false;
}
