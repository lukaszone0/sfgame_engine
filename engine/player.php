<?php
class Player
{
	public $id;
	public $nick;
	public $lvl;
	public $race;
	public $gender;
	public $face = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	public $dmg_min = 1;
	public $dmg_max = 2;
	public $dungeon_done = 0;
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
		
	public $hp;
	public $attr_str;
	public $attr_agi;
	public $attr_int;
	public $attr_wit;
	public $attr_luck;
	public $armor = 0;
	public $class;
	
	public $data;
	public $bonus_gold_quest;
	public $bonus_gold_pvp;
	public $bonus_exp_quest;
	public $bonus_mush_quest;
	public $bonus_time_quest;
	public $bonus_reaction;
	public $bonus_crit;
	public $bonus_beer;
	public $bonus_items;
	
	function __construct()
	{
		switch(Server::event())
		{
			case 1:
				$this->bonus_exp_quest++;
			break;
			case 2:
				$this->bonus_exp_quest++;
				$this->bonus_gold_quest++;
			break;
			case 3:
				$this->bonus_gold_quest++;
			break;
			case 4:
				$this->bonus_mush_quest++;
			break;
			case 5:
				$this->bonus_exp_quest++;
				$this->bonus_gold_quest++;
				$this->bonus_mush_quest++;
			break;
		}
	}
	
	public function login($SSID)
	{
		$qry = $GLOBALS['db']->prepare("SELECT * FROM `user_data` WHERE `ssid` = :ssid LIMIT 1");
		$qry->bindParam(":ssid", $SSID);
		$qry->execute();
		
		if($qry->rowCount() == 0)
		{
			return true;
		}
		else
		{
			$this->data = $qry->fetch();
			$this->id = $this->data['user_id'];
		}
	}
	
	public function login_username($user_name)
	{
		$qry = $GLOBALS['db']->prepare("SELECT * FROM `user_data` WHERE `user_name` = :user_name LIMIT 1");
		$qry->bindParam(":user_name", $user_name, PDO::PARAM_STR);
		$qry->execute();
		
		if($qry->rowCount() == 0)
		{
			return true;
		}
		else
		{
			$this->data = $qry->fetch();
			$this->id = $this->data['user_id'];
			return false;
		}
	}
	
	public function login_userid($userid)
	{
		$qry = $GLOBALS['db']->prepare("SELECT * FROM `user_data` WHERE `user_id` = :uid LIMIT 1");
		$qry->bindParam(":uid", $userid, PDO::PARAM_STR);
		$qry->execute();
		
		if($qry->rowCount() == 0)
		{
			return true;
		}
		else
		{
			$this->data = $qry->fetch();
			$this->id = $this->data['user_id'];
			return false;
		}
	}
	
