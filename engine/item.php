<?php
class Item
{
	public $item;
	private $epic;
	private $class_weap_multiplier = array(2.3, 5.5, 3);
	private $class_weap_multiplier_armor = array(15, 3, 8);
	
	function __construct()
	{
		$this->item = array(
			"id" => "NULL",
			"item_type" => "0",
			"item_id" => "0",
			"enchant" => "0",
			"enchant_power" => "0",
			"dmg_min" => "0",
			"dmg_max" => "0",
			"attr_type_1" => "0",
			"attr_type_2" => "0",
			"attr_type_3" => "0",
			"attr_val_1" => "0",
			"attr_val_2" => "0",
			"attr_val_3" => "0",
			"gold" => "0",
			"mush" => "0",
			"toilet" => "0",
			"slot" => "0",
			"owner_id" => "0"
		);
	}
	
	public function get_id_item($class, $type, $epic)
	{
		$item_id = 0;
		$event_special = Server::event_special();
		
		switch($type)
		{
			case 1:
				if($class == 1)
				{
					$item_id = rand(1, 30);
					if($epic)
					{
						$item_id = rand(50 , (60 + $event_special));
						if($item_id > 60)
						{
							if($event_special > 3)
							{
								$item_id = 60 + rand(4, 6);
							}
							else
							{
								$item_id = 60 + $event_special;
							}
						}
					}
				}
				else
				{
					$item_id = rand(1, 10);
					if($epic)
					{
						$item_id = rand(50 , (60 + $event_special));
						if($item_id > 60)
						{
							if($event_special > 3)
							{
								$item_id = 60 + rand(4, 6);
							}
							else
							{
								$item_id = 60 + $event_special;
							}
						}
					}
				}
			break;
			case 2:
				$item_id = rand(1, 10);
				if($epic)
				{
					$item_id = rand(50 , (60 + $event_special));
					if($item_id > 60)
					{
						if($event_special == 4)
						{
							$item_id = 60 + rand(4, 6);
						}
						else
						{
							$item_id = 60 + $event_special;
						}
					}
				}
			break;
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
				$item_id = rand(1, 10);
				if($epic)
				{
					$item_id = rand(50, 58);
					if($event_special != 0)
					{
						$item_id = rand((60 + $event_special), (60 + ($event_special == 4 ? 6 : $event_special)));
					}
				}
			break;
			case 8:
				$item_id = rand(1, 21);
				if($epic)
				{
					$item_id = rand(50 , (60 + $event_special));
					if($item_id > 60)
					{
						if($event_special > 3)
						{
							$item_id = 60 + rand(4, 6);
						}
						else
						{
							$item_id = 60 + $event_special;
						}
					}
				}
			break;
			case 9:
				$item_id = rand(1, 16);
				if($epic)
				{
					$item_id = rand(50 , (60 + $event_special));
					if($item_id > 60)
					{
						if($event_special > 3)
						{
							$item_id = 60 + rand(4, 6);
						}
						else
						{
							$item_id = 60 + $event_special;
						}
					}
				}
			break;
			case 10:
				$item_id = rand(1, 37);
				if($epic)
				{
					$item_id = rand(50 , (60 + $event_special));
					if($item_id > 60)
					{
						if($event_special > 3)
						{
							$item_id = 60 + rand(4, 6);
						}
						else
						{
							$item_id = 60 + $event_special;
						}
					}
				}
			break;
		}
		
		if($type < 8)
		{
			$item_id += (($class - 1) * 1000);
		}
		
		return $item_id;
	}
	
