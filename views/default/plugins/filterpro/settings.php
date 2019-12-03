<?php 

// SW Social Web LLC
// SW Social Web Rules!!!

$filter_setting = $vars['entity']->filter_filter;
$jpg_filter_setting = $vars['entity']->jpg_filter;
$word_filter_setting = $vars['entity']->word_filter;
$excel_filter_setting = $vars['entity']->excel_filter;
$mp3_filter_setting = $vars['entity']->mp3_filter;
$pdf_filter_setting = $vars['entity']->pdf_filter;


?>


<p>
  <b>Utilizar el Filtro de Archivos?</b>

<?php

echo elgg_view('input/dropdown',array(
'name' => 'params[filter_filter]', 
'options_values'=> array( '0' => '  ', '1'=>'Yes','2'=>'No'),
'value'=> $filter_setting));

 ?>
</p>
<?php

if ($filter_setting == 1)
{ 
?>
<p>
  <b>Permitir archivos JPG?</b>

<?php

echo elgg_view('input/dropdown',array(
'name' => 'params[jpg_filter]', 
'options_values'=> array( '0' => '  ', '1'=>'Yes','2'=>'No'),
'value'=> $jpg_filter_setting));

 ?>
</p>

<p>
  <b>Permitir documentos de Word?</b>

<?php

echo elgg_view('input/dropdown',array(
'name' => 'params[word_filter]', 
'options_values'=> array( '0' => '  ', '1'=>'Yes','2'=>'No'),
'value'=> $word_filter_setting));

 ?>
</p>

<p>
  <b>Permitir documentos Excel?</b>

<?php

echo elgg_view('input/dropdown',array(
'name' => 'params[excel_filter]', 
'options_values'=> array( '0' => '  ', '1'=>'Yes','2'=>'No'),
'value'=> $excel_filter_setting));

 ?>
</p>

<p>
  <b>Permitir archivos PDF?</b>

<?php

echo elgg_view('input/dropdown',array(
'name' => 'params[pdf_filter]', 
'options_values'=> array( '0' => '  ', '1'=>'Yes','2'=>'No'),
'value'=> $pdf_filter_setting));

 ?>
</p>


<p>
  <b>Permitir archivos MP4?</b>

<?php

echo elgg_view('input/dropdown',array(
'name' => 'params[mp3_filter]', 
'options_values'=> array( '0' => '  ', '1'=>'Yes','2'=>'No'),
'value'=> $mp3_filter_setting));

 ?>
</p>


<?php
}
?>