	public function get_ret()
	{
		global $db;
		
		if($this->data['mount_dur'] < $GLOBALS['SERVER_TIME'])
		{
			$db->query("UPDATE `user_data` SET `mount` = 0, `mount_dur` = 0 WHERE `user_id` = ".$this->data['user_id']."");
		}
		
		$db->query("UPDATE `user_data` SET `last_activ` = ".$GLOBALS['SERVER_TIME']." WHERE `user_id` = ".$this->data['user_id']."");
		
		$this->id = $this->data['user_id'];
		$this->nick = $this->data['user_name'];
		$this->lvl = $this->data['lvl'];
		$this->race = $this->data['race'];
		$this->gender = $this->data['gender'];
		
		$ret = array_fill(0, 513, '0');
		
		$ret[$GLOBALS['PLAYER_PAYMENT_ID']] = 0;
		$ret[$GLOBALS['PLAYER_ID']] = $this->data['user_id'];
		$ret[$GLOBALS['PLAYER_LAST_ACTION_DATE']] = $this->data['last_activ'];
		$ret[$GLOBALS['PLAYER_REGISTRATION_DATE']] = $this->data['reg_date'];
		$ret[$GLOBALS['PLAYER_REGISTRATION_IP']] = 0;
		
		$count_msg = $db->query("SELECT `msg_id` FROM `messages` WHERE `reciver_id` = ".$this->data['user_id']."");
		$count_msg_unread = $db->query("SELECT `msg_id` FROM `messages` WHERE `reciver_id` = ".$this->data['user_id']." AND `read` = 0");
		
		$ret[$GLOBALS['PLAYER_MSG_COUNT']] = $count_msg->rowCount();
		$ret[$GLOBALS['PLAYER_UNREAD_MSGS']] = $count_msg_unread->rowCount();
		$ret[$GLOBALS['PLAYER_VALIDATION_IP']] = 0;
		$ret[$GLOBALS['PLAYER_LEVEL']] = $this->data['lvl'];
		$ret[$GLOBALS['PLAYER_EXP']] = $this->data['exp'];
		$ret[$GLOBALS['PLAYER_EXP_REQ']] = Server::get_exp($this->data['lvl']);
		$ret[$GLOBALS['PLAYER_HONOR']] = $this->data['honor'];
		$ret[$GLOBALS['PLAYER_RANK']] = $this->get_rank(); 
		$ret[$GLOBALS['PLAYER_CLASS_RANK']] = 0;
		$ret[$GLOBALS['PLAYER_SILVER']] = $this->data['silver'];
		$ret[$GLOBALS['PLAYER_MUSH']] = $this->data['mushroom'];
		$ret[$GLOBALS['PLAYER_MUSHROOMS_MAY_DONATE']] = $this->data['mushroom_buy'];
		$ret[$GLOBALS['PLAYER_FIRST_PAYMENT']] = $this->data['mushroom_buy'] > 1 ? 1 : 0;
		$ret[$GLOBALS['PLAYER_MSUH_GAINED']] = 0;
		$ret[$GLOBALS['PLAYER_MUSH_SPEND']] = 0;
		
		$ret[$GLOBALS['PLAYER_DMG_MIN']] = $this->dmg_min;
		$ret[$GLOBALS['PLAYER_DMG_MAX']] = $this->dmg_max;
		
		for ($i = 1; $i <= 10; $i++) 
		{
			$ret[$GLOBALS['PLAYER_FACE_'.$i]] = $this->data['face'.$i];
			$this->face[$i] = $this->data['face'.$i];
		}
		
		$qry = $db->query("SELECT * FROM `items` WHERE `owner_id` = ".$this->data['user_id']." AND `slot` <= 10");
		$items_hero = $qry->fetchAll();
		
		foreach($items_hero as $item)
		{
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']]] = $item['item_type'] + $item['enchant'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 1] = $item['item_id'] + $item['enchant_power'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 2] = $item['dmg_min'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 3] = $item['dmg_max'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 4] = $item['attr_type_1'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 5] = $item['attr_type_2'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 6] = $item['attr_type_3'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 7] = $item['attr_val_1'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 8] = $item['attr_val_2'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 9] = $item['attr_val_3'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 10] = $item['gold'];
			$ret[$GLOBALS['PLAYER_HERO_SLOT_'. $item['slot']] + 11] = $item['mush'];
			
			if($item['item_type'] == 1)
			{
				$ret[$GLOBALS['PLAYER_DMG_MIN']] = $item['dmg_min'];
				$ret[$GLOBALS['PLAYER_DMG_MAX']] = $item['dmg_max'];
				$this->weapon['has_weapon'] = $item['item_type'];
				$this->weapon['item_type'] = $item['item_type'];
				$this->weapon['item_id'] = $item['item_id'];
				$this->weapon['dmg_min'] = $item['dmg_min'];
				$this->weapon['dmg_max'] = $item['dmg_max'];
				$this->weapon['attr_type_1'] = $item['attr_type_1'];
				$this->weapon['attr_type_2'] = $item['attr_type_2'];
				$this->weapon['attr_type_3'] = $item['attr_type_3'];
				$this->weapon['attr_val_1'] = $item['attr_val_1'];
				$this->weapon['attr_val_2'] = $item['attr_val_2'];
				$this->weapon['attr_val_3'] = $item['attr_val_3'];
				$this->weapon['gold'] = $item['gold'];
				$this->weapon['mush'] = $item['mush'];
			}
			
			if($item['item_type'] == 2)
			{
				$this->shield['has_shield'] = $item['item_type'];
				$this->shield['item_type'] = $item['item_type'];
				$this->shield['item_id'] = $item['item_id'];
				$this->shield['dmg_min'] = $item['dmg_min'];
				$this->shield['dmg_max'] = $item['dmg_max'];
				$this->shield['attr_type_1'] = $item['attr_type_1'];
				$this->shield['attr_type_2'] = $item['attr_type_2'];
				$this->shield['attr_type_3'] = $item['attr_type_3'];
				$this->shield['attr_val_1'] = $item['attr_val_1'];
				$this->shield['attr_val_2'] = $item['attr_val_2'];
				$this->shield['attr_val_3'] = $item['attr_val_3'];
				$this->shield['gold'] = $item['gold'];
				$this->shield['mush'] = $item['mush'];
			}
			
			if($item['item_type'] > 2 AND $item['item_type'] < 8)
			{
				$this->armor +=$item['dmg_min'];
			}
		
			switch($item['slot'])
			{
				case 0:
					$this->bonus_exp_quest += round($item['item_id'] / 65536);
				break;
				case 1:
					$this->bonus_mush_quest += round($item['item_id'] / 65536);
				break;
				case 2:
					$this->bonus_reaction += round($item['item_id'] / 65536);
				break;
				case 3:
					$this->bonus_time_quest += round($item['item_id'] / 65536);
				break;
				case 4:
					$this->bonus_items += round($item['item_id'] / 65536);
				break;
				case 5:
					$this->bonus_beer += round($item['item_id'] / 65536);
				break;
				case 6:
					$this->bonus_gold_quest += round($item['item_id'] / 65536);
				break;
				case 7:
					$this->bonus_gold_pvp += round($item['item_id'] / 65536);
				break;
				case 8:
					$this->bonus_crit += round($item['item_id'] / 65536);
				break;
			}
			
			for($i = 1; $i<4;$i++)
			{
				if($item['attr_type_'. $i] != 0 and $item['attr_type_'. $i] != 6)
				{
					$ret[$GLOBALS['PLAYER_ATTR_BONUS_'. $this->get_name_attr($item['attr_type_'. $i])]] += $item['attr_val_'. $i];
				}
				else if($item['attr_type_'. $i] == 6)
				{
					$ret[$GLOBALS['PLAYER_ATTR_BONUS_str']] += $item['attr_val_1'];
					$ret[$GLOBALS['PLAYER_ATTR_BONUS_agi']] += $item['attr_val_1'];
					$ret[$GLOBALS['PLAYER_ATTR_BONUS_int']] += $item['attr_val_1'];
					$ret[$GLOBALS['PLAYER_ATTR_BONUS_wit']] += $item['attr_val_1'];
					$ret[$GLOBALS['PLAYER_ATTR_BONUS_luck']] += $item['attr_val_1'];
					$i = 4;
				}
			}
		}
		
		$ret[$GLOBALS['PLAYER_RACE']] = $this->data['race'];
		$ret[$GLOBALS['PLAYER_GENDER']] = $this->data['gender'];
		$ret[$GLOBALS['PLAYER_CLASS']] = $this->data['class'];
		
		for($i=1;$i<4;$i++)
		{
			if($this->data['potion_time'.$i] > $GLOBALS['SERVER_TIME'])
			{
				$ret[$GLOBALS['PLAYER_POTION_TYPE'.$i]] = $this->data['potion_id'.$i];
				$ret[$GLOBALS['PLAYER_POTION_TIME'.$i]] = $this->data['potion_time'.$i];
				$ret[$GLOBALS['PLAYER_POTION_VALUE'.$i]] = $this->data['potion_value'.$i];
				$attr = 'attr_' . ['str', 'agi', 'int', 'wit', 'luck', 'str', 'agi', 'int', 'wit', 'luck', 'str', 'agi', 'int', 'wit', 'luck', 'wit'][$this->data['potion_id'.$i] - 1];
				$this->data[$attr] += ($this->data[$attr] * ($this->data['potion_value'.$i] / 100)); 
			}
			else
			{
				$db->query("UPDATE `user_data` SET `potion_id".$i."` = 0, `potion_time".$i."` = 0, `potion_value".$i."` = 0 WHERE `user_id` = ".$this->data['user_id']."");
			}
		}
		
		$ret[$GLOBALS['PLAYER_ATTR_STR']] = $this->data['attr_str'];
		$ret[$GLOBALS['PLAYER_ATTR_AGI']] = $this->data['attr_agi'];
		$ret[$GLOBALS['PLAYER_ATTR_INT']] = $this->data['attr_int'];
		$ret[$GLOBALS['PLAYER_ATTR_WIT']] = $this->data['attr_wit'];
		$ret[$GLOBALS['PLAYER_ATTR_LUCK']] = $this->data['attr_luck'];
		
		$this->data['attr_str'] += $ret[$GLOBALS['PLAYER_ATTR_BONUS_str']];
		$this->data['attr_agi'] += $ret[$GLOBALS['PLAYER_ATTR_BONUS_agi']];
		$this->data['attr_int'] += $ret[$GLOBALS['PLAYER_ATTR_BONUS_int']];
		$this->data['attr_wit'] += $ret[$GLOBALS['PLAYER_ATTR_BONUS_wit']];
		$this->data['attr_luck'] += $ret[$GLOBALS['PLAYER_ATTR_BONUS_luck']];
		
		$this->attr_str = round($this->data['attr_str']);
		$this->attr_agi = round($this->data['attr_agi']);
		$this->attr_int = round($this->data['attr_int']);
		$this->attr_wit = round($this->data['attr_wit']);
		$this->attr_luck = round($this->data['attr_luck']);
		
		$k = [5,2,4][$this->data['class'] - 1];
		$this->hp = round($this->data['attr_wit'] * $k * ($this->data['lvl'] + 1));
		$this->class = $this->data['class'];
		$this->dmg_min = $this->weapon['dmg_min'] * (1 + $this->data['attr_' . $this->get_name_attr([1,3,2][$this->data['class'] - 1])] / 10);
		$this->dmg_max = $this->weapon['dmg_max'] * (1 + $this->data['attr_' .$this->get_name_attr([1,3,2][$this->data['class'] - 1])] / 10);
		
		$ret[$GLOBALS['PLAYER_ATTR_STR_BUY']] = $this->data['attr_str_buy'];
		$ret[$GLOBALS['PLAYER_ATTR_AGI_BUY']] = $this->data['attr_agi_buy'];
		$ret[$GLOBALS['PLAYER_ATTR_INT_BUY']] = $this->data['attr_int_buy'];
		$ret[$GLOBALS['PLAYER_ATTR_WIT_BUY']] = $this->data['attr_wit_buy'];
		$ret[$GLOBALS['PLAYER_ATTR_LUCK_BUY']] = $this->data['attr_luck_buy'];
		
		$ret[$GLOBALS['PLAYER_STATUS']] = $this->data['status'];
		$ret[$GLOBALS['PLAYER_CHOSEN_QUEST']] = $this->data['status_extra'];
		$ret[$GLOBALS['PLAYER_ACT_ENDTIME']] = $this->data['status_end'];
		
		$qry = $db->query("SELECT * FROM `items` WHERE `owner_id` = ".$this->data['user_id']." AND `slot` > 10 AND `slot` < 16");
		$items_backpack = $qry->fetchAll();
		
		foreach($items_backpack as $item)
		{
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)]] = $item['item_type'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 1] = $item['item_id'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 2] = $item['dmg_min'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 3] = $item['dmg_max'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 4] = $item['attr_type_1'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 5] = $item['attr_type_2'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 6] = $item['attr_type_3'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 7] = $item['attr_val_1'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 8] = $item['attr_val_2'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 9] = $item['attr_val_3'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 10] = $item['gold'];
			$ret[$GLOBALS['PLAYER_BACKPACK_SLOT_'. ($item['slot'] - 10)] + 11] = $item['mush'];
		}
		
		$ret[$GLOBALS['PLAYER_QUEST_DESC_1']] = 3;
		$ret[$GLOBALS['PLAYER_QUEST_DESC_2']] = 1;
		$ret[$GLOBALS['PLAYER_QUEST_DESC_3']] = 5;
		
		$ret[$GLOBALS['PLAYER_QUEST_OFFER_LOCATION1']] = $this->data['quest_location_1'];
		$ret[$GLOBALS['PLAYER_QUEST_OFFER_LOCATION2']] = $this->data['quest_location_2'];
		$ret[$GLOBALS['PLAYER_QUEST_OFFER_LOCATION3']] = $this->data['quest_location_3'];
		
		$ret[$GLOBALS['PLAYER_QUEST_DURATION_1']] = $this->data['quest_dur_1'];
		$ret[$GLOBALS['PLAYER_QUEST_DURATION_2']] = $this->data['quest_dur_2'];
		$ret[$GLOBALS['PLAYER_QUEST_DURATION_3']] = $this->data['quest_dur_3'];
		
		$qry = $db->query("SELECT * FROM `items_tavern` WHERE `owner_id` = ".$this->data['user_id']."");
		$items_tavern = $qry->fetchAll();
		
		foreach($items_tavern as $item)
		{
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']]] = $item['item_type'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 1] = $item['item_id'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 2] = $item['dmg_min'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 3] = $item['dmg_max'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 4] = $item['attr_type_1'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 5] = $item['attr_type_2'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 6] = $item['attr_type_3'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 7] = $item['attr_val_1'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 8] = $item['attr_val_2'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 9] = $item['attr_val_3'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 10] = $item['gold'];
			$ret[$GLOBALS['PLAYER_QUEST_REWARD_'. $item['slot']] + 11] = $item['mush'];
		}
		
		$ret[$GLOBALS['PLAYER_QUEST_EXP_1']] = $this->data['quest_exp_1'];
		$ret[$GLOBALS['PLAYER_QUEST_EXP_2']] = $this->data['quest_exp_2'];
		$ret[$GLOBALS['PLAYER_QUEST_EXP_3']] = $this->data['quest_exp_3'];
		
		$ret[$GLOBALS['PLAYER_QUEST_GOLD_1']] = $this->data['quest_gold_1'];
		$ret[$GLOBALS['PLAYER_QUEST_GOLD_2']] = $this->data['quest_gold_2'];
		$ret[$GLOBALS['PLAYER_QUEST_GOLD_3']] = $this->data['quest_gold_3'];
		
		$ret[$GLOBALS['PLAYER_MOUNT']] = (($this->data['tower_level'] - 1) * 65536) + $this->data['mount'];
		$ret[$GLOBALS['PLAYER_MOUNT_DURATION']] = $this->data['mount_dur'];

		$qry = $db->query("SELECT * FROM `items_shakes` WHERE `owner_id` = ".$this->data['user_id']."");
		$items_shakes = $qry->fetchAll();
		
		foreach($items_shakes as $item)
		{
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']]] = $item['item_type'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 1] = $item['item_id'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 2] = $item['dmg_min'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 3] = $item['dmg_max'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 4] = $item['attr_type_1'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 5] = $item['attr_type_2'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 6] = $item['attr_type_3'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 7] = $item['attr_val_1'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 8] = $item['attr_val_2'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 9] = $item['attr_val_3'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 10] = $item['gold'];
			$ret[$GLOBALS['PLAYER_SHAKES_ITEM_'. $item['slot']] + 11] = $item['mush'];
		}
		
		$qry = $db->query("SELECT * FROM `items_fidget` WHERE `owner_id` = ".$this->data['user_id']."");
		$items_fidget = $qry->fetchAll();
		
		foreach($items_fidget as $item)
		{
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']]] = $item['item_type'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 1] = $item['item_id'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 2] = $item['dmg_min'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 3] = $item['dmg_max'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 4] = $item['attr_type_1'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 5] = $item['attr_type_2'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 6] = $item['attr_type_3'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 7] = $item['attr_val_1'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 8] = $item['attr_val_2'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 9] = $item['attr_val_3'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 10] = $item['gold'];
			$ret[$GLOBALS['PLAYER_FIDGET_ITEM_'. $item['slot']] + 11] = $item['mush'];
		}
		
		$ret[$GLOBALS['PLAYER_GUILD_INDEX']] = $this->data['guild_id'];
		$ret[$GLOBALS['PLAYER_GUILD_RANK']] = $this->data['guild_rank'];
		$ret[$GLOBALS['PLAYER_ALBUM']] = $this->data['album'];
		$ret[$GLOBALS['PLAYER_THIRST']] = $this->data['thirst'];
		$ret[$GLOBALS['PLAYER_BEERS']] = $this->data['beers'];
		$ret[$GLOBALS['PLAYER_DUNGEON_ENDTIME']] = $this->data['dungeon_time'];
		$ret[$GLOBALS['PLAYER_ARENA_ENDTIME']] = $this->data['arena_time'];
		
		if($this->get_guild())
		{
			$g_data = $this->get_guild();
			$ret[$GLOBALS['PLAYER_EXP_BONUS']] = ($g_data['instructor'] + ($g_data['dung'])) * 2;
			$ret[$GLOBALS['PLAYER_GOLD_BONUS']] = ($g_data['treasure'] + ($g_data['dung'])) * 2;
		}
		
		$ret[$GLOBALS['PLAYER_EMAIL_VALID']] = $this->data['email_validate'];
		$ret[$GLOBALS['PLAYER_ACHIEVEMENT_LVL']] = $this->data['lvl'];
		
		$sum = 0;
		
		for($i=1;$i<14;$i++)
		{
			$a = $this->data['dungeon_'.$i];
			if($a > 2)
			{
				$sum += ($a - 2);
			}
		}
		
		$this->dungeon_done = $sum;
		
		$ret[$GLOBALS['PLAYER_ACHIEVEMENT_DUNG']] = $sum;
		$ret[$GLOBALS['PLAYER_DUNGEON_DONE']] = $sum;
		$ret[$GLOBALS['PLAYER_ACHIEVEMENT_ARENA']] = $this->data['medal_gladiator'];
		$ret[$GLOBALS['PLAYER_ACHIEVEMENT_QUEST']] = $this->data['medal_adventurer'];
		$ret[$GLOBALS['PLAYER_ACHIEVEMENT_WORK']] = $this->data['medal_employment'];
		$ret[$GLOBALS['PLAYER_ACHIEVEMENT_GOLD']] = $this->data['medal_commerce'];
		$ret[$GLOBALS['PLAYER_ACHIEVEMENT_HONOR']] = $this->data['medal_bravery'];
		$ret[$GLOBALS['PLAYER_ACHIEVEMENT_FRIEND']] = $this->data['medal_friendship'];
		
		$ret[$GLOBALS['PLAYER_DUNGEON_1']] = $this->data['dungeon_1'] ;
		$ret[$GLOBALS['PLAYER_DUNGEON_2']] = $this->data['dungeon_2'];
		$ret[$GLOBALS['PLAYER_DUNGEON_3']] = $this->data['dungeon_3'];
		$ret[$GLOBALS['PLAYER_DUNGEON_4']] = $this->data['dungeon_4'];
		$ret[$GLOBALS['PLAYER_DUNGEON_5']] = $this->data['dungeon_5'];
		$ret[$GLOBALS['PLAYER_DUNGEON_6']] = $this->data['dungeon_6'];
		$ret[$GLOBALS['PLAYER_DUNGEON_7']] = $this->data['dungeon_7'];
		$ret[$GLOBALS['PLAYER_DUNGEON_8']] = $this->data['dungeon_8'];
		$ret[$GLOBALS['PLAYER_DUNGEON_9']] = $this->data['dungeon_9'];
		$ret[$GLOBALS['PLAYER_DUNGEON_10']] = $this->data['dungeon_10'];
		$ret[$GLOBALS['PLAYER_DUNGEON_11']] = $this->data['dungeon_11'];
		$ret[$GLOBALS['PLAYER_DUNGEON_12']] = $this->data['dungeon_12'];
		$ret[$GLOBALS['PLAYER_DUNGEON_13']] = 120 + $this->data['dungeon_13'];
		$ret[$GLOBALS['PLAYER_GUILD_JOIN_DATE']] = $this->data['guild_join_time'];
		$ret[$GLOBALS['PLAYER_NEW_FLAGS']] = $this->data['golden_frame'];
		
		$status_attack = 0;
		
		if($this->data['guild_attack'] == 1)
		{
			$status_attack = 1;
		}
		else if($this->data['guild_defend'] == 1)
		{
			$status_attack = 2;
		}
		else if($this->data['guild_attack'] + $this->data['guild_defend'] == 2)
		{
			$status_attack = 3;
		}
		
		$ret[$GLOBALS['PLAYER_GUILD_WAR_STATUS']] = $status_attack; 
		$ret[$GLOBALS['PLAYER_ARMOR']] = $this->armor;
		$ret[$GLOBALS['PLAYER_TOILET']] = $this->data['toilet'];
		$ret[$GLOBALS['PLAYER_PHP_SESSION']] = 0;
		$ret[$GLOBALS['PLAYER_SSID']] = $this->data['ssid'];
		$ret[$GLOBALS['PLAYER_SERVER_TIME']] = $GLOBALS['SERVER_TIME'];
		$ret[$GLOBALS['PLAYER_MUSHROOM_BOUGHT_AMOUNT']] = 0;
		$ret[$GLOBALS['PLAYER_TOWER_LEVEL']] = $this->data['tower_level'] - 1;
		
		return $ret;
	}

	public function get_name_attr($id)
	{
		switch($id)
		{
			case 1:
				return "str";
			break;
			case 2:
				return "agi";
			break;
			case 3:
				return "int";
			break;
			case 4:
				return "wit";
			break;
			case 5:
				return "luck";
			break;
		}
	}
	
	public function reroll_items_shop($shop)
	{
		global $db;
		
		$db->query("DELETE FROM `".$shop."` WHERE `owner_id` = ".$this->data['user_id']."");
		$db->query("UPDATE `user_data` SET `shop_reroll_time` = ".$GLOBALS['SERVER_TIME_TOMORROW']." WHERE `user_id` = ".$this->data['user_id']."");
		
		for($i = 1;$i<7;$i++)
		{
			if($shop == "items_fidget")
			{
				$shop_items_type = [8,9,10,12][rand(0, 3)];
			}
			else if($shop == "items_shakes")
			{
				if($this->data['class'] == 1)
				{
					$shop_items_type = rand(1, 7);
				}
				else
				{
					$shop_items_type = [1, 3, 4, 5, 6, 7][rand(0, 5)];
				}
			}
			$items = new Item;
			$items->gen_item($shop_items_type, $this->data['lvl'], $this->data['class']);
			$item_shop = $items->item;
			$item_shop['owner_id'] = $this->data['user_id'];
			$item_shop['slot'] = $i;
			$items->insert_item($item_shop, $shop);
		}
	}
	
	public function get_gold($quest)
	{
		if($quest == false)
		{
			return $this->data['lvl'] * ($this->data['lvl'] + $this->bonus_gold_quest) * 100;
		}
		else
		{
			return (round(rand(rand($this->data['lvl'] * 2 + $this->bonus_gold_quest, $this->data['lvl'] * 5 + $this->bonus_gold_quest), rand($this->data['lvl'] * 10 + $this->bonus_gold_quest, $this->data['lvl'] * 15 + $this->bonus_gold_quest)) * ($this->data['lvl'] / 2)) * rand(1, 6)) + rand(1, 99);
		}
	}
	
	public function get_exp()
	{
		return floor(Server::get_exp($this->data['lvl']) / (10 + ($this->data['lvl'] * 0.05)) * (0.85 + mt_rand() / mt_getrandmax() * 0.3)) + $this->bonus_exp_quest;
	}
	
	public function get_rank()
	{
		global $db;
		
		$rank = $db->query("SELECT pos FROM (SELECT honor, lvl, user_name, ssid, user_id, @r:=@r+1 as pos
			FROM user_data, (select @r:=0) r0
			ORDER BY honor DESC, lvl DESC, user_id DESC) user_data_ordered WHERE `user_id` = ".$this->data['user_id']."");
		$rank = $rank->fetch();
		return $rank['pos'];
	}
	
	public function check_slot($id)
	{
		global $db;
		
		$qry = $db->query("SELECT * FROM `items` WHERE `owner_id` = ".$this->data['user_id']." AND `slot` = ".$id."");
		if($qry->rowCount() == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function search_item($item_id, $item_type, $player_id)
	{
		global $db;
		$qry = $db->query("SELECT * FROM `items` WHERE `item_id` = ".$item_id." AND `item_type` = ".$item_type." AND `owner_id` = ".$player_id."");
		if($qry->rowCount() >= 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function get_free_slot()
	{
		global $db;
		
		for($i=11;$i<16;$i++)
		{
			$qry = $db->query("SELECT * FROM `items` WHERE `owner_id` = ".$this->data['user_id']." AND `slot` = ".$i."");
			if($qry->rowCount() == 0)
			{
				return $i;
			}
		}
		return 0;
	}
	
	public function get_guild()
	{
		global $db;
		
		if($this->data['guild_id'] == 0)
		{
			return false;
		}
		$qry = $db->query("SELECT * FROM `guilds` WHERE `guild_id` = ".$this->data['guild_id']."");
		if($qry->rowCount() == 1)
		{
			$qry = $qry->fetch();
			return $qry;
		}
		else
		{
			return false;
		}
	}
	
	public function load_witch_data()
	{
		global $db;
		
		$qry = $db->query("SELECT * FROM `witch` WHERE `id` = 1 LIMIT 1");
		$witch = $qry->fetch();
		
		$cost = $this->data['lvl'] * 65536;

		$sound = 0;

	   $ret = ";9/" . $witch['progress'] . "/" . $witch['users'] . "/" . $witch['id_donation'] . "/
	   0/" . $witch['work'] . "/
	   0/" . $witch['scroll_max'] ."/
	   0/" . $witch['scroll_1'] . "/" . $witch['scroll_1_time'] . "/
	   0/" . $witch['scroll_2'] . "/" . $witch['scroll_2_time'] . "/
	   0/" . $witch['scroll_3'] . "/" . $witch['scroll_3_time'] . "/
	   0/" . $witch['scroll_4'] . "/" . $witch['scroll_4_time'] . "/
	   0/" . $witch['scroll_5'] . "/" . $witch['scroll_5_time'] . "/
	   0/" . $witch['scroll_6'] . "/" . $witch['scroll_6_time'] . "/
	   0/" . $witch['scroll_7'] . "/" . $witch['scroll_7_time'] . "/
	   0/" . $witch['scroll_8'] . "/" . $witch['scroll_8_time'] . "/
	   0/" . $witch['scroll_9'] . "/" . $witch['scroll_9_time'] . "/
	   0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0/;" . $sound . "/
		" . $cost;
		
		return $ret;
	}
	
	public function load_mail_data($view)
	{
		global $db, $DATA;
		
		$ret = array_fill(0, 14, '');
		
		$pos = addslashes($DATA);
		
		if($pos == "" OR $pos == "-1")
		{
			$pos = 1;
		}
		else if($pos < 1 OR $pos > 100)
		{
			exit("Error: Message not found !");
		}
		
		if(!$view)
			$pos = 1;
		
		$messages_query = $db->query("SELECT * FROM `messages` WHERE `reciver_id` = ".$this->data['user_id']." ORDER BY `time` DESC");
		
		$messages_array = array_fill(0, 101, "");
		
		$messages_count = $messages_query->rowCount();
		
		if($messages_query->rowCount() > 0)
		{
			$db->query("UPDATE `messages` SET `read` = 1 WHERE `reciver_id` = ".$this->data['user_id']."");
			
			$messages = $messages_query->fetchAll();
			
			$y = 0;
			foreach($messages as $message)
			{
				$sender = $db->query("SELECT `user_name` FROM `user_data` WHERE `user_id` = ".$message['sender_id']."");
				if($sender->rowCount() == 1)
				{
					$sender = $sender->fetch();
					$nick = $sender['user_name'];
				}
				else
				{
					$nick = "Null";
				}
				
				$messages_array[$y] = $nick . "/" . Server::utf8_format(urldecode($message['subject'])) . "/" . $message['time'];
				$y++;
			}
		}
		
		$e = 0;
		for($i = $pos;$i<(16 + $pos) ;$i++)
		{
			$ret[$e] = $messages_array[$i - 1];
			$e++;
			$ret[$e] = ";". $messages_count;
		}
		
		$ret[0] = "005" . $messages_query->rowCount() . ";" . $ret[0];
		return $ret;
	}
	
	public function get_player_name($uid)
	{
		global $db;
		
		$qry = $db->query("SELECT `user_name` FROM `user_data` WHERE `user_id` = ".$uid." LIMIT 1");
		if($qry->rowCount() == 1)
		{
			return $qry->fetch()['user_name'];
		}
		else 
		{
			return "Null";
		}
	}
	
	public function add_stats($add)
	{
		global $db;
		
		$this->data['attr_str'] +=$add;
		$this->data['attr_agi'] +=$add;
		$this->data['attr_int'] +=$add;
		$this->data['attr_luck'] +=$add;
		$this->data['attr_wit'] +=$add;
				
		$db->query("UPDATE `user_data` SET `attr_str` = '".$this->data['attr_str']."', `attr_agi` = '".$this->data['attr_agi']."', `attr_int` = '".$this->data['attr_int']."', `attr_wit` = '".$this->data['attr_wit']."', `attr_luck` = '".$this->data['attr_luck']."' WHERE `user_data`.`user_id` = ".$this->data['user_id'].";");
	}
	
	public function add_stats_archivment_dung()
	{
		global $db;
		
		$sum = $this->get_ret()[466];
		
		$archivment = array(5, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100);
		
		for($i=0;$i<11;$i++)
		{
			if($archivment[$i] == ($sum))
			{
				$stat_add = (1 + $i);
				$this->add_stats($stat_add);
				$i = 11;
			}
		}
	}
	
	public function add_stats_archivment_arena()
	{
		//todo
	}
}
?>