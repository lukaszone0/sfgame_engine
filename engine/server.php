<?php
class Server
{
	public function start_time()
	{
		global $db;
		$qry = $db->query("SELECT `value` FROM `game_settings` WHERE `setting` = 'SERVER_RESTART'");
		return $qry->fetch()[0];
	}
	
	public function pvp($player1, $player2)
	{
		global $db;
		
		$ret = array();
		$sim_reverse_array = array();
		
		array_push($ret, $player1->hp, $player1->attr_str, $player1->attr_agi, $player1->attr_int, $player1->attr_wit, $player1->attr_luck);
		array_push($ret, $player2->hp, $player2->attr_str,  $player2->attr_agi, $player2->attr_int, $player2->attr_wit, $player2->attr_luck . ";");
	
		array_push($sim_reverse_array, $player2->hp, $player2->attr_str,  $player2->attr_agi, $player2->attr_int, $player2->attr_wit, $player2->attr_luck);
		array_push($sim_reverse_array, $player1->hp, $player1->attr_str, $player1->attr_agi, $player1->attr_int, $player1->attr_wit, $player1->attr_luck . ";");
		
		$fight = new Fight;
		
		$sim = $fight->fight_cal($player1, $player2);
		$sim_reverse = $sim[1];
		$sim = $sim[0];
		
		$arena_time = $GLOBALS['SERVER_TIME'] + 600;
		
		$db->query("UPDATE `user_data` SET `arena_time` = ".$arena_time." WHERE `user_id` = ".$player1->data['user_id']."");
		
		$ret[count($ret) - 1] .= $sim[0][0] . "/" . $sim[0][1] . "/" . $sim[0][2];
		
		for($i=1;$i<count($sim);$i++) 
		{
			array_push($ret, $sim[$i][0], $sim[$i][1], $sim[$i][2]);
		}
		
		$sim_reverse_array[count($sim_reverse_array) - 1] .= $sim_reverse[0][0] . "/" . $sim_reverse[0][1] . "/" . $sim_reverse[0][2];
		
		for($i=1;$i<count($sim);$i++) 
		{
			array_push($sim_reverse_array, $sim_reverse[$i][0], $sim_reverse[$i][1], $sim_reverse[$i][2]);
		}
		
		$ret[count($ret) - 1] .= Fight::load_view($player1, $player2);
		
		$sim_reverse_array[count($sim_reverse_array) - 1] .= Fight::load_view($player2, $player1);
		
		$win = $sim[count($sim) - 1][0] <= 0 ? true : false;
		
		$honor = Server::calculateHonor($player1, $player2, $win);
		
		if($win)
		{
			$silver = $player2->get_gold(true);
			$db->query("UPDATE `user_data` SET `honor` = `honor` + ".$honor.", `silver` = `silver` +  ".$silver.", `medal_gladiator` = `medal_gladiator` + 1 WHERE `user_id` = ".$player1->data['user_id']."");
			$db->query("UPDATE `user_data` SET `honor` = `honor` - ".$honor.", `silver` = `silver` -  ".$silver." WHERE `user_id` = ".$player2->data['user_id']."");
			
			$ret[count($ret) - 1] .= ";2;1;".$honor.";". $silver .";-1";
			$sim_reverse_array[count($sim_reverse_array) - 1] .= ";2;1;-".$honor.";-". $silver .";-1";
		}
		else
		{
			$silver = $player1->get_gold(true);
			$db->query("UPDATE `user_data` SET `honor` = `honor` - ".$honor.", `silver` = `silver` -  ".$silver." WHERE `user_id` = ".$player1->data['user_id']."");
			$db->query("UPDATE `user_data` SET `honor` = `honor` +".$honor.", `silver` = `silver` +  ".$silver." WHERE `user_id` = ".$player2->data['user_id']."");
			$ret[count($ret) - 1] .= ";2;1;-".$honor.";-". $silver .";-1";
			$sim_reverse_array[count($sim_reverse_array) - 1] .= ";2;1;".$honor.";". $silver .";-1";
		}
		
		$qry = $db->query("SELECT COUNT(*) FROM messages WHERE reciver_id = ".$player2->data['user_id']."");
		
		if($qry->rowCount() < 100)
		{
			$sim_reverse_array = str_replace(';', '#', $sim_reverse_array);
			$mlog = join("/", $sim_reverse_array);
					
			$sub = $win ? 6 : 7;
			
			$db->query("INSERT INTO `messages`(`sender_id`, `reciver_id`, `time`, `subject`, `msg`) VALUES (".$player1->data['user_id']." ,".$player2->data['user_id'].",".$GLOBALS['SERVER_TIME'].",".$sub.",'".$mlog."')");
		}
		
		$success = $win ? 'yes' : 'no';
		$db->exec("INSERT INTO `user_fights`(`user_id`, `target_id`, `success`, `honor`, `fight_time`) VALUES(".$player1->data['user_id'].", ".$player2->data['user_id'].", '".$success."', ".$honor.", ".$GLOBALS['SERVER_TIME'].")");
		
		return $ret;
	}
	
