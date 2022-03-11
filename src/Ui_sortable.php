<?php
namespace booosta\ui_sortable;

use \booosta\Framework as b;
b::init_module('ui_sortable');

class UI_Sortable extends \booosta\ui\UI
{
  use moduletrait_ui_sortable;

  protected $content = [];
  protected $ajaxurl;

  public function __construct($name = null, $content = null, $ajaxurl = null)
  {
    parent::__construct();
    $this->id = "ui_sortable_$name";
    if($content !== null) $this->content = $content;

    if($ajaxurl === true) $this->ajaxurl = '?action=sort_sortable';
    elseif($ajaxurl !== null) $this->ajaxurl = $ajaxurl;
  }

  public function after_instanciation()
  {
    parent::after_instanciation();

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\Webapp")):
      $this->topobj->moduleinfo['ui_sortable'] = true;
    endif;
  }


  public function set_content($item) { $this->content = $item; }
  public function add_content($item) { $this->content[] = $item; }
  public function set_ajaxurl($ajaxurl) { $this->ajaxurl = $ajaxurl; }

  public function get_htmlonly() { 
    $result = '<ul class="js-sortable">';
    foreach($this->content as $content):
      if(is_object($content) && method_exists($content, 'get_html')) $code = $content->get_html();
      else $code = $content;

      $result .= "<li>$content</li>";
    endforeach;

    return $result . '</ul>';
  }

  public function get_js()
  {
    if($this->ajaxurl):
      $separator = strstr($this->ajaxurl, '?') ? '&' : '?';
      $ajax = "[0].addEventListener('sortupdate', function(e) 
               { $.ajax('$this->ajaxurl{$separator}origin=' + e.detail.origin.index + '&destination=' + e.detail.destination.index); })";
    endif;

    $code = "sortable('.js-sortable')$ajax;";

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\webapp")):
      $this->topobj->add_jquery_ready($code);
      return '';
    else:
      return "\$(document).ready(function(){ $code });";
    endif;
  }
}
