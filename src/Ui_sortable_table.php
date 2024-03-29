<?php
namespace booosta\ui_sortable;


class Ui_sortable_table extends \booosta\tablelister\Tablelister
{
  protected $ajaxurl;
  protected $table2sort;

  public function __construct($data, $tabletags = true, $use_datatable = false, $ajaxurl = null)
  {
    parent::__construct($data, $tabletags, $use_datatable);

    $this->tbody_class = 'js-sortable-table';
    $this->tr_class = 'js-sortable-tr';

    if($ajaxurl) $this->ajaxurl = $ajaxurl;
  }

  public function after_instanciation()
  {
    parent::after_instanciation();

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\Webapp")):
      $this->topobj->moduleinfo['ui_sortable'] = true;
    endif;
  }

  public function set_ajaxurl($ajaxurl) { $this->ajaxurl = $ajaxurl; }
  public function set_table2sort($table2sort) { $this->table2sort = $table2sort; }

  public function get_html()
  {
    $this->tbody_class = "js-sortable-table-$this->id";
    return parent::get_html() . $this->get_js();
  }

  public function get_js()
  {
    if($this->ajaxurl):
      $separator = strstr($this->ajaxurl, '?') ? '&' : '?';
      $table2sort = $this->table2sort ? "table2sort=$this->table2sort{$separator}" : '';

      $ajax = "[0].addEventListener('sortupdate', function(e) 
               { $.ajax('$this->ajaxurl{$separator}{$table2sort}origin=' + e.detail.origin.index + '&destination=' + e.detail.destination.index); }); ";
    endif;

    $placeholder = $this->t('Drop row here');
    $code = "sortable('.js-sortable-table-$this->id', {
      items: 'tr.js-sortable-tr',
      placeholder: '<tr><td colspan=\"42\"><span class=\"center\">$placeholder</span></td></tr>',
      forcePlaceholderSize: false })$ajax";

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\webapp")):
      $this->topobj->add_jquery_ready($code);
      return '';
    else:
      return "\$(document).ready(function(){ $code });";
    endif; 
  }
}