	public function calculateHonor($player1, $player2, $win)
	{
	   
		if ($win == true) 
		{
			$rank = $player1->data['honor'] - $player2->data['honor'];
			$honor = 100 - ($rank / 10);
		} 
		else 
		{
			$rank = $player2->data['honor'] - $player1->data['honor'];
			$honor = 100 - ($rank / 10);
		}
		
		
		if ($honor < 0)
			$honor = 0;
		
		else if ($honor > 200)
			$honor = 200;
		
	   
		if ($win == true)
		{
			
			if ($honor > $player2->data['honor'])
				$honor = $player2->data['honor'];
		} 
		else 
		{
			if ($honor > $player1->data['honor'])
				$honor = $player1->data['honor'];
		}
		
		if ($honor < 0)
			$honor = 0;
		
		$honor = round($honor);
		
		$a = $win ? 1 : -1;
		
		return $honor * $a;
	}
	
	public function reverseArenaLog($log, $win)
	{
		/* fightRound = int(fightData.length / 6) - 1;
         charLife = fightData[fightRound * 6];
         charDamage = fightData[fightRound * 6 + 1];
         charFlag = fightData[fightRound * 6 + 2];
         oppLife = fightData[fightRound * 6 + 3];
         oppDamage = fightData[fightRound * 6 + 4];
         oppFlag = fightData[fightRound * 6 + 5];*/
		
		$logs = explode(";", $log);
		$logs_reverse = array();
		
		$stats = explode("/", $logs[0]);
		$stats_reverse = array();
		$fight = explode("/", $logs[1]);
		$fight_reverse = array();
		$looks = explode("/", $logs[2]);
		$looks_reverse = array();
		$weapons = explode("/", $logs[3]);
		$weapons_reverse = array();
		$shield = explode("/", $logs[4]);
		$shield_reverse = array();
		
		for($i=6;$i<12;$i++)
		{
			array_push($stats_reverse, $stats[$i]);
		}
		for($i=0;$i<6;$i++)
		{
			array_push($stats_reverse, $stats[$i]);
		}
		
		$i = 0;
		
		while ($i < count($fight)) 
		{
			array_push($fight_reverse, $fight[$i + 3]);
			array_push($fight_reverse, $fight[$i + 4]);
			array_push($fight_reverse, $fight[$i + 5]);
			
			array_push($fight_reverse, $fight[$i + 0]);
			array_push($fight_reverse, $fight[$i + 1]);
			array_push($fight_reverse, $fight[$i + 2]);
			
			$i += 6;
		}
		
		for($i=15;$i<30;$i++)
		{
			array_push($looks_reverse, $looks[$i]);
		}
		for($i=0;$i<14;$i++)
		{
			array_push($looks_reverse, $looks[$i]);
		}
		
		for($i=12;$i<24;$i++)
		{
			array_push($weapons_reverse, $weapons[$i]);
		}
		for($i=0;$i<12;$i++)
		{
			array_push($weapons_reverse, $weapons[$i]);
		}
		
		for($i=12;$i<23;$i++)
		{
			array_push($shield_reverse, $shield[$i]);
		}
		for($i=0;$i<12;$i++)
		{
			array_push($shield_reverse, $shield[$i]);
		}
		
		array_push($logs_reverse, join("/", $stats_reverse));
		array_push($logs_reverse, join("/", $fight_reverse));
		array_push($logs_reverse, join("/", $looks_reverse));
		array_push($logs_reverse, join("/", $weapons_reverse));
		array_push($logs_reverse, join("/", $shield_reverse));
		
		for($i=5;$i<10;$i++)
		{
			if($i == 8 OR $i == 9)
			{
				if($win)
				{
					array_push($logs_reverse, "-" . $logs[$i]);
				}
			}
			else
			{
				array_push($logs_reverse, $logs[$i]);
			}
			
		}
		
		return join("#", $logs_reverse);
	}
	
