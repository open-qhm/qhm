<?php
/**
 *   Bootstrap Media list Plugin
 *   -------------------------------------------
 *   plugin/media_list.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/06/10
 *   modified : 
 *
 *   @see: http://getbootstrap.com/components/#media
 *   
 *   Usage :
 *     #media_list{{
 *     * Panel Header
 *     ====
 *     Panel Contents
 *     ====
 *     Panel footer
 *     }}
 *   
 */
define("PLUGIN_MEDIA_LIST_DEFAULT_IMAGE", '<img class="media-object" src="http://placehold.jp/80x80.png" alt="dummy">');

function plugin_media_list_convert()
{
    $qm = get_qm();
    $qt = get_qt();

    $args   = func_get_args();
    $body   = array_pop($args);

    $cols = array();
    $delim = "\r====\r";
    
    foreach ($args as $arg)
    {
        if (preg_match('{ ^(\d+)(?:\+(\d+))?((?:\.[a-zA-Z0-9_-]+)+)?$ }mx', $arg, $matches))
        {
            $cols['cols']   = !empty($matches[1]) ? (int)$matches[1] : 0;
            $cols['offset'] = !empty($matches[2]) ? (int)$matches[2] : 0;
            $cols['class']  = !empty($matches[3]) ? trim(str_replace('.', ' ', $matches[3])) : '';
        }
    }
    
    $items = array();
    $item_bodies = explode($delim, $body);
    foreach ($item_bodies as $i => $item_body)
    {
        $item = plugin_media_list_createItemData($item_body);
        if ($item !== FALSE)
        {
            $items[] = $item;
        }
    }
    
    $html  = '';
    foreach($items as $i => $item)
    {
        $html .= '<div class="qhm-plugin-medialist media">';
        $align = $item['align'] ? $item['align'] : 'pull-left';
        $image = $item['image'] ? $item['image'] : PLUGIN_MEDIA_LIST_DEFAULT_IMAGE;
        $html .= '<span class="'. $align . '">' . $image . '</span>';
        
        if ((isset($item['heading']) && $item['heading'] !== '') or $item['body'] !== '')
        {
            $html .= '
            <div class="media-body">
                '. $item['heading']. '
                '. $item['body'] . '
            </div>
';
        }
        $html .= '</div>';
    }
    
    if (count($cols) > 0)
    {
        $cols_class = plugin_media_list_createColumnClass($cols);        
    
        $cols_fmt = '<div class="row"><div class="%s">%s</div></div>';
        $html = sprintf($cols_fmt, $cols_class, $html);
    }

    return $html;    

}

function plugin_media_list_createItemData($body)
{
    $body = trim($body);
    if ($body === '') return FALSE;
    
    $data = array();

    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));

    $elements = preg_split('{ \n+ }mx', $body);
    $line_count = count($elements);
    $max_line = $line_count - 1;

    $imageSet = $headingSet = false;
    
    // 最初の2行のみ
    for ($line = 0; $line < 2 && $line < $line_count; $line++)
    {
        $html = convert_html($elements[$line], TRUE);

        //画像をセット
        if ( ! $imageSet && preg_match('{ <img\b.*?> }mx', $html))
        {
            $data['image'] = plugin_media_list_adjustImage($html);;
            $imageSet = true;
            unset($elements[$line]);
        }
        //見出しをセット
        else if ( ! $headingSet)
        {
            if (preg_match('{ <h }mx', $html))
            {
                $data['heading'] = plugin_media_list_adjustHeading($html);
                $headingSet = true;
                unset($elements[$line]);
            }
            break;
        }
    }

    $data['body_data'] = compact('elements', 'line_count', 'max_line', 'imageSet', 'headingSet');
    $data = plugin_media_list_adjustData($data);
    if (isset($data['body_data']))
    {
        extract($data['body_data']);
        unset($data['body_data']);
    }

    // 残りをparse
    $data['body'] = convert_html(join("\n", $elements), TRUE);
    
    foreach ($data as $key => $value)
    {
        if (is_string($value))
            $data[$key] = trim($value);
    }

    return $data;
}

function plugin_media_list_adjustImage($html)
{
    return str_replace(
                  '<img', 
                  '<img class="media-object"',
                  strip_tags($html, '<img>')
              );
}

function plugin_media_list_adjustHeading($html)
{
    if ( ! preg_match('{ <h[1-6][^>]*?class=".*?" }mx', $html))
    {
        return preg_replace('{ <h([1-6])(.*?>) }mx', '<h\1 class="media-heading"\2', $html);
    }
    else
    {
        return $html;
    }
}

function plugin_media_list_adjustData($itemData)
{
    extract($itemData['body_data']);

    if ( ! isset($elements[$max_line])) return $itemData;

    $html = convert_html($elements[$max_line], TRUE);
    if ( ! $imageSet && preg_match('{ <img\b.*?> }mx', $html))
    {
        $itemData['image'] = str_replace(
                                  '<img', '<img class="media-object"',
                                  strip_tags($html, '<img>')
                                  );
        $itemData['align'] = 'pull-right';

        unset($itemData['body_data']['elements'][$max_line]);
    }

    return $itemData;
}

function plugin_media_list_createColumnClass($columnData)
{
    $classes = array();
    
    if (is_array($columnData))
    {
        if ( ! isset($columnData['cols']))
        {
            return '';
        }
    
        foreach($columnData as $column => $value)
        {
            $option = '';
            switch($column)
            {
                case 'offset':
                    $option = "offset-";
                case 'cols':
                    if ($value > 0)
                    {
                        $classes[] = 'col-sm-'. $option . $value;
                    }
                    break;
                case 'class':
                    if ($value !== '')
                    {
                        $classes[] = $value;
                    }
                    break;
            }
        }
        
        return join(" ", $classes);
    }
    
    return '';
}
