<?php

$engine['work_order']=array(
	'require_password'=>false,
	'allow_skip_confirm'=>true,
	'enable_staff_alerts'=>true,
	'enable_staff_alerts_view'=>true,
	'child_records'=>array('work_order_comment'),
	'list_fields'=>array('work_order_id','added_at','work_order_status_code','work_order_category_code','housing_project_code','housing_unit_code','title','description'),
	'fields'=>array(
		'priority'=>array(
			'valid'=>array(
				'($x<=5) and ($x>=1)'=>'{$Y} must be between 1 and 5'
			)
		),
		'title'=>array(
			'row_before'=> 'bigger(bold("Main Info"))'
		),
		'agency_project_code'=>array(
			'default'=>'FACILITY',
			'row_before'=> 'bigger(bold("Categories"))'
		),
		'target_date'=>array(
			'row_before'=>'bigger(bold("Dates and Hours"))'
		),
		'work_order_status_code'=>array(
			'show_lookup_code'=>'DESCRIPTION',
			'label'=>'Status'
		),
		'work_order_category_code'=>array(
			'show_lookup_code'=>'DESCRIPTION',
			'label'=>'Category'
		),
		'assigned_to'=>array('data_type'=>'staff'),
		'work_order_id'=>array('label'=>'ID'),
		'housing_project_code'=>array(
			 'label'=>'Building',
			 'label_list'=>'Project',
			 'display_edit'=>'display',
			 'java'=> array(
				 'on_event'=> array(
					 'populate_on_select'=> array(
						'populate_field'=>'housing_unit_code',
						'table'=>'housing_unit_current'
					)
				 )
			 )
		),
		'housing_unit_code'=>array(
		   'show_lookup_code'=>'CODE',
		   'label'=>'Unit',
		   'is_html'=>true,
		   'value'=>'link_unit_history($x,true,false)'
	   )
	)
);