	public function guild_war($guild1, $guild1_members, $guild2, $guild2_members)
	{
		global $db;
		
		$ret = array("");
		$ret_reverse = array("");
		
		$round1 = 1;
		$round2 = 1;
		
		$load_newplayer1 = 1;
		$load_newplayer2 = 1;
		
		$player1_default_hp = 0;
		$player2_default_hp = 0;
		
		$win_guild_id = 0;
		
		$guild1_players_name = array_fill(0, 50, "");
		$guild2_players_name = array_fill(0, 50, "");
		
		$player1;
		$player2;
		
		for($i=0;$i<Count($guild1_members);$i++)
		{
			$qry = $db->query("SELECT `user_name` FROM `user_data` WHERE `user_id` = ".$guild1_members[$i]['user_id']."");
			$qry = $qry->fetch();
			$guild1_players_name[$i] =  $qry['user_name'];
		}
		
		for($i=0;$i<Count($guild2_members);$i++)
		{
			$qry = $db->query("SELECT `user_name` FROM `user_data` WHERE `user_id` = ".$guild2_members[$i]['user_id']."");
			$qry = $qry->fetch();
			$guild2_players_name[$i] =  $qry['user_name'];
		}
		
		While(Count($guild1_members) >= $round1 AND Count($guild2_members) >= $round2)
		{
			
			if($load_newplayer1 == 1)
			{
				$player1 = new Player;
				$player1->login_userid($guild1_members[$round1 - 1]['user_id']);
				$player1_load = $player1->get_ret();
				$player1_default_hp = $player1->hp;
				if($player1->data['guild_attack'] == 0)
				{
					$player1->hp = round($player1->hp / 2);
				}
			}
			
			if($load_newplayer2)
			{
				$player2 = new Player;
				$player2->login_userid($guild2_members[$round2 - 1]['user_id']);
				$player2_load = $player2->get_ret();
				$player2_default_hp = $player2->hp;
				if($player2->data['guild_defend'] == 0)
				{
					$player2->hp = round($player2->hp / 2);
				}
			}
			
			$ret[Count($ret) - 1] .= $player1_default_hp;
			array_push($ret, $player1->attr_str, $player1->attr_agi, $player1->attr_int, $player1->attr_wit, $player1->attr_luck);
			array_push($ret, $player2_default_hp, $player2->attr_str,  $player2->attr_agi, $player2->attr_int, $player2->attr_wit, $player2->attr_luck . ";");
			
			$ret_reverse[Count($ret_reverse) - 1] .= $player2_default_hp;
			array_push($ret_reverse, $player2->attr_str, $player2->attr_agi, $player2->attr_int, $player2->attr_wit, $player2->attr_luck);
			array_push($ret_reverse, $player1_default_hp, $player1->attr_str,  $player1->attr_agi, $player1->attr_int, $player1->attr_wit, $player1->attr_luck . ";");
		
			if($player1->hp != $player1_default_hp)
			{
				$hp1 = $player1->hp;
			}
			else
			{
				$hp1 = "-1";
			}
			
			if($player2->hp != $player2_default_hp)
			{
				$hp2 = $player2->hp;
			}
			else
			{
				$hp2 = "-1";
			}
			
			$fight = new Fight;
		
			$sim = $fight->fight_cal($player1, $player2);
			$sim_reverse = $sim[1];
			$sim = $sim[0];
			
			$ret[count($ret) - 1] .= $sim[0][0] . "/" . $sim[0][1] . "/" . $sim[0][2];
		
			for($i=1;$i<count($sim);$i++) 
			{
				array_push($ret, $sim[$i][0], $sim[$i][1], $sim[$i][2]);
			}
			
			$ret_reverse[count($ret_reverse) - 1] .= $sim_reverse[0][0] . "/" . $sim_reverse[0][1] . "/" . $sim_reverse[0][2];
		
			for($i=1;$i<count($sim_reverse);$i++) 
			{
				array_push($ret_reverse, $sim_reverse[$i][0], $sim_reverse[$i][1], $sim_reverse[$i][2]);
			}
		
			$ret[count($ret) - 1] .= $fight->load_view($player1, $player2);
			$ret_reverse[count($ret_reverse) - 1] .= $fight->load_view($player2, $player1);
			
			$ret[count($ret) - 1] .= ";2;0;0;0;-1;";
			$ret_reverse[count($ret_reverse) - 1] .= ";2;0;0;0;-1;";
        
			$ret[count($ret) - 1] .= $guild1['name'] . ";" . $guild2['name'] . ";§" . $hp1;
			$ret_reverse[count($ret_reverse) - 1] .= $guild2['name'] . ";" . $guild1['name'] . ";§" . $hp2;
			
			for($i=0;$i<5;$i++)
			{
				if(count($guild1_members) > $round1)
				{
					array_push($ret, $guild1_players_name[$i +  $round1]);
				}
				else
				{
					array_push($ret, "");
				}
			}
			
			for($i=0;$i<5;$i++)
			{
				if(count($guild2_members) > $round2)
				{
					array_push($ret_reverse, $guild2_players_name[$i +  $round1]);
				}
				else
				{
					array_push($ret_reverse, "");
				}
			}
			
			
			array_push($ret, $hp2);
			array_push($ret_reverse, $hp1);
			
			for($i=0;$i<5;$i++)
			{
				if(count($guild2_members) > $round2)
				{
					array_push($ret, $guild2_players_name[$i + $round2]);
				}
				else
				{
					array_push($ret, "");
				}
			}
			for($i=0;$i<5;$i++)
			{
				if(count($guild1_members) > $round1)
				{
					array_push($ret_reverse, $guild1_players_name[$i + $round1]);
				}
				else
				{
					array_push($ret_reverse, "");
				}
			}
			
			$ret[count($ret) - 1] .= "§";
			$ret_reverse[count($ret_reverse) - 1] .= "§";
			
			if($player1->hp > 0)
			{
				$round2++;
				$load_newplayer1 = 0;
				$load_newplayer2 = 1;
				$win_guild_id = $player1->data['guild_id'];
			}
			else
			{
				$round1++;
				$load_newplayer1 = 1;
				$load_newplayer2 = 0;
				$win_guild_id = $player2->data['guild_id'];
			}
        
		}
		
		return array($ret, $ret_reverse, $win_guild_id);
	}
	
