<?php
$listViewDefs ['Calls'] =
array (
  'SET_COMPLETE' =>
  array (
    'width' => '1%',
    'label' => 'LBL_LIST_CLOSE',
    'link' => true,
    'sortable' => false,
    'default' => true,
    'related_fields' =>
    array (
      0 => 'status',
    ),
  ),
  'DIRECTION' =>
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_DIRECTION',
    'link' => false,
    'default' => true,
  ),
  'NAME' =>
  array (
    'width' => '20%',
    'label' => 'LBL_LIST_SUBJECT',
    'link' => true,
    'default' => true,
  ),
  'ASTERISK_CALLER_ID_C' =>
  array (
    'width' => '10%',
    'label' => 'LBL_ASTERISK_CALLER_ID',
    'default' => true,
    'customCode' => '<span class="asterisk_phoneNumber" id="">{$ASTERISK_CALLER_ID_C}</span>&nbsp;&nbsp;',
  ),
  'CONTACT_NAME' =>
  array (
    'width' => '20%',
    'label' => 'LBL_LIST_CONTACT',
    'link' => true,
    'id' => 'CONTACT_ID',
    'module' => 'Contacts',
    'default' => true,
    'ACLTag' => 'CONTACT',
  ),
  'PARENT_NAME' =>
  array (
    'width' => '20%',
    'label' => 'LBL_LIST_RELATED_TO',
    'dynamic_module' => 'PARENT_TYPE',
    'id' => 'PARENT_ID',
    'link' => true,
    'default' => true,
    'sortable' => false,
    'ACLTag' => 'PARENT',
    'related_fields' =>
    array (
      0 => 'parent_id',
      1 => 'parent_type',
    ),
  ),
  'DATE_START' =>
  array (
    'width' => '15%',
    'label' => 'LBL_LIST_DATE',
    'link' => false,
    'default' => true,
    'related_fields' =>
    array (
      0 => 'time_start',
    ),
  ),
  'ASSIGNED_USER_NAME' =>
  array (
    'width' => '2%',
    'label' => 'LBL_LIST_ASSIGNED_TO_NAME',
    'default' => true,
  ),
  'STATUS' =>
  array (
    'width' => '10%',
    'label' => 'LBL_STATUS',
    'link' => false,
    'default' => false,
  ),
);
?>
