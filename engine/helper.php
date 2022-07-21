<?php
class Helper
{
	public $nick;
	public $lvl;
	public $race = 0;
	public $gender = 0;
	private $default_attr;
	public $attr_str;
	public $attr_agi;
	public $attr_int;
	public $attr_wit;
	public $attr_wit_all;
	public $attr_luck;
	public $dmg_min = 1;
	public $dmg_max = 2;
	public $hp;
	public $armor;
	public $class;
	public $bonus_reaction = 0;
	public $bonus_crit = 0;
	public $face = array(0, 391, 0, 0, 0, 0, 0, 0, 0, 0);
	private $class_weapon_multiplier = array(2.3, 5.5, 3);
	public $weapon = array(
			"has_weapon" => "0",
			"item_type" => "0",
			"item_id" => "0",
			"dmg_min" => "1",
			"dmg_max" => "2",
			"attr_type_1" => "0",
			"attr_type_2" => "0",
			"attr_type_3" => "0",
			"attr_val_1" => "0",
			"attr_val_2" => "0",
			"attr_val_3" => "0",
			"gold" => "0",
			"mush" => "0",
		);
	public $shield = array(
			"has_shield" => "0",
			"item_type" => "0",
			"item_id" => "0",
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
		);
	public $item;
	private $ID_Helper;
	private $user_id;
	public $lvl_player;
	private $items;
	private $item_stats = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
	
	function __construct($player, $ID_Helper)
	{
		global $db;
		
		$item = array("item_type" => "0", "item_id" => "0", "enchant" => "0", "enchant_power" => "0", "dmg_min" => "1", "dmg_max" => "2", "attr_type_1" => "0", "attr_type_2" => "0", "attr_type_3" => "0", "attr_val_1" => "0", "attr_val_2" => "0", "attr_val_3" => "0", "gold" => "0", "mush" => "0");
		$this->items = array(0 => $item, 1 => $item, 2 => $item, 3 => $item, 4 => $item, 5 => $item, 6 => $item, 7 => $item, 8 => $item, 9 => $item);
		
		$this->attr_str = explode(";", $player->data['copycat_str'])[$ID_Helper - 1];
		$this->attr_agi = explode(";", $player->data['copycat_agi'])[$ID_Helper - 1];
		$this->attr_int = explode(";", $player->data['copycat_int'])[$ID_Helper - 1];
		$this->attr_wit = explode(";", $player->data['copycat_wit'])[$ID_Helper - 1];
		$this->attr_luck = explode(";", $player->data['copycat_luck'])[$ID_Helper - 1];
		$this->lvl = explode(";", $player->data['copycat_lvl'])[$ID_Helper - 1];
		$this->ID_Helper = $ID_Helper;
		$this->user_id = $player->data['user_id'];
		$this->cost_upgrade_lvl = (5000 + 5000 * ($this->lvl - 1)) * 100;
		$this->lvl_player = $player->data['lvl'];
		$this->class = $ID_Helper;
		
		switch($ID_Helper)
		{
			case 1:
				$this->nick = "warrior";
				$this->default_attr = $this->attr_str;
			break;
			case 2:
				$this->nick = "mage";
				$this->default_attr = $this->attr_int;
			break;
			case 3:
				$this->nick = "hunter";
				$this->default_attr = $this->attr_agi;
			break;
		}
		
		for($i=0;$i<9;$i++)
		{
			$items_fetch = $db->query("SELECT * FROM `tower_helper_items` WHERE `owner_id` = ".$this->user_id." AND `helper` = ".$this->ID_Helper." AND `slot` = ".($i+1)."");
			if($items_fetch->rowCount() >= 1)
			{
				$this->items[$i] = $items_fetch->fetch();
				$item = $this->items[$i];
				$this->item_stats[$item['attr_type_1']] += $item['attr_val_1'];
				$this->item_stats[$item['attr_type_2']] += $item['attr_val_2'];
				$this->item_stats[$item['attr_type_3']] += $item['attr_val_3'];
				
				if($item['item_type'] == 1)
				{
					$this->weapon = $item;
					$this->weapon['has_weapon'] = 1;
				}
				else if($item['item_type'] > 2 AND $item['item_type'] < 6)
				{
					$this->armor += $item['dmg_min'];
				}
			}
		}
		
		$this->dmg_min = $this->weapon['dmg_min'] * (1 + $this->default_attr / 10);
		$this->dmg_max = $this->weapon['dmg_max'] * (1 + $this->default_attr / 10);
		
		$this->attr_wit_all += $this->attr_wit + $this->item_stats[5] + $this->item_stats[6];
		
		$this->hp = $this->attr_wit_all * [5,2,4][$ID_Helper - 1] * ($this->lvl + 1);
		
		$this->face[1] = "-" . ($this->face[1] + ($ID_Helper - 1));
	}
	
