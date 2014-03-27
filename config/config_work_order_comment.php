<?php

$engine['work_order_comment']=array(
	'require_password'=>false,
	'allow_skip_confirm'=>true,
	'list_fields'=>array('work_order_comment_id','added_at','added_by','work_order_id','comment'),
	'fields'=>array(

		'work_order_comment_id'=>array(
			'label'=>'ID'
		),

		'work_order_id'=>array(
				'display'=>'display',
				'value_format'=>'elink_value("work_order",$x_raw)',
				'is_html'=>true
		)
	)
);