	public function gen_item($type, $lvl, $class)
	{
		if(server::event() == 2 OR server::event() == 5)
		{
			$this->epic = rand(1, 99) < 25 ? true : false;
		}
		else
		{
			$this->epic = rand(1, 99) < 3 ? true : false;
		}
		
		if($type == 2 AND $class != 1)
		{
			$type = [1, 3, 4, 5, 6, 7][rand(0, 5)];
		}
		
		
		if($lvl == 1)
		{
			$this->epic = false;
		}
		
		if($type <= 10)
		{
			if($type == 1)
			{
				$dmg = 1 + floor($this->class_weap_multiplier[$class - 1] * $lvl);
				$minmax = 2 + floor($lvl * rand(5, 100) / 100);

				$this->item['dmg_min'] = $dmg - $minmax;
				$this->item['dmg_max'] = $dmg + $minmax;
			}
			else if($type == 2)
			{
				if($lvl < 5)
				{
					$this->item['dmg_min'] = rand(5, 10);
				}
				else if($lvl >= 5 AND $lvl < 25 )
				{
					$this->item['dmg_min'] = rand(5, 15);
				}
				else if($lvl >= 25)
				{
					$this->item['dmg_min'] = rand(15, 25);
				}
			}
			
			if($this->epic)
			{
				if(rand(1, 4) > 1)
				{
					$statVal = rand($lvl, ($lvl * rand(($lvl / 8), ($lvl / 14))));

					$this->item['attr_type_1'] = [1, 3, 2][$class - 1];
					$this->item['attr_type_2'] = 4;
					$this->item['attr_type_3'] = 5;
					$this->item['attr_val_1'] = $statVal;
					$this->item['attr_val_2'] = $statVal;
					$this->item['attr_val_3'] = $statVal;
				}
				else
				{
					$statVal = 4 + round($lvl * 1.27);
					$z = round(3 + $lvl * 0.05);
					$statVal += round(rand($z / -2, $z / 2));

					$this->item['attr_type_1'] = 6;
					$this->item['attr_val_1'] = $statVal;
				}
				
				$this->item['mush'] = 15;
			}
			else
			{
				$statVal = rand($lvl, ($lvl * rand(($lvl / 8), ($lvl / 18))));
				$this->item['attr_type_1'] = rand(1,5);
				$this->item['attr_val_1'] = $statVal;
				if(rand(1,3) == 2)
				{
					$statVal = rand($lvl, ($lvl * rand(($lvl / 8), ($lvl / 18))));
					$attr_type = rand(1,5);
					
					while($attr_type == $this->item['attr_type_1'])
					{
						$attr_type = rand(1,5);
					}
					
					$this->item['attr_type_2'] = $attr_type;
					$this->item['attr_val_2'] = $statVal;
					$this->item['mush'] = 5;
				}
			}
			
			$this->item["gold"] = rand($lvl * $lvl * 2 * 0.9, $lvl * $lvl * 5 * 1.1);
		}
		
		switch($type)
		{
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
			case 9:
			case 10:
				$this->item['item_id'] = $this->get_id_item($class, $type, $this->epic);
			break;
			case 11:
				//Dungeon key genetare in other function
			break;
			case 12:
			
				if(rand(0, 99) > 15)
				{
					if($lvl < 10)
					{
						$this->item['item_id'] = rand(1, 5);
					}
					else if($lvl < 25)
					{
						$this->item['item_id'] = rand(1, 10);
					}
					else
					{
						$this->item['item_id'] = rand(1, 15);
					}

					$this->item['attr_type_1'] = 11;
					$this->item['attr_val_1'] = 72;

					if($this->item['item_id'] < 6)
					{
						$this->item['attr_val_2'] = 5;
						$this->item['attr_type_2'] = $this->item['item_id'];
					}
					else if($this->item['item_id'] < 11)
					{
						$this->item['attr_val_2'] = 15;
						$this->item['attr_type_2'] = $this->item['item_id'] - 5;
					}
					else if($this->item['item_id'] < 16)
					{
						$this->item['attr_val_2'] = 25;
						$this->item['attr_type_2'] = $this->item['item_id'] - 10;
					}
				}
				else
				{
					$this->item['item_id'] = 16;

					$this->item['attr_type_1'] = 11;
					$this->item['attr_val_1'] = 168;

					$this->item['attr_type_2'] = 12;
					$this->item['attr_val_2'] = 25;

					$item['mush'] = (rand(0, 1) * 15);
				}
				
				$this->item["gold"] = rand($lvl * $lvl * 5 * 0.9, $lvl * $lvl * 10 * 1.1);
				
			break;
		}
		
		$this->item['item_type'] = $type;
		
		if($type > 2 AND $type < 8)
		{
			$this->item['dmg_min'] = $lvl * $this->class_weap_multiplier_armor[$class - 1] * rand(1.5, 1.7) + rand($lvl, ($lvl * 2));
		}
	}
	
