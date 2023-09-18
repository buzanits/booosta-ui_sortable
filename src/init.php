<?php
namespace booosta\ui_sortable;

use \booosta\Framework as b;
b::add_module_trait('webapp', 'ui_sortable\webapp');

trait webapp
{
  protected $table_sortable = false;
  protected $table_sort_ajaxurl;
  protected $subtable_sortable = false;
  protected $subtable_sort_ajaxurl;

  protected $sortclassname, $sortclause;

  protected function webappinit_ui_sortable()
  {
    $this->sortclassname = $this->sortclassname ?? $this->editclassname ?? $this->classname ?? $this->name;
  }

  protected function preparse_ui_sortable()
  {
    $lib = 'vendor/npm-asset/html5sortable/dist';
    $csslib = 'vendor/booosta/ui_sortable/src';

    if($this->moduleinfo['ui_sortable']):
      $this->add_includes("<script type='text/javascript' src='{$this->base_dir}$lib/html5sortable.min.js'></script>
<link rel='stylesheet' type='text/css' href='{$this->base_dir}$csslib/sortable.css' media='screen' />");
    endif;
  }

  protected function replace_sortvar($varname)
  {
    if(key_exists($varname, $this->VAR)) return $this->VAR[$varname];
    if($this->$varname) return $this->$varname;
    return '';
  }

  protected function action_sort_sortable()
  {
    #b::debug("sortclause: $this->sortclause");
    $sortclause = str_replace('{id}', $this->id, $this->sortclause);
    $sortclause = preg_replace_callback('/\{([A-Za-z0-9_]+)\}/', function($m) { return $this->replace_sortvar($m[1]); }, $sortclause);
    $sortclause = $sortclause ?: '1';
    #debug("this->sortclause: $this->sortclause");
    #b::debug("sortclause: $sortclause");
    #debug("sortclassname: {$this->sortclassname}");

    // reorder numbers to not have gaps in numeration
    $i = 1;
    $lines = $this->DB->query_value_set("select id from `{$this->sortclassname}` where $sortclause order by ordernum, id");
    foreach($lines as $line):
      $this->DB->query("update `{$this->sortclassname}` set ordernum='$i' where id='$line'");
      $i++;
    endforeach;

    $origin = intval($this->VAR['origin']) + 1;   // ordernum is 1 based
    $destination = intval($this->VAR['destination']) + 1;
    $first = min($origin, $destination);
    $last = max($origin, $destination);
    $direction = $destination <=> $origin;   // 1 = move up, -1 = move down
    #b::debug("sort: $origin -> $destination in id $this->id");

    $this->DB->query("update `{$this->sortclassname}` set ordernum='-1' where $sortclause and ordernum='$origin'");
    $this->DB->query("update `{$this->sortclassname}` set ordernum=ordernum-$direction where $sortclause and ordernum between $first and $last");
    $this->DB->query("update `{$this->sortclassname}` set ordernum='$destination' where $sortclause and ordernum='-1'");

    \booosta\ajax\Ajax::print_response('result', '');
    $this->no_output = true;
  }
}
