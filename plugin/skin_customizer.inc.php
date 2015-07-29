<?php
/**
 *   skin customizer
 *   -------------------------------------------
 *   skin_customizer.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/06/10
 *   modified :
 *
 *   Usage : 
 *   
 */
function plugin_skin_customizer_action()
{
    global $vars, $script; 
    
    if ( ! ss_admin_check())
    {
        redirect($script, '管理者のみアクセスできます。');
        exit;
    }

  	$skin_name = $_SESSION['temp_design'];
    $custom_file = CACHE_DIR.'custom_skin.'.$skin_name.'.dat';

    if (isset($vars['phase']) && $vars['phase'] == 'file_upload')
    {
        require(PLUGIN_DIR.'skin_customizer/SkinCustomizer_UploadHandler.php');
        
        $param_name = isset($vars['param_name']) ? $vars['param_name'] : 'files';
        
        $options = array(
            'upload_dir' => CACHE_DIR,
            'upload_url' => CACHE_DIR,
            'param_name' => $param_name,
            'image_versions' => array('' => array('auto_orient' => true)),
            'skin_name'  => $skin_name,
        );

        $upload_handler = new SkinCustomizer_UploadHandler($options);
        exit;
    }
  	else if (isset($vars['save']))
  	{
        $skin_config = read_skin_config($_SESSION['temp_design']);
        $data = array();
        foreach ($skin_config['custom_options'] as $key => $row)
        {
            if (isset($vars[$key]))
            {
                if ($row['type'] === 'if')
                {
                    $vars[$key] = (intval($vars[$key]) === 1) ? true : false;
                }
                $data[$key] = $vars[$key];
            }
        }
  	
        // data write
        $data = serialize($data);
        file_put_contents($custom_file, $data, LOCK_EX);
    
    }
    else if (isset($vars['reset']))
    {
        $files = glob(CACHE_DIR.'custom_skin.'.$skin_name .'*');
        foreach ($files as $file)
        {
            unlink($file);
        }
    }

    redirect($script);
}

