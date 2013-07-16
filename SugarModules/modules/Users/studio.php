<?php
$GLOBALS['studioDefs']['Users'] = array(
'LBL_DETAILVIEW'=>array(
'template'=>'xtpl',
'template_file'=>'custom/modules/Users/DetailView.html',
'php_file'=>'custom/modules/Users/DetailView.php',
'type'=>'DetailView',
),
'LBL_EDITVIEW'=>array(
'template'=>'xtpl',
'template_file'=>'custom/modules/Users/EditView.html',
'php_file'=>'custom/modules/Users/EditView.php',
'type'=>'EditView',
),
'LBL_LISTVIEW'=>array(
'template'=>'listview',
'meta_file'=>'custom/modules/Users/metadata/listviewdefs.php',
'type'=>'ListView',
),
'LBL_SEARCHFORM'=>array(
'template'=>'xtpl',
'template_file'=>'custom/modules/Users/SearchForm.html',
'php_file'=>'custom/modules/Users/ListView.php',
'type'=>'SearchForm',
),

);
?>