	public function get_ret()
	{
		$ret = array();
		
		$ret[] = $this->lvl;
		$ret[] = $this->ID_Helper;
		$ret[] = 0;
		$ret[] = 0;
		$ret[] = $this->attr_str;
		$ret[] = $this->attr_agi;
		$ret[] = $this->attr_int;
		$ret[] = $this->attr_wit;
		$ret[] = $this->attr_luck;
		
		$ret[] = $this->item_stats[1] + $this->item_stats[6];
		$ret[] = $this->item_stats[2] + $this->item_stats[6];
		$ret[] = $this->item_stats[3] + $this->item_stats[6];
		$ret[] = $this->item_stats[4] + $this->item_stats[6];
		$ret[] = $this->item_stats[5] + $this->item_stats[6];
			
		$ret[] = 0;
		$ret[] = 0;
		$ret[] = 0;
		$ret[] = 0;
		$ret[] = 0;
			
		$ret[] = $this->armor;
			
		$ret[] = $this->dmg_min;
		$ret[] = $this->dmg_max;
			
		for($i=0;$i<10;$i++)
		{
			$ret_item = $this->items[$i];
			$ret[] = $ret_item["item_type"] + $ret_item["enchant"];
			$ret[] = $ret_item["item_id"] + $ret_item["enchant_power"];
			$ret[] = $ret_item["dmg_min"];
			$ret[] = $ret_item["dmg_max"];
			$ret[] = $ret_item["attr_type_1"];
			$ret[] = $ret_item["attr_type_2"];
			$ret[] = $ret_item["attr_type_3"];
			$ret[] = $ret_item["attr_val_1"];
			$ret[] = $ret_item["attr_val_2"];
			$ret[] = $ret_item["attr_val_3"];
			$ret[] = $ret_item["gold"];
			$ret[] = $ret_item["mush"];
		}
		
		$ret[] = $this->cost_upgrade_lvl;
			
		$ret[] = 0;
		$ret[] = 0;
		$ret[] = 0;
		$ret[] = 0;
		$ret[] = 0;
		
		return $ret;
	}
	
	public function check_slot($id)
	{
		global $db;
		
		$qry = $db->query("SELECT * FROM `tower_helper_items` WHERE `owner_id` = ".$this->user_id." AND `slot` = ".$id." AND `helper` = ".$this->ID_Helper."");
		if($qry->rowCount() == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function get_item($slot)
	{
		global $db;
		$qry = $db->query("SELECT * FROM `tower_helper_items` WHERE `owner_id` = ".$this->user_id." AND `slot` = ".$slot." AND `helper` = ". $this->ID_Helper ."");
		
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
	
	public function insert_item($item)
	{
		global $db;
		
		$db->exec("INSERT INTO `tower_helper_items` (`id`, `item_type`, `item_id`, `enchant`, `enchant_power`, `dmg_min`, `dmg_max`, `attr_type_1`, `attr_type_2`, `attr_type_3`, `attr_val_1`, `attr_val_2`, `attr_val_3`, `gold`, `mush`, `toilet`, `slot`, `owner_id`, `helper`)
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
		". $this->user_id .",
		". $this->ID_Helper .");");
	}
	
	public function delete_item($item)
	{
		$GLOBALS['db']->exec("DELETE FROM `tower_helper_items` WHERE `id` = ".$item['id']."");
	}
}
?>