function plugin_skin_customizer_set_form()
{
    global $script, $style_name;

    $skin_name = $_SESSION['temp_design'];
    $skin_config = read_skin_config($_SESSION['temp_design']);
    if (! $skin_config['bootstrap'])
    {
        return '';
    }
    
    $custom_file = CACHE_DIR.'custom_skin.'.$skin_name.'.dat';
    if (file_exists($custom_file))
    {
        $custom_data = file_get_contents($custom_file);
        $custom_data = unserialize($custom_data);

        if ($custom_data)
        {
            foreach($custom_data as $key => $value)
            {
                if (isset($skin_config['custom_options'][$key]))
                {
                    $skin_config['custom_options'][$key]['value'] = trim($value);
                }
            }
        }
    }

    $qt = get_qt();
    $html = array();
    $modal = array();
    $tmpl = '
  <div %s>
    <label class="control-label col-sm-4">%s</label>
    <div class="col-sm-8 text-left">
      %s
    </div>
  </div>
';
    foreach ($skin_config['custom_options'] as $key => $conf)
    {
        $group_attr = 'class="form-group"';
        if (isset($conf['follow']))
        {
            $group_attr = 'class="form-group collapse" data-follow="'.h($conf['follow']).'"';
        }

        if (isset($conf['draft']))
        {
            $draft = '<span class="text-muted">'.h($conf['draft']).'</span>';
            $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $draft);
            continue;
        }

        switch($conf['type'])
        {
            case 'divider':
                $html[] = '<div class="divider"></div>';
                break;

            case 'spacer':
                $html[] = '<div class="spacer"></div>';
                break;

            case 'text':
                $formhtml = '<input type="text" name="'.h($key).'" value="'.h($conf['value']).'" class="form-control input-sm">';
                $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                break;

            case 'textarea':
                $formhtml = '<textarea name="'.h($key).'" rows="2" class="form-control input-sm">'.h($conf['value']).'</textarea>';
                $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                break;

            case 'hidden':
                $formhtml = '<input type="hidden" name="'.h($key).'" value="'.h($conf['value']).'">';
                $html[] = $formhtml;
                break;

            case 'color':
                $formhtml = '
                <div class="row">
                  <div class="col-sm-5">
                    <input type="text" name="'.h($key).'" value="'.h($conf['value']).'" class="color-picker form-control input-sm" data-color="'.h($conf['value']).'">
                  </div>
                </div>
';
                $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                break;

            case 'theme_color':
                $formhtml = '
                  <div id="themeColorPicker_palette" class="themeColorPicker-palette">
                    <input type="hidden" name="'.h($key).'" value="'.h($conf['value']).'" class="theme-color-picker">
                  </div>
';
                $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                break;

            case 'img':
                $param = array(
                    'cmd'   => 'skin_customizer',
                    'phase' => 'file_upload',
                    'param_name' => h($key),
                );
                $formhtml = '
                <span class="pull-right"><small>選択中の画像：</small><span class="qhm-btn-default qhm-btn-sm" data-image="'.h($conf['value']).'" data-target="#qhm_skin_customizer_'.h($key).'_file">&nbsp;</span></span>
                <span class="qhm-btn-info qhm-btn-sm fileinput-button">
                  <i class="text-lg glyphicon glyphicon-paperclip"></i>
                  <span>アップロード</span>
                  <input type="file" name="'.h($key).'_FILE" id="qhm_skin_customizer_'.h($key).'_file" data-file-upload="image" data-form-data="'.h(json_encode($param)).'" data-param-name="'.h($key).'" data-sync="'.h($key).'" data-parent="file">
                  <input type="hidden" name="'.h($key).'" value="'.h($conf['value']).'">
                </span>
';
                $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                break;

            case 'font':
                $formhtml = '';
                if (isset($conf['options']))
                {
                    $formhtml  = '<select name="'.h($key).'" class="form-control input-sm">';
                    foreach ($conf['options'] as $option)
                    {
                        $selected = ($option == $conf['value']) ? " selected" : '';
                        $style = 'font-family: ' . h($option) . ';';
                        $formhtml .= '<option value="'.h($option).'" style="'.$style.'"'.$selected.'>'.h($option).'</option>';
                    }
                    $formhtml .= '</select>';
                }
                else
                {
                    $formhtml = '<input type="text" name="'.h($key).'" value="'.h($conf['value']).'" class="form-control input-sm">';
                }
                $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                break;

            case 'select':
                if (isset($conf['options']))
                {
                    $formhtml  = '<select name="'.h($key).'" class="form-control input-sm">';
                    foreach ($conf['options'] as $op_key =>  $op_name)
                    {
                        $selected = ($op_key == $conf['value']) ? " selected" : '';
                        $formhtml .= '<option value="'.h($op_key).'"'.$selected.'>'.h($op_name).'</option>';
                    }
                    $formhtml .= '</select>';
                    $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                }
                
                break;

            case 'if':
                $formhtml = '';
                if (isset($conf['paint']))
                {
                    $target = $conf['paint'];
                    if (isset($skin_config['custom_options'][$target]['value']))
                    {
                        $base = 'white';
                        $color = $skin_config['custom_options'][$target]['value'];
                        $base_style = 'data-color="'.$base.'" data-font-color="'.h($color).'"';
                        $color_style = 'data-color="'.h($color).'" data-font-color="'.$base.'"';

                        $formhtml = '
          <div class="btn-group qhm-skin-customizer-color" data-toggle="buttons" data-color-type="paint" data-paint-target="'.h($target).'">
            <label class="btn qhm-btn-default qhm-btn-sm'.($conf['value'] ? ' active' : '').'" '.$base_style.'><input type="radio" name="'.h($key).'" value="1"'.($conf['value'] ? ' checked' : '').'> A </label>
            <label class="btn qhm-btn-default qhm-btn-sm'.($conf['value'] ? '' : ' active').'" '.$color_style.'><input type="radio" name="'.h($key).'" value="0"'.($conf['value'] ? '' : ' checked').'> A </label>
          </div>
';
                    }
                }
                else
                {
                    $formhtml = '
      <div class="btn-group" data-toggle="buttons" data-type="if">
        <label class="btn qhm-btn-default qhm-btn-sm'.($conf['value'] ? ' active' : '').'"><input type="radio" name="'.h($key).'" value="1"'.($conf['value'] ? ' checked' : '').'> ON</label>
        <label class="btn qhm-btn-default qhm-btn-sm'.($conf['value'] ? '' : ' active').'"><input type="radio" name="'.h($key).'" value="0"'.($conf['value'] ? '' : ' checked').'> OFF</label>
      </div>
';
                }

                if ($formhtml != '')
                {
                    $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                }
                break;
            
            case 'select_img':
                $haik_bg_images = file_get_contents("http://hokuken.sakura.ne.jp/qhmhaik/get_images.php");
                $haik_bg_images = unserialize($haik_bg_images);

                $is_custom_image = (is_url($conf['value'], TRUE, TRUE) || $conf['value'] == '') ? FALSE : TRUE;
                $bg_basename = basename($conf['value']);

                $param = array(
                    'cmd'   => 'skin_customizer',
                    'phase' => 'file_upload',
                    'param_name' => h($key),
                );
                $formhtml = '
                <span class="pull-right"><small>選択中の画像：</small><span class="qhm-btn-default qhm-btn-sm" data-image="'.h($conf['value']).'" data-target="#qhm_skin_customizer_'.h($key).'_select_img">&nbsp;</span></span>
                <button id="qhm_skin_customizer_'.h($key).'_select_img" type="button" class="qhm-btn-info qhm-btn-sm" data-toggle="modal" data-target="#qhm_skin_customizer_'.h($key).'_modal">
                  <i class="text-lg glyphicon glyphicon-paperclip"></i>
                  <span>画像の選択</span>
                  <input type="hidden" name="'.h($key).'" value="'.h($conf['value']).'">
                </button>
';

                $tmpl_file = PLUGIN_DIR . 'skin_customizer/select_img_modal.html';
                ob_start();
                include($tmpl_file);
                $modal[] = ob_get_clean();
                $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                break;

            case 'select_texture':
                $haik_bg_images = file_get_contents("http://hokuken.sakura.ne.jp/qhmhaik/get_textures.php");
                $haik_bg_images = unserialize($haik_bg_images);

                $is_custom_image = (is_url($conf['value'], TRUE, TRUE) || $conf['value'] == '') ? FALSE : TRUE;
                $bg_basename = basename($conf['value']);

                $param = array(
                    'cmd'   => 'skin_customizer',
                    'phase' => 'file_upload',
                    'param_name' => h($key),
                );
                $formhtml = '
                <span class="pull-right"><small>選択中の画像：</small><span class="qhm-btn-default qhm-btn-sm" data-image="'.h($conf['value']).'" data-target="#qhm_skin_customizer_'.h($key).'_select_texture">&nbsp;</span></span>
                <button id="qhm_skin_customizer_'.h($key).'_select_texture" type="button" class="qhm-btn-info qhm-btn-sm" data-toggle="modal" data-target="#qhm_skin_customizer_'.h($key).'_modal">
                  <i class="text-lg glyphicon glyphicon-paperclip"></i>
                  <span>画像の選択</span>
                  <input type="hidden" name="'.h($key).'" value="'.h($conf['value']).'">
                </button>
';

                $tmpl_file = PLUGIN_DIR . 'skin_customizer/select_texture_modal.html';
                ob_start();
                include($tmpl_file);
                $modal[] = ob_get_clean();
                $html[] = sprintf($tmpl, $group_attr, h($conf['title']), $formhtml);
                break;
        }
    }

    $custom_html =  '
