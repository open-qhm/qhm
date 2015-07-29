<?php
/**
 *   Progress Bar Plugin
 *   -------------------------------------------
 *   /haik-contents/plugin/progress_bar.inc.php
 *   
 *   Copyright (c) 2013 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2013/08/22
 *   modified :
 *   
 *   進捗をバーで表す。
 *   
 *   Usage :
 *     #progress_bar(60)
 *   
 */

function plugin_progress_bar_convert()
{
    $args = func_get_args();
    $body = end($args);
    $progresses = array();
    if (strpos($body, "\r") !== FALSE)
    {
        array_pop($args);
        $progresses = explode("\r", $body);
    }

    $striped = FALSE;
    $animated = FALSE;
    $latency = 0;

    // Parse wrapper params
    $a_progress_params = array();
    while ($arg = trim(array_shift($args)))
    {
        switch ($arg)
        {
            case is_numeric($arg) ? true : false:
                $a_progress_params[0] = $arg;
                break;
            case 'success':
            case 'info':
            case 'warning':
            case 'danger':
                $a_progress_params[1] = $arg;
                break;
            case 'striped':
            case 'stripe':
                $striped = TRUE;
                break;
            case 'active':
            case 'animated':
                $striped = TRUE;
                $animated = TRUE;
                break;
            case preg_match('/\Alatency(?:=(\d+))?\z/', $arg, $mts) ? true : false:
                $latency = isset($mts[1]) ? $mts[1] : 1000;
                break;
            default: //label
                $a_progress_params[2] = $arg;
        }
    }

    $progress_bar = new QHM_Plugin_ProgressBar($striped, $animated);

    if (count($progresses) === 0)
    {
        $progresses[] = $a_progress_params;
    }

    foreach ($progresses as $progress_params_str)
    {
        if (trim($progress_params_str) === '') continue;
        if (is_array($progress_params_str))
        {
            $value = & $progress_params_str[0];
            $type = & $progress_params_str[1];
            $label = & $progress_params_str[2];
        }
        else
        {
            list($value, $type, $label) = array_pad(explode(',', $progress_params_str, 3), 3, NULL);
        }
        $progress = new QHM_Plugin_ProgressBar_Progress($value, $type, $label);
        if ($label !== '' && $label !== NULL)
        {
            $progress->revealLabel();
        }
        if ($latency)
        {
            $progress->setTransitionLatency($latency);
        }
        $progress_bar->addProgress($progress);
    }

    if ($latency)
    {
        $addjs = '
<script>
$(window).load(function(){
  $(".progress-bar[data-latency]").each(function(){
    var $progress = $(this);
    var data = $progress.data();
    setTimeout(function(){
      $progress.attr("aria-valuenow", data.value).css("width", data.value + "%");
    }, data.latency);
  });
});
</script>
';
        $qt = get_qt();
        $qt->appendv_once('plugin_progress_bar', 'beforescript', $addjs);
    }

    return $progress_bar->render();
}

class QHM_Plugin_ProgressBar {

    protected $progresses;

    protected $striped;
    protected $animated;

    public function __construct($striped = FALSE, $animated = FALSE)
    {
        $this->progresses = array();
        $this->striped = $striped;
        $this->animated = $animated;
    }

    public function addProgress(QHM_Plugin_ProgressBar_Progress $progress)
    {
        $this->progresses[] = $progress;
    }

    public function render()
    {
        $progresses = array();
        foreach ($this->progresses as $progress)
        {
            $progresses[] = $progress->render();
        }

        $class_attr = 'progress';
        if ($this->striped) $class_attr .= ' progress-striped';
        if ($this->animated) $class_attr .= ' active';
        return '<div class="'.$class_attr.'">'. join("\n", $progresses) .'</div>' . "\n";
    }
}

class QHM_Plugin_ProgressBar_Progress {

    protected $value;

    protected $type;

    protected $label;
    protected $labelVisible;

    protected $transitionLatency;

    public function __construct($value, $type = NULL, $label = '')
    {
        $this->value = $value ? $value : 100;
        $this->type = $type;
        $this->label = $label;
        $this->labelVisible = FALSE;
        $this->transitionLatency = 0;

        if ($this->label === '' OR $this->label === NULL)
        {
            $this->setAutoLabel();
        }
    }

    protected function setAutoLabel()
    {
        $this->label = "{$this->value}%";
    }

    public function revealLabel()
    {
        $this->labelVisible = true;
    }

    public function setTransitionLatency($latency)
    {
        if ($latency > 0)
        {
            $this->transitionLatency = $latency;
        }
    }

    public function render()
    {
        $label = $this->label;
        if ( ! $this->labelVisible)
        {
            $label = '<span class="sr-only">'.$label.'</span>';
        }
        $class_attr = 'progress-bar';
        if ($this->type)
        {
            $class_attr .= ' progress-bar-' . $this->type;
        }
        $transition_attr = '';
        if ($this->transitionLatency)
        {
            $transition_attr = ' data-latency="'.$this->transitionLatency.'" data-value="'.$this->value.'"';
            $this->value = 0;
        }
        return <<< EOD
<div class="{$class_attr}" role="progressbar" aria-valuenow="{$this->value}" aria-valuemin="0" aria-valuemax="100" style="width: {$this->value}%;"{$transition_attr}>{$label}</div>
EOD;
    }
}