	public function get_item($owner_id, $slot, $table)
	{
		
		$qry = $GLOBALS['db']->query("SELECT * FROM `".$table."` WHERE `owner_id` = ".$owner_id." AND `slot` = ".$slot."");
		
		if($qry->rowCount() == 1)
		{
			$this->item = $qry->fetch();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function get_slot_item($item_type)
	{
		switch($item_type)
		{
			case 1:
				return 9;
			break;
			case 2:
				return 10;
			break;
			case 3:
				return 2;
			break;
			case 4:
				return 4;
			break;
			case 5:
				return 3;
			break;
			case 6:
				return 1;
			break;
			case 7:
				return 6;
			break;
			case 8:
				return 5;
			break;
			case 9:
				return 7;
			break;
			case 10:
				return 8;
			break;
		}
	}
	
	public function get_enchant_id($id)
	{
		switch($id)
		{
			case 1:
				return 2;
			case 2:
				return 7;
			case 3:
				return 5;
			case 4:
				return 6;
			case 5:
				return 3;
			case 6:
				return "0";
			case 7:
				return 4;
			case 8:
				return 1;
			case 9:
				return 8;
		}
	}
	
	public function update_item($item, $table)
	{
		$GLOBALS['db']->query("UPDATE `".$table."` SET 
		`item_id` = ".$item['item_id'].",
		`item_type` = ".$item['item_type'].",
		`enchant` = ".$item['enchant'].",
		`enchant_power` = ".$item['enchant_power'].",
		`dmg_min` = ".$item['dmg_min'].",
		`dmg_max` = ".$item['dmg_max'].",
		`attr_type_1` = ".$item['attr_type_1'].",
		`attr_type_2` = ".$item['attr_type_2'].",
		`attr_type_3` = ".$item['attr_type_3'].",
		`attr_val_1` = ".$item['attr_val_1'].",
		`attr_val_2` = ".$item['attr_val_2'].",
		`attr_val_3` = ".$item['attr_val_3'].",
		`gold` = ".$item['gold'].",
		`mush` = ".$item['mush'].",
		`toilet` = ".$item['toilet'].",
		`slot` = ".$item['slot']."
		WHERE `owner_id` = ".$item['owner_id']."
		AND `id` = ".$item['id']."");
	}
	
	public function change_item($item, $lvl)
	{
		$old_class = round($item['item_id'] / 1000);
		$new_class = mt_rand(1, 3);
		$epic = ($item['item_id'] - (1000 * $old_class)) > 49 ? true : false;
		$item_id = $item['item_id'] - (1000 * $old_class);
		$item['toilet'] = 1;
		$item['gold'] = 0;
		$item['mush'] = 0;
		$this->item['dmg_min'] = 0;
		switch($item['item_type'])
		{
			case 1:
				$dmg = 1 + floor($this->class_weap_multiplier[$new_class - 1] * $lvl);
				$minmax = 2 + floor($lvl * rand(5, 100) / 100);
				$item['dmg_min'] = $dmg - $minmax;
				$item['dmg_max'] = $dmg + $minmax;
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
				$item['item_id'] = ($item_id + (1000 * ($new_class - 1)));
			case 9:
			case 10:
			case 11:
				if($item['attr_type_2']  == 0 AND $item['attr_type_1'] != 6)
				{
					$item['attr_type_1'] = rand(1, 5);
				}
				else if($item['attr_type_2'] > 3)
				{
					$item['attr_type_1'] = $new_class;
				}
			break;
		}
		
		if($item['item_type'] > 2 AND $item['item_type'] < 8)
		{
			$item['dmg_min'] = $lvl * $this->class_weap_multiplier_armor[$new_class - 1] * rand(1.5, 1.7) + rand($lvl, ($lvl * 2));
		}
		
		$this->item = $item;
	}
	
	public function insert_item($item, $table)
	{
		$GLOBALS['db']->exec("INSERT INTO `".$table."` (`id`, `item_type`, `item_id`, `enchant`, `enchant_power`, `dmg_min`, `dmg_max`, `attr_type_1`, `attr_type_2`, `attr_type_3`, `attr_val_1`, `attr_val_2`, `attr_val_3`, `gold`, `mush`, `toilet`, `slot`, `owner_id`)
		VALUES (
		'NULL', 
		".$item['item_type'].", 
		".$item['item_id'].", 
		".$item['enchant'].",
		".$item['enchant_power'].",
		".$item['dmg_min'].", 
		".$item['dmg_max'].", 
		".$item['attr_type_1'].", 
		".$item['attr_type_2'].", 
		".$item['attr_type_3'].", 
		".$item['attr_val_1'].", 
		".$item['attr_val_2'].", 
		".$item['attr_val_3'].", 
		".$item['gold'].", 
		".$item['mush'].", 
		".$item['toilet'].", 
		".$item['slot'].", 
		".$item['owner_id'].")");
	}
	
	public function delete_item($item, $table)
	{
		$GLOBALS['db']->exec("DELETE FROM `".$table."` WHERE `id` = ".$item['id']."");
	}
}
?>