<a class="btn btn-link pull-left qhm-skin-customizer-menu-toggle" data-toggle="collapse" href=".qhm-skin-customizer-menu">カスタマイズ</a>
<div class="qhm-skin-customizer-menu collapse">
  <form action="'.h($script).'" class="form-horizontal" method="post" enctype="multipart/form-data">
    <input type="hidden" name="cmd" value="skin_customizer" />
    <div class="qhm-skin-customizer-body container-fluid">
'.join('', $html).'
    </div>
    <div class="qhm-skin-customizer-footer">
      <div class="form-group">
        <div class="col-sm-8 col-sm-offset-4 text-left">
          <input type="submit" class="qhm-btn-primary btn-sm" name="save" value="保存" />
          <input type="submit" class="btn btn-link btn-sm" name="reset" onclick="return confirm(\'設定したデータを初期化します。\nアップロードした画像も削除します。\n\nよろしいですか？\');" value="リセット" />
        </div>
      </div>
    </div>
  </form>
</div>
';


    $modal_html = join('', $modal);
    $qt->appendv('lastscript', $modal_html);

    $open = 0;
    if (isset($_SESSION['temp_design_customizer']))
    {
        $open = $_SESSION['temp_design_customizer'];
        unset($_SESSION['temp_design_customizer']);
    }

    $colors = file_get_contents(PLUGIN_DIR.'skin_customizer/colors.json');

    $data = array(
        'script' => $script,
        'open' => $open,
        'file_upload_postdata' => array(
          array(
            'name'=> 'cmd',
            'value' =>'skin_customizer'
          ),
          array(
            'name'=> 'phase',
            'value' =>'file_upload'
          ),
        ),
        'palette' => json_decode($colors, false),
    );

    $skin_dir = SKIN_DIR . $skin_name;
    if (file_exists($skin_dir.'/theme_colors.json'))
    {
        $theme_colors = file_get_contents($skin_dir.'/theme_colors.json');
        $data['theme_colors'] = json_decode($theme_colors, false);
    }

    $data_json = json_encode($data);

    $js = '
<link rel="stylesheet" href="js/jquery.fileupload.css" />
<link rel="stylesheet" href="'.PLUGIN_DIR.'skin_customizer/color_picker.css" />
<link rel="stylesheet" href="'.PLUGIN_DIR.'skin_customizer/skin_customizer.css" />
<script type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="js/jquery.fileupload.js"></script>
<script type="text/javascript">
  var skin_customizer = '.$data_json.';
</script>
<script type="text/javascript" src="'.PLUGIN_DIR.'skin_customizer/color_picker.js"></script>
<script type="text/javascript" src="'.PLUGIN_DIR.'skin_customizer/skin_customizer.js"></script>
';
    $qt->appendv('beforescript', $js);

    return $custom_html;
}
