<?php
return array(
	'ctrl' => array(
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'title' => 'Cacheopt record',
		'delete' => 'deleted',
		'searchFields' => 'title'
	),
	'interface' => array(
		'always_description' => 0,
		'showRecordFieldList' => 'title'
	),
	'columns' => array(
		'title' => array(
			'label' => 'Title',
			'config' => array(
				'type' => 'input',
				'eval' => 'required',
				'size' => '50',
				'max' => '256'
			)
		),
	),
	'types' => array(
		'0' => array(
			'showitem' => 'title'
		),
	),
	'palettes' => array(),
);
