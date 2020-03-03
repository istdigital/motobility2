<?php
$mysqli = new mysqli("localhost","root","root","motobility");

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}
sfsdf


$attributes = [
 "Adjustable Armrests",
 "Adjustable Backrest",
 "Adjustable Seat Height",
 "Adjustable Tiller Angle",
 "Adjustable Tiller Height",
 "Anti-Tip Mechanism",
 "Armrests",
 "Battery",
 "Battery Range",
 "Charger",
 "Controller",
 "Dimensions",
 "Dimensions when folded",
 "Distance between armrests",
 "Drive Wheel",
 "Foot Room",
 "Front Height",
 "Front Wheel",
 "Ground Clearance",
 "High / Low Speed Selection",
 "Lights",
 "Lithium Battery",
 "Maximum Speed",
 "Mirrors",
 "Motor",
 "Number of Components",
 "Seat Dimensions",
 "Seat height from Deck",
 "Seat height from Floor",
 "Seat Padding",
 "Slope ability",
 "Suspension",
 "Swivel Seat",
 "Tiller Angle",
 "Total Height",
 "Total Length",
 "Total Width",
 "Turning Radius",
 "Tyres",
 "Weight Capacity",
 "Weight with Battery",
 "Wheel to Wheel",
 "Adjustable Armrests",
 "Adjustable Backrest",
 "Adjustable Seat Height",
 "Adjustable Tiller Angle",
 "Adjustable Tiller Height",
 "Anti-Tip Mechanism",
 "Armrests",
 "Battery",
 "Battery Range",
 "Charger",
 "Controller",
 "Dimensions",
 "Dimensions when folded",
 "Distance between armrests",
 "Drive Wheel",
 "Foot Room",
 "Front Height",
 "Front Wheel",
 "Ground Clearance",
 "High / Low Speed Selection",
 "Lights",
 "Lithium Battery",
 "Maximum Speed",
 "Mirrors",
 "Motor",
 "Number of Components",
 "Seat Dimensions",
 "Seat height from Deck",
 "Seat height from Floor",
 "Seat Padding",
 "Slope ability",
 "Suspension",
 "Swivel Seat",
 "Tiller Angle",
 "Total Height",
 "Total Length",
 "Total Width",
 "Turning Radius",
 "Tyres",
 "Weight Capacity",
 "Weight with Battery",
 "Wheel to Wheel",
];

foreach ($attributes as $attribute) 
{
	$code = strtolower(preg_replace("/ /i", "_",preg_replace("/-|\//i", "", $attribute)));
	//echo '<br/>';
	$label = $attribute;

	$mysqli -> query("INSERT INTO `eav_attribute` (`entity_type_id`, `attribute_code`, `attribute_model`, `backend_model`, `backend_type`, `backend_table`, `frontend_model`, `frontend_input`, `frontend_label`, `frontend_class`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`, `note`) VALUES
	(4,	'$code',	NULL,	NULL,	'varchar',	NULL,	NULL,	'text', '$label',	NULL,	NULL,	0,	1,	NULL,	0,	NULL);");



	$mysqli -> query("INSERT INTO `eav_entity_attribute` (`entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`)
	SELECT 4,	4,	25,	(SELECT `attribute_id` FROM `eav_attribute` WHERE `attribute_code` = '$code'),	1 ;");


	$mysqli -> query("INSERT INTO `catalog_eav_attribute` (`attribute_id`, `frontend_input_renderer`, `is_global`, `is_visible`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_used_for_price_rules`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `apply_to`, `is_visible_in_advanced_search`, `position`, `is_wysiwyg_enabled`, `is_used_for_promo_rules`, `is_required_in_admin_store`, `is_used_in_grid`, `is_visible_in_grid`, `is_filterable_in_grid`, `search_weight`, `additional_data`)
	SELECT  (SELECT `attribute_id` FROM `eav_attribute` WHERE `attribute_code` = '$code'),	NULL,	1,	1,	0,	0,	1,	1,	1,	0,	0,	0,	0,	NULL,	0,	0,	0,	0,	0,	0,	1,	0,	1,	NULL;");

}


$mysqli -> close();

