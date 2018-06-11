<?php

/**
 * Class MaterialViewModel
 */
class MaterialViewModel extends ViewModel {
	/**
	 * 定义视图模型包含的字段
	 */
	public $viewFields = array(
		'Material' => array('marterial_id', 'marterial_name','marterial_type', 'marterial_status', 'create_time', '_type'=>'LEFT'),
		'MaterialHistory' => array('create_time' => 'history_time', 'material_id', 'COUNT(MaterialHistory.material_id)' => 'use_time', '_on' => 'Material.marterial_id = MaterialHistory.material_id'),
	);
}