	public function reverse_guild_war($log)
	{
		$ret = "";
		
		$logs = explode(";", $log);
		
		for($i=0;$i<Count($logs);$i++)
		{
			$data = $logs[$i];
			
			$stats = explode("/", $data[0]);
			
			for($i=7;$i<13;$i++)
			{
				$ret .= $stats[$i] . "/";
			}
			for($i=0;$i<7;$i++)
			{
				$ret .= $stats[$i] . "/";
			}
			$ret .= $stats[5];
			
			$ret .= ";" . $data[1] . ";";
			
			$view = explode("/", $data[2]);
			for($i=15;$i<29;$i++)
			{
				$ret .= $view[$i] . "/";
			}
			for($i=0;$i<15;$i++)
			{
				$ret .= $view[$i] . "/";
			}
			$ret .= $view[14];
			
			$ret .= ";";
			
			$stats_weapon = explode("/", $data[3]);
			for($i=12;$i<24;$i++)
			{
				$ret .= $stats_weapon[$i] . "/";
			}
			for($i=0;$i<11;$i++)
			{
				$ret .= $stats_weapon[$i] . "/";
			}
			
			$ret .= $stats_weapon[11];
			
			$ret .= ";";
			
			$stats_shield = explode("/", $data[4]);
			for($i=12;$i<24;$i++)
			{
				$ret .= $stats_shield[$i] . "/";
			}
			for($i=0;$i<11;$i++)
			{
				$ret .= $stats_shield[$i] . "/";
			}
			$ret .= $stats_shield[11];
			
		}
		
		return $ret;
	}
	
	public function calculateGuildHonor($guild1, $guild2, $win)
	{
		//TODO :P
		if ($win === TRUE) 
		{
			$diff = $guild1['honor'] - $guild2['honor'];
			$honor = 100 - ($diff / 10);
		}
		else 
		{
			$diff = $guild2['honor'] - $guild1['honor'];
			$honor = 100 - ($diff / 10);
		}
		
	   
		if ($honor < 0)
			$honor = 0;
		
		else if ($honor > 200)
			$honor = 200;
		
		
		if ($win === TRUE) 
		{
			if ($honor > $guild2['honor'])
				$honor = $guild2['honor'];
		}
		else 
		{
			if ($honor > $guild1['honor'])
				$honor = $guild1['honor'];
		}
		
		if ($honor < 0)
			$honor = 0;
			
		$a = $win ? 1 : -1;
		
		return $honor * $a;
	}
	
