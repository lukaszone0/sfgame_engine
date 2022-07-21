<?php
class tower
{
	private $warrior;
	private $mage;
	private $hunter;
	private $user_id;
	private $tower_level;
	
	function __construct($player)
	{
		$this->user_id = $player->data['user_id'];
		$this->tower_level = $player->data['tower_level'];
		
		$warrior = new Helper($player, 1);
		$this->warrior = $warrior;
		
		$mage = new Helper($player, 2);
		$this->mage = $mage;
		
		$hunter = new Helper($player, 3);
		$this->hunter = $hunter;
	}
	
	public function fight($monster, $player)
	{
		$ret = "";
		
		$round = 0;
		$itmslot = "-1";
		
		$group = array($this->warrior, $this->mage, $this->hunter, $player);
		
		$show_name = array(2, 3, $player->data['user_name'], "", "", "");
		
		$monster_default_hp = $monster->hp;
		
		$monster->face[1] = "-".$monster->face[1];
		
		while($round < 4 AND $monster->hp > 0)
		{
			$player = $group[$round];
			
			$ret .= $player->hp ."/".  $player->attr_str ."/".  $player->attr_agi ."/".  $player->attr_int ."/".  $player->attr_wit ."/".  $player->attr_luck ."/";
			$ret .= $monster_default_hp ."/".  $monster->attr_str ."/".  $monster->attr_agi ."/".  $monster->attr_int ."/".  $monster->attr_wit ."/".  $monster->attr_luck .";";
			
			if($monster->hp == $monster_default_hp)
			{
				$monster_hp = "-1";
			}
			else
			{
				$monster_hp = $monster->hp;
			}
			
			$fight = new Fight;
		
			$sim = $fight->fight_cal($player, $monster)[0];
			
			for($i=0;$i<count($sim);$i++) 
			{
				$ret .= $sim[$i][0] ."/". $sim[$i][1] ."/". $sim[$i][2];
				if($i < (count($sim) - 1))
				{
					$ret .= "/";
				}
			}
			
			$ret .= $fight->load_view($player, $monster);
			
			$ret .=  ";2;0;0;0;-1;;5;6§-1/" . $show_name[$round] ."/". $show_name[$round + 1] ."/". $show_name[$round + 2] . "///" . $monster_hp . "/////§";
			
			$round++;
		}
		
		if($monster->hp <= 0)
		{
			$player->data['tower_level']++;
			
			$player->data['exp'] += $monster->exp;
			while($player->data['exp'] > Server::get_exp($player->data['lvl']))
			{
				$player->data['exp'] -= Server::get_exp($player->data['lvl']);
				$player->data['lvl']++;
			}
			
			$GLOBALS['db']->query("UPDATE `user_data` SET 
			`exp` = '".$player->data['exp']."',
			`silver` = '".$player->data['silver']."', 
			`mushroom` = ".$player->data['mushroom'].",
			`lvl` = ".$player->data['lvl'].",
			`dungeon_time` = ".$player->data['dungeon_time'].",
			`tower_level` = ".$player->data['tower_level']."
			WHERE `user_id` = ".$player->data['user_id']."");
			
			
			if($player->get_free_slot())
			{
				if(rand(1, 100) < 15 + $player->bonus_items)
				{
					$itmslot = $player->get_free_slot();
					$rand_type = rand(1, 10);
					$item = new Item;
					$item->gen_item($rand_type, $player->data['lvl'], rand(1, 3));
					$item_quest =  $item->item;
					$item_quest['owner_id'] = $player->data['user_id'];
					$item_quest['slot'] = $itmslot;
					$item->insert_item($item_quest, "items");
				}
			}
			
		}
		
		$ret .=  ";" . $monster->gold . ";" . $itmslot . ";" . $monster->exp . ";0;-1§";
		
		$ret = explode("/", $ret);
		return $ret;
	}
	
	public function get_ret()
	{
		$ret = ";". $this->user_id . "/" . 1479412 . "/" . ($this->tower_level - 1);
		
		for($i=0;$i<Count($this->warrior->get_ret());$i++)
		{
			$ret .= "/" . $this->warrior->get_ret()[$i];
		}
		
		for($i=0;$i<Count($this->mage->get_ret());$i++)
		{
			$ret .= "/" . $this->mage->get_ret()[$i];
		}
		
		for($i=0;$i<Count($this->hunter->get_ret());$i++)
		{
			$ret .= "/" . $this->hunter->get_ret()[$i];
		}
		
		$ret .= "0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/";
		
		return $ret;
	}
}
?>