	public function finish_quest($player)
	{
		$ret = array();
		
		$mush_reward = 0;
		$exp_reward = 0;
		$gold_reward = 0;
		
		$monster = new Monster;
		$monster->load_monster_quest($player);
		
		array_push($ret, $player->hp, $player->attr_str, $player->attr_agi, $player->attr_int, $player->attr_wit, $player->attr_luck);
		
		array_push($ret, $monster->hp, $monster->attr_str,  $monster->attr_agi, $monster->attr_int, $monster->attr_wit, $monster->attr_luck . ";");
		
		$fight = new Fight;
		
		$sim = $fight->fight_cal($player, $monster)[0];
		
		$win = $sim[count($sim) - 1][0] <= 0 ? true : false;
		
		$ret[count($ret) - 1] .= $sim[0][0] . "/" . $sim[0][1] . "/" . $sim[0][2];
		
		for($i=1;$i<count($sim);$i++) 
		{
			array_push($ret, $sim[$i][0], $sim[$i][1], $sim[$i][2]);
		}
		
		if($win)
		{
			$exp_reward = $player->data['quest_exp_'.$player->data['status_extra']];
			$player->data['exp'] += $exp_reward;
			$gold_reward = $player->data['quest_gold_'.$player->data['status_extra']];
			$player->data['silver'] += $gold_reward;
			
			while($player->data['exp'] > Server::get_exp($player->data['lvl']))
			{
				$player->data['exp'] -= Server::get_exp($player->data['lvl']);
				$player->data['lvl']++;
			}
			
			if(rand(1, 100) <= Server::mush_chance() OR $player->data['thirst'] == 6000)
			{
				$player->data['mushroom'] += Server::mush_found();
				$mush_reward = Server::mush_found();
			}
			
			$item = new Item;
			
			if($item->get_item($player->data['user_id'], $player->data['status_extra'], "items_tavern"))
			{
				$free_slot = $player->get_free_slot();
				if($free_slot)
				{
					$item->item['slot'] = $free_slot;
					$item->insert_item($item->item, "items");
				}
			}
		}
		
		$player->data['thirst'] -= ($player->data['quest_dur_'.$player->data['status_extra']]);
		
		$GLOBALS['db']->query("UPDATE `user_data` SET 
			`exp` = '".$player->data['exp']."',
			`silver` = '".$player->data['silver']."', 
			`mushroom` = ".$player->data['mushroom'].",
			`lvl` = ".$player->data['lvl'].",
			`status` = 0,
			`thirst` = '".$player->data['thirst']."',
			`medal_adventurer` = medal_adventurer + 1
			WHERE `user_id` = ".$player->data['user_id']."");
			
		Server::generate_quests($player);
		
		$monster->face[1] = "-" . $monster->face[1];
		
		$ret[count($ret) - 1] .= $fight->load_view($player, $monster);
		
		$ret[count($ret) - 1] .= ";1;".$mush_reward.";".$exp_reward.";".$gold_reward.";-1";
		
		return $ret;
	}
	
	public function generate_quests($player)
	{
		global $SERVER_TIME_TOMORROW;
		
		for($i=1;$i<4;$i++)
		{
			$quest_gold = $player->get_gold(true);
			$quest_exp = $player->get_exp();
			
			$quest_exp += ($quest_exp * ("0" . $player->data['tower_level']));
			
			if($player->data['lvl'] < 5)
			{
				$quest_dur = rand(1, 5);
			}
			else if($player->data['lvl'] < 10)
			{
				$quest_dur = rand(1, 10);
			}
			else
			{
				$quest_dur = rand(5, 20);
			}
			
			if(round($player->data['thirst'] / 60) < 10 AND rand(1, 2) == 1 AND $player->data['thirst'] > 60)
			{
				$quest_dur = round($player->data['thirst'] / 60);
			}
			
			if($player->data['mount'] != 0 AND $quest_dur > 5)
			{
				$quest_dur = round($quest_dur - ($quest_dur * Server::mount_value($player->data['mount'])));
			}
			
			$quest_dur *=60;
			
			$GLOBALS['db']->query("UPDATE `user_data` SET `quest_dur_".$i."` = ".$quest_dur.", `quest_gold_".$i."` = ".$quest_gold.", `quest_exp_".$i."` = ".$quest_exp.", `quest_location_1` = ".rand(1, 20).",  `quest_location_2` = ".rand(1, 20).",  `quest_location_3` = ".rand(1, 20)." WHERE `user_id` = ".$player->data['user_id']."");
		
			$GLOBALS['db']->query("DELETE FROM `items_tavern` WHERE `owner_id` = ".$player->data['user_id']." AND `slot` = ".$i."");
			
			if(rand(1, 100) < 15 + $player->bonus_items)
			{
				$rand_type = rand(1, 10);
				$item = new Item;
				$item->gen_item($rand_type, $player->data['lvl'], $player->data['class']);
				$item_quest =  $item->item;
				for($e=1;$e<10;$e++)
				{
					if($player->data['dungeon_'.$e] == 0 AND $player->data['lvl'] >= ($e*10))
					{
						$item_quest = array(
							"id" => "NULL",
							"item_type" => "11",
							"item_id" => $e,
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
							"gold" => ($e * 10),
							"mush" => "0",
							"toilet" => "0",
							"slot" => "0",
							"owner_id" => "0"
						);
						$e = 13;
					}
				}
				
				if($item_quest['item_type'] != 11)
				{
					if($player->data['lvl'] > 100 AND $player->data['toilet'] == 0 AND $player->search_item(20, 11, $player->data['user_id']) == false)
					{
						$item_quest = array(
						"id" => "NULL",
						"item_type" => "11",
						"item_id" => "20",
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
						"gold" => "10000",
						"mush" => "0",
						"toilet" => "0",
						"slot" => "0",
						"owner_id" => "0");
					}
				}
				
				$item_quest['owner_id'] = $player->data['user_id'];
				$item_quest['slot'] = $i;
				$item_quest['mush'] = 0;
				$item->insert_item($item_quest, "items_tavern");
			}
		}
	}
	
	public function event()
	{
		$qry = $GLOBALS['db']->query("SELECT `value` FROM `game_settings` WHERE `setting` = 'event' LIMIT 1");
		$res = $qry->fetch();
		$event = $res['value'];
		return $event;
	}
	
	public function event_special()
	{
		$qry = $GLOBALS['db']->query("SELECT `value` FROM `game_settings` WHERE `setting` = 'event_special' LIMIT 1");
		$res = $qry->fetch();
		$event_special = $res['value'];
		return $event_special;
	}
	
	public function players_count()
	{
		$qry = $GLOBALS['db']->query("SELECT Count(*) FROM `user_data`");
		$qry = $qry->fetch();
		
		return $qry[0];
	}
	
	public function mush_chance()
	{
		$qry = $GLOBALS['db']->query("SELECT `value` FROM `game_settings` WHERE `setting` = 'mush_chance' LIMIT 1");
		$res = $qry->fetch();
		return $res['value'];
	}
	
	public function mush_found()
	{
		$qry = $GLOBALS['db']->query("SELECT * FROM `game_settings` WHERE `setting` = 'mush_drop_found' LIMIT 1");
        $res = $qry->fetch();
		return $res['value'];
	}
	
	public function quest_skip()
	{
		$qry = $GLOBALS['db']->query("SELECT * FROM `game_settings` WHERE `setting` = 'quest_skip' LIMIT 1");
        $res = $qry->fetch();
		
		if($res['value'] == 1)
		{
			return "193";
		}
		else
		{
			return "108";
		}
	}
        
	public function utf8_format($text)
	{
		$text = str_replace('%u0142', 'ł', $text); 
		$text = str_replace('%u0141', 'Ł', $text);
		$text = str_replace('%u0105', 'ą', $text);
		$text = str_replace('%u0104', 'Ą', $text);
		$text = str_replace('%u0119', 'ę', $text);
		$text = str_replace('%u0118', 'Ę', $text);
		$text = str_replace('%u0107', 'ć', $text);
		$text = str_replace('%u0106', 'Ć', $text);
		$text = str_replace('%u0143', 'Ń', $text);
		$text = str_replace('%u0144', 'ń', $text);
		$text = str_replace('%u015B', 'ś', $text);  
		$text = str_replace('%u015A', 'Ś', $text);  
		$text = str_replace('%u017C', 'ż', $text);  
		$text = str_replace('%u017B', 'Ż', $text);  
		$text = str_replace('%u017A', 'ź', $text);  
		$text = str_replace('%u0179', 'Ź', $text);  
		$text = str_replace('%40', '@', $text);  
		return $text;
	}
	
	public function mount_cost($mount)
	{
		$ret = array();
		switch ($mount) 
		{
			case 1:
				$ret['silver'] = 100;
				$ret['mushroom'] = 0;
			break;
			case 2:
				$ret['silver'] = 500;
				$ret['mushroom'] = 0;
			break;
			case 3:
				$ret['silver'] = 1000;
				$ret['mushroom'] = 1;
		   break;
			case 4:
				$ret['silver'] = 0;
				$ret['mushroom'] = 25;
			break;
		}
	   return $ret;
	}

	public function mount_value($mount)
	{
		switch($mount) 
		{
			case 0:
				return '1';
			break;
			case 1:
				return '0.9';
			break;
			case 2:
				return '0.8';
			break;
			case 3:
				return '0.7';
			break;
			case 4:
				return '0.5';
			break;
		}
	}
	
	public function check_account_ip($ip)
	{
		$qry = $GLOBALS['db']->query("SELECT `user_id` FROM `user_data` WHERE `last_ip` = '".$ip."'");
		if($qry->rowCount() >= 3)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	public function get_code()
	{
		$dummy = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
		 mt_srand((double) microtime() * 1000000);
		 for($i = 1; $i <= (count($dummy) * 2); $i++) 
		 {
			 $swap = mt_rand(0, count($dummy) - 1);
			 $tmp = $dummy[$swap];
			 $dummy[$swap] = $dummy[0];
			 $dummy[0] = $tmp;
		  }
		  return substr(implode('', $dummy), 0, 8);
	}

	public function get_exp($lvl)
	{
		return [0,150,250,500,750,2500,5000,7515,8925,10335,11975,13715,15730,17745,20250,22755,25620,28660,32060,35460,39535,43610,48155,52935,58260,63585,69760,75935,82785,89905,97695,105485,114465,123445,133260,143425,154545,165665,178210,190755,204430,218540,233785,249030,266140,283250,301715,320685,341170,361655,384360,407065,431545,456650,483530,510410,540065,569720,601435,633910,668670,703430,741410,779390,819970,861400,905425,949450,997485,1045520,1096550,1148600,1203920,1259240,1319085,1378930,1442480,1507225,1575675,1644125,1718090,1792055,1870205,1949685,2033720,2117755,2208040,2298325,2393690,2490600,2592590,2694580,2803985,2913390,3028500,3145390,3268435,3391480,3522795,3654110,3792255,3932345,4079265,4226185,4382920,4539655,4703955,4870500,5045205,5219910,5405440,5590970,5785460,5982490,6188480,6394470,6613125,6831780,7060320,7291640,7533530,7775420,8031275,8287130,8554570,8825145,9107305,9389465,9687705,9985945,10296845,10611275,10939230,11267185,11612760,11958335,12318585,12682650,13061390,13440130,13839160,14238190,14653230,15072545,15508870,15945195,16403485,16861775,17338505,17819980,18319895,18819810,19344795,19869780,20414715,20964770,21536005,22107240,22705735,23304230,23925545,24552535,25202340,25852145,26532725,27213305,27918540,28630050,29367610,30105170,30875945,31646720,32445505,33251010,34084530,34918050,35789075,36660100,37561220,38469755,39410080,40350405,41330960,42311515,43326065,44348735,45405405,46462075,47563900,48665725,49804020,50951005,52136360,53321715,54555530,55789345,57064175,58348500,59673840,60999180,62378435,63757690,65180715,66614100,68093535,69572970,71110105,72647240,74233350,75830465,77476555,79122645,80832985,82543325,84305910,86080505,87909870,89739235,91636870,93534505,95490375,97459260,99486380,101513500,103616290,105719080,107883715,110062180,112305475,114548770,116872700,119196630,121589225,123996780,126473000,128949220,131514215,134079210,136717090,139371155,142101400,144831645,147656105,150480565,153385655,156307860,159310695,162313530,165420140,168526750,171718645,174929030,178228565,181528100,184937365,188346630,191849945,195373130,198990370,202607610,206345275,210082940,213920015,217778100,221739815,225701530,229790630,233879730,238078150,242299140,246629445,250959750,255429090,259898430,264482960,269091720,273820565,278549410,283425105,288300800,293302740,298330180,303483865,308637550,313951595,319265640,324712695,330187105,335799860,341412615,347193920,352975225,358901970,364857940,370959350,377060760,383345695,389630630,396068325,402536785,409164155,415791525,422612215,429432905,436420230,443440385,450627180,457813975,465210300,472606625,480177945,487784290,495572280,503360270,511368340,519376410,527574890,535810100,544235725,552661350,561325655,569989960,578853765,587756750,596866840,605976930,615337095,624697260,634274025,643892430,653727435,663562440,673667980,683773520,694105920,704481995,715093150,725704305,736599015,747493725,758634285,769821230,781254025,792686820,804425165,816163510,828158780,856843923,903283173,921348836,939775812,958571328,977742755,997297610,1017243562,1037588434,1058340202,1079507006,1101097146,1123119089,1145581470,1168493100,1191862962,1215700221,1240014225,1264814510,1290110800,1315913016,1342231277,1369075902,1396457420,1424386569,1452874300,1481931786,1511570422,1541801830,1572637867,1604090624,1636172436,1668895885,1702273803,1736319279,1771045664,1806466578,1842595909,1879447828,1917036784,1955377520,1994485070,2034374772,2075062267,2116563512,2158894783,2202072678,2246114132,2291036414,2336857143,2383594286,2431266171,2479891495,2529489325,2580079111,2631680693,2684314307,2738000593,2792760605,2848615817,2905588134,2963699896,3022973894,3083433372,3145102040,3208004080,3272164162,3337607445,3404359594,3472446786,3541895722,3612733636,3684988309,3758688075,3833861837,3910539073,3988749855,4068524852,4149895349,4232893256,4317551121,4403902143,4491980186,4581819790,4673456186,4766925310,4862263816,4959509092,5058699274,5159873259,5263070725,5368332139,5475698782,5585212757,5696917013,5810855353,5927072460,6045613909,6166526187,6289856711,6415653845,6543966922,6674846261,6808343186,6944510049,7083400250,7225068255,7369569621,7516961013,7667300233,7820646238,7977059163,8136600346,8299332353,8465319000,8634625380,8807317888,8983464245,9163133530,9346396201,9533324125,9723990607,9918470419,10116839828,10319176624,10525560157,10736071360,10950792787,11169808643,11393204816,11621068912,11853490290,12090560096,12332371298,12579018724,12830599099,13087211081,13348955302,13615934408,13888253096,14166018158,14449338521,14738325292,15033091798,15333753634,15640428706,15953237280,16272302026,16597748067,16929703028,17268297088,17613663030,17965936291,18325255017,18691760117,19065595319,19446907226,19835845370,20232562278,20637213523,21049957794,21470956950,21900376089,22338383610,22785151282,23240854308,23705671394,24179784822,24663380519,25156648129,25659781092,26172976713,26696436248,27230364973,27774972272,28330471718,28897081152,29475022775,30064523230,30665813695,31279129969,31904712568,32542806820,33193662956,33857536215,34534686939,35225380678,35929888292,36648486058,37381455779,38129084894,38891666592,39669499924,40462889923,41272147721,42097590676,42939542489,43798333339,44674300006,45567786006,46479141726,47408724560,48356899052,49324037033,50310517773,51316728129,52343062691,53389923945,54457722424,55546876872,56657814410,57790970698,58946790112,60125725914,61328240433,62554805241,63805901346,65082019373,66383659760,67711332956,69065559615,70446870807,71855808223,73292924388,74758782875,76253958533,77779037704,79334618458,80921310827,82539737043,84190531784,85874342420,87591829268,89343665854,91130539171,92953149954,94812212953,96708457212,98642626357,100615478884,102627788461,104680344231,106773951115,108909430138,111087618740,113309371115,115575558537,117887069708,120244811102,122649707324,125102701471,127604755500,130156850610,132759987622,135415187375,138123491122,140885960945,143703680164,146577753767,149509308842,152499495019,155549484920,158660474618,161833684110,165070357793,168371764948,171739200247,175173984252,178677463937,182251013216,185896033480,189613954150,193406233233,197274357898,201219845056,205244241957,209349126796,213536109332,217806831518,222162968149,226606227512,231138352062,235761119103,240476341485,245285868315,250191585681,255195417395,260299325743,265505312258,270815418503,276231726873,281756361410,287391488639,293139318411,299002104780,304982146875,311081789813,317303425609,323649494121,330122484004,336724933684,343459432357,350328621004,357335193424,364481897293,371771535239,379206965944,386791105262,394526927368,402417465915,410465815233,418675131538,427048634169,435589606852,444301398989,453187426969,462251175508,471496199019,480926122999,490544645459,500355538368,510362649135,520569902118,530981300161,541600926164,552432944687,563481603581,574751235652,586246260365,597971185573,609930609284,622129221470,634571805899,647263242017,660208506858,673412676995,686880930535,700618549145,714630920128,728923538531,743502009301,758372049487,773539490477,789010280287,804790485892,820886295610,837304021523,854050101953,871131103992,888553726072,906324800593,924451296605,942940322537,961799128988,981035111568,1000655813799,1020668930075,1041082308677,1061903954850,1083142033947,1104804874626,1126900972119,1149438991561,1172427771392,1195876326820,1219793853356,1244189730424,1269073525032,1294454995533,1320344095443,1346750977352,1373685996899,1401159716837,1429182911174,1457766569398,1486921900785,1516660338801,1546993545577,1577933416489,1609492084819,1641681926515,1674515565045,1708005876346,1742165993873,1777009313750,1812549500025,1848800490026,1885776499827,1923492029823,1961961870420,2001201107828,2041225129984,2082049632584,2123690625236,2166164437741,2209487726495,2253677481025,2298751030646,2344726051259,2391620572284,2439452983730,2488242043404,2538006884272,2588767021958,2640542362397][$lvl];
	}
	
	public function get_default_stats($class, $race)
	{
		$ret = array_fill(0, 5, '10');

		switch($class)
		{
			case 1:
				$ret[0] += 7;
				$ret[1] += 3;
				$ret[2] += 0;
				$ret[3] += 5;
				$ret[4] += 0;
			break;
			case 2: 
				$ret[0] += 0;
				$ret[1] += 0;
				$ret[2] += 8;
				$ret[3] += 2;
				$ret[4] += 5;
			break;
			case 3:
				$ret[0] += 1;
				$ret[1] += 7;
				$ret[2] += 1;
				$ret[3] += 4;
				$ret[4] += 2;
			 break;
		}
		switch($race) 
		{
			case 1:
				$ret[0] -= 0;
				$ret[1] += 0;
				$ret[2] += 0;
				$ret[3] -= 0;
				$ret[4] += 0;
			break;
			case 2:
				$ret[0] -= 1;
				$ret[1] += 2;
				$ret[2] += 0;
				$ret[3] -= 1;
				$ret[4] += 0;
			break;
			case 3:
				$ret[0] -= 0;
				$ret[1] += 2;
				$ret[2] += 1;
				$ret[3] -= 2;
				$ret[4] += 1;
			break;
			case 4:
				$ret[0] -= 2;
				$ret[1] += 3;
				$ret[2] += 1;
				$ret[3] -= 1;
				$ret[4] += 1;
			break;
			case 5:
				$ret[0] -= 1;
				$ret[1] += 0;
				$ret[2] += 1;
				$ret[3] -= 0;
				$ret[4] += 0;
			break;
			case 6:
				$ret[0] -= 2;
				$ret[1] += 2;
				$ret[2] += 1;
				$ret[3] -= 1;
				$ret[4] += 0;
			break;
			case 7:
				$ret[0] -= 2;
				$ret[1] += 2;
				$ret[2] += 0;
				$ret[3] -= 1;
				$ret[4] += 1;
			break;
			case 8:
				$ret[0] -= 3; 
				$ret[1] += 1;
				$ret[2] += 0;
				$ret[3] -= 1;
				$ret[4] += 3;
			break;
		}
		return $ret;
	}
}
?>