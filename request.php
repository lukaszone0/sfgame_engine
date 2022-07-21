<?php
/*
TODO 35%
- guilad 50%
- towet
- pvp message fight
- potion check :/
*/
ini_set('display_errors', '1');

include("engine/globals.php");
include("engine/account.php");
include("engine/item.php");
include("engine/server.php");
include("engine/player.php");
include("engine/monster.php");
include("engine/fight.php");
include("engine/guild.php");
include("engine/tower.php");
include("engine/helper.php");

try
{
	$db = NEW PDO("mysql:host=localhost;dbname=sfgame;", "root", "");
}
catch(PDOException $e)
{
	 exit('Połączenie nie mogło zostać utworzone: ' . $e->getMessage());
}

$ret = array_fill(0, 511, "0");

if(!isset($_GET['req']))
{
	exit("Błąd: Brak parametrów !");
}

$SSID = "00000000000000000000000000000000002";

if(isset($_COOKIE['gosf_ssid']))
{
	$SSID = $_COOKIE['gosf_ssid'];
}

$ACT = addslashes(substr($_GET['req'], 32, 3));
$DATA = addslashes(substr($_GET['req'], 35));

switch ($ACT)
{
    case $ACT_REGISTER :
		
		$data = explode(";", $DATA);
		
		$nick = $data[0];
		$password = $data[1];
		$email = $data[2];
		$race = $data[5];
		$gender = $data[6];
		$class = $data[7];
		$face = explode("/", $data[8]);
		
		if(strlen($nick) < 3 OR strlen($nick) > 20) 
		{
			$ret = array($ERR_NAME_TOO_SHORT);
			break;
		}
		
		if(preg_match("/[^-a-z0-9_]/i", $nick))
		{
			$ret = array($ERR_NAME_EXISTS);
			break;
		}
		
		if(is_numeric($nick))
		{
			$ret = array($ERR_NAME_EXISTS);
			break;
		}
	
		if(strlen($password) < 4) 
		{
			$ret = array($ERR_PASSWORD_TOO_SHORT);
			break;
		}
         
        if(filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE)
		{ 
			$ret = array($ERR_EMAIL_WRONG);
			break; 
		}
		
		if(!Account::check_nick($nick))
		{
			$ret = array($ERR_NAME_EXISTS);
			break;
		}
		
		if(!Account::check_email($email))
		{
			$ret = array($ERR_EMAIL_EXISTS);
			break;
		}
		
		if(!Account::check_account_ip($SERVER_IP))
		{
			$ret = array($ERR_ACCOUNTS_PER_IP);
			break;
		}
		
		$user_id = Account::register($data);
		
		$player = new Player;
		$player->login_userid($user_id);
		Server::generate_quests($player);
		
		$ret = array($ACT_REGISTER . $user_id);
		
	break;
	case $ACT_LOGIN:
		
		$data = explode(';', $DATA);
        
        $nick = $data[0];
        $password = $data[1];
		
		$qry = $db->prepare("SELECT * FROM `user_data` WHERE `user_name` = :name AND `password` = :password LIMIT 1");
        $qry->bindParam(':name', $nick);
        $qry->bindParam(':password', $password);
        $qry->execute();
        
        if($qry->rowCount() != 1)
		{
            $ret = array($ERR_LOGIN_FAILED);
            break;
        }
		
		$player_data = $qry->fetch();
		
		if(!$player_data['enabled'])
		{
            $ret = array($ERR_LOCKED_ADMIN);
            break;
        }
		
		$SSID = md5(microtime() . rand(1, 99));
		
		$db->query("UPDATE `user_data` SET 
		`ssid` = '".$SSID."', 
		`last_ip` = '".$SERVER_IP."', 
		`last_activ` = ".$SERVER_TIME." 
		WHERE `user_id` = ".$player_data['user_id']."");
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		setcookie("gosf_ssid", $SSID, ($GLOBALS['SERVER_TIME'] + 3600));
		
		$ret = $player->get_ret();
		$ret[0] = $ACT_LOGIN . $ret[0];
		$ret[] .= ";0;000000000000000zeusxp00000000000;0;534;0";
		
	break;
    case $ACT_LOGOUT:
		
		$qry = $db->prepare("UPDATE `user_data` SET ssid = '0', `gchat_last` = '0' WHERE `ssid` = :ssid");
        $qry->bindParam(':ssid', $SSID);
        $qry->execute();
		
		$ret = array($SERVER_LOGIN_SUCCESS_BOUGHT);
		
	break;
	case $ACT_LOGIN_FOLLOW_UP:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$guild = array(
		"name"=>"", 
		"guild_id"=>"");
		
		if($player->data['guild_id'] !=0)
		{
			$guild_check = $player->get_guild();
			if($guild_check)
			{
				$guild['name'] = $guild_check['name'];
				$guild['guild_id'] = $guild_check['guild_id'];
			}
		}
		else
		{
			break;
		}
		
		$ret = array( "+101".$guild['name'].";".$guild['guild_id']);
		
	break; 
	case $ACT_TAVERN_ENTER:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['status'] == 1)
		{
			$ret = array( $ACT_WORK_ENTER . $player->get_gold($quest = false) . ";1");
			break;
		}
		else if($player->data['status'] == 2 AND $SERVER_TIME >= $player->data['status_end'])
		{
			$player->get_ret();
			
			$ret = Server::finish_quest($player);

			$ret[0] = $SERVER_QUEST_DONE . $ret[0];
			
			break;
		}
		else if($SERVER_TIME >= $player->data['quest_reroll_time'])
		{
			$db->query("UPDATE `user_data` SET `thirst` = 6000, `quest_reroll_time` = ".$SERVER_TIME_TOMORROW." WHERE `user_id` = ".$player->id."");
			Server::generate_quests($player);
			$player->login($SSID);
		}
		
		$ret = $player->get_ret();
		
		$ret[0] = Server::quest_skip() . $ret[0];
		
	break;
	case $ACT_START_QUEST:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['status'] != 0)
		{
			break;
		}
		
		if($DATA[0] < 1 OR $DATA[0] > 3 OR !intval($DATA[0]))
		{
			break;
		}
		
		$quest_dur = $player->data['quest_dur_' . $DATA[0]];
		
		if($player->data['thirst'] < $quest_dur) 
		{
            $ret = array($ERR_NO_ENDURANCE);
            break;
        }
		
		$qry = $db->query("SELECT * FROM `items_tavern` WHERE `owner_id` = ".$player->id." AND `slot` =".$DATA[0]."");
		
		if($qry->rowCount() == 1)
		{
			if($player->get_free_slot() == 0)
			{
				$ret = array($ERR_INVENTORY_FULL);
				break;
			}
		}
		
		$qry = $db->query("UPDATE `user_data` SET `status` = 2, `status_extra` = ".$DATA[0].", `status_end` = ". ($quest_dur + $SERVER_TIME)." WHERE `user_id` =".$player->id."");
		
		$player->login($SSID);
		
		$ret = $player->get_ret();
		
		$ret[0] = Server::quest_skip() . $ret[0];
		
	break;
    case $ACT_QUEST_CANCEL:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['status'] != 2)
		{
			break;
		}
		
		$qry = $db->query("UPDATE `user_data` SET `status` = 0, `status_extra` = 0, `status_end` = 0 WHERE `user_id` =".$player->id."");

		$player->login($SSID);
		
		$ret = $player->get_ret();
		
        $ret[0] = $SERVER_QUEST_STOP . $ret[0];
		
	break;
    case $ACT_QUEST_SKIP:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['status'] != 2)
		{
			break;
		}
		
		if($player->data['mushroom'] < 1)
		{
			$ret = array($ERR_NO_MUSH_BAR);
            break;
		}
		
		if(Server::quest_skip() != "193")
		{
			break;
		}
		
		$player->get_ret();
		
		$player->data['mushroom'] --;
			
		$ret = Server::finish_quest($player);

		$ret[0] = $SERVER_QUEST_DONE . $ret[0];
		
	break;
    case $ACT_BARN:
	
	$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
	$ret = array("015");
	
	break;
	case $ACT_DRINK_BEER:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['beers'] >= 11) 
		{
            $ret = array($ERR_BEER);
            break;
        }
		
		if(Server::event() != 6)
		{
			if($player->data['mushroom'] < 1)
			{
				$ret = array($ERR_NO_MUSH_BAR);
				break;
			}
		}
		
		if($player->data['thirst'] >= 5800)
		{
			break;
		}
		
		$player->data['mushroom'] --;
		$player->data['thirst'] +=1200;
		
		$db->query("UPDATE `user_data` SET `thirst` = ".$player->data['thirst'].", `mushroom` = ".$player->data['mushroom'].", `beers` = beers + 1 WHERE `user_id` =".$player->id."");
		
		$ret = $player->get_ret();
		$ret[0] = "010" . $ret[0];
		
    break;
	case $ACT_SCREEN_TOILET:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($SERVER_TIME > $player->data['toilet_time'])
		{
			$player->data['toilet_time'] = strtotime('tomorrow');
			$db->query("UPDATE `user_data` SET `toilet_time` =".$player->data['toilet_time'].", `toilet_full` = 0 WHERE `user_id` = ".$player->id."");
		}
		
		$ret = $player->get_ret();
		
		if($player->data['lvl'] < 100 OR $player->data['toilet'] == 0)
		{
			$ret[0] = $SERVER_TOILET_LOCKED . $ret[0];
			break;
		}
		else
		{
			$ret[0] = $ACT_SCREEN_TOILET . $ret[0];
		}
		
		 $ret[] = ";" . $player->data['toilet_full'] . ";" . $player->data['aura'] . ";" . $player->data['fill_level'] . ";" . $player->data['fill_level_next'];
		
	break;
    case $ACT_TOILET_FLUSH:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$ret = $player->get_ret();
		
		if($player->data['fill_level'] < $player->data['fill_level_next'])
		{
			$ret[0] = $ERR_TOILET_EMPTY . $ret[0];
			$ret[] = ";" . $player->data['toilet_full'] . ";" . $player->data['aura'] . ";" . $player->data['fill_level'] . ";" . $player->data['fill_level_next'];
			break;
		}
		
		$slot = $player->get_free_slot();
		
		if(!$slot)
		{
			$ret[0] = $ERR_NO_SLOT_FOR_FLUSHING . $ret[0];
			$ret[] = ";" . $player->data['toilet_full'] . ";" . $player->data['aura'] . ";" . $player->data['fill_level'] . ";" . $player->data['fill_level_next'];
			break;
		}
		else
		{
			$item = new Item;
			$item->gen_item(rand(1, 10), $player->data['lvl'], $player->data['class']);
			$item_new = $item->item;
			$item_new['owner_id'] = $player->id;
			$item_new['slot'] = $slot;
			$item->insert_item($item_new, "items");
			$player->data['fill_level_next'] = (150 + ($player->data['aura'] + 1) * 50);
			$db->query("UPDATE `user_data` SET `fill_level` = 0, `toilet_full` = 0, `aura` = aura + 1, `fill_level_next` = ".$player->data['fill_level_next']." WHERE `user_id` = ".$player->id."");
			$player->login($SSID);
			$ret = $player->get_ret();
			$ret[0] = $SERVER_TOILET_FLUSHED . $ret[0];
			$ret[] = ";" . $player->data['toilet_full'] . ";" . $player->data['aura'] . ";" . $player->data['fill_level'] . ";" . $player->data['fill_level_next'];
		}
		
	break;
	case $ACT_ARENA_ENTER:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['status'] == 2)
		{
            $ret = $player->get_ret();
			$ret[0] = Server::quest_skip() . $ret[0];
        }
		else if($player->data['status'] == 1)
		{
			$ret = array( $ACT_WORK_ENTER . $player->get_gold($quest = false) . ";1");
			break;
		}
		
		$qry = $db->prepare("SELECT user_name, lvl, guild_id, ABS( honor - (SELECT honor FROM user_data WHERE ssid = :ssid) ) AS distance FROM ((
		    SELECT user_name, lvl, guild_id, honor  FROM `user_data` WHERE honor >= (SELECT honor FROM user_data WHERE ssid = :ssid) AND ssid NOT IN (:ssid)
		    ORDER BY honor
		    LIMIT 10) UNION ALL (SELECT user_name, lvl, guild_id, honor FROM `user_data`
			WHERE honor < (SELECT honor FROM user_data WHERE ssid = :ssid) AND ssid NOT IN (:ssid) ORDER BY honor DESC LIMIT 10)) AS n ORDER BY RAND() LIMIT 1");
        $qry->bindParam(':ssid', $SSID);
        $qry->execute();
		if($qry->rowCount() < 1)
		{
			$ret = array("011");
			break;
		}
        $user_data = $qry->fetchAll();
        $user_data = $user_data[0];
		
		if($user_data['guild_id'] != 0)
		{
			$qry = $db->prepare("SELECT `name` FROM `guilds` WHERE `guild_id` = :gid");
			$qry->bindParam(':gid', $user_data['guild_id']);
			$qry->execute();
			$guild = $qry->fetch();
		}
        else
		{
			$guild['name'] = "";
		}
		
		$ret = array( "011" . $user_data['user_name'] . ";" . $user_data['lvl'] . ";" . $guild['name'] . ";0/");
		
	break;
    case $ACT_ARENA:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['status'] != 0)
		{
			break;
		}
		
		$nick = htmlspecialchars($DATA);
		
		if($nick == "")
		{
			$ret = array($ERR_FIGHT_PLAYER);
			break;
		
		}
		if($player->data['user_name'] == $nick)
		{
			$ret = array($ERR_FIGHT_SELF );
			break;
		}
		
		$player2 = new Player;
		
		if($player2->login_username($nick))
		{
			$ret = array($ERR_GUILD_PLAYER_NOT_FOUND);
			break;
		}
		
		if($SERVER_TIME < $player->data['arena_time'])
		{
			if($player->data['mushroom'] < 1)
			{
				$ret = array( $ERR_NO_MUSH_PVP );
				break;
			}
			$player->data['arena_time'] += ($SERVER_TIME + 600);
			$player->data['mushroom'] --;
		}
		
		$load1 = $player->get_ret();
		$load2 = $player2->get_ret();
		
		$ret = Server::pvp($player, $player2);
		
		$ret[0] = "106". $ret[0];

	break;
	case $ACT_WORK_ENTER:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['status'] == 2)
		{
			$ret = $player->get_ret();
            //$ret[0] = "010" . $ret[0];
			$ret[0] = Server::quest_skip() . $ret[0];
			
			break;
        }
		else if($player->data['status'] == 1 AND $SERVER_TIME > $player->data['status_end'])
		{
            $gold_reward = ($player->get_gold($quest = false) * $player->data['status_extra']);
            $player->data['medal_employment']++;
			$player->data['silver'] +=$gold_reward;
			
            $qry = $db->prepare("UPDATE `user_data` SET `status` = 0, `silver` = :silver, `medal_employment` = :medal_employment WHERE `ssid` = :ssid");
            $qry->bindParam(':ssid', $SSID);
            $qry->bindParam(':silver', $player->data['silver']);
            $qry->bindParam(':medal_employment', $player->data['medal_employment']);
            $qry->execute();
            
			$player->login($SSID);
			$ret = $player->get_ret();
			
            $ret[0] = $SERVER_WORK_END . $ret[0];
            $ret[] = ";" . $gold_reward;
			
		}
		else
		{
			$ret = $player->get_ret();
			$ret = array($ACT_WORK_ENTER . $player->get_gold($quest = false) . ";0");
		}
		
	break;
    case $ACT_WORK:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['status'] != 0)
		{
			break;
		}
		
		$end_work = $SERVER_TIME + (3600 * ( int ) $DATA);
		
		$qry = $db->prepare("UPDATE `user_data` SET `status` = 1, `status_end` = :end_work, `status_extra` = :hours WHERE `ssid` = :ssid");
        $qry->bindParam(':end_work', $end_work);
        $qry->bindParam(':ssid', $SSID);
        $qry->bindParam(':hours', $DATA);
        $qry->execute();
		
		$player->login($SSID);
		$ret = $player->get_ret();
		
		$ret[0] = $SERVER_WORK_START . $ret[0];
		
	break;
    case $ACT_WORK_CANCEL:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['status'] != 1)
		{
			break;
		}
		
		$qry = $db->prepare("UPDATE `user_data` SET `status` = 0 WHERE `ssid` = :ssid");
        $qry->bindParam(':ssid', $SSID);
        $qry->execute();
		
		$player->login($SSID);
		$ret = $player->get_ret();
		
		$ret[0] = $SERVER_WORK_STOP . $ret[0];
		
	break;
	case $ACT_ENTER_SHOP_SHAKES:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($SERVER_TIME > $player->data['shop_reroll_time'])
		{
			$player->reroll_items_shop("items_fidget");
			$player->reroll_items_shop("items_shakes");
		}
		
		$ret = $player->get_ret();
		
		$ret[0] = $ACT_ENTER_SHOP_SHAKES . $ret[0];
        
        $ret[] = ";" . Server::event();
		
    break;
    case $ACT_ENTER_SHOP_FIDGET:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($SERVER_TIME > $player->data['shop_reroll_time'])
		{
			$player->reroll_items_shop("items_fidget");
			$player->reroll_items_shop("items_shakes");
		}
		
		$ret = $player->get_ret();
		
		$ret[0] = $ACT_ENTER_SHOP_FIDGET . $ret[0];
        
        $ret[] = ";" . Server::event();
		
    break;
	case $ACT_SCREEN_WITCH: 
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['lvl'] < 66)
		{
			break;
		}
		
		$ret = $player->get_ret();
		
		$ret [0] = $SERVER_SCREEN_WITCH . $ret [0];

		$ret[] = $player->load_witch_data();
		
	break;		
    case $ACT_WITCH_ENCHANT:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$in = addslashes($DATA);
		
		if(!intval($in))
		{
			break;
		}

		$cost = $player->data['lvl'] * 65536;
		
		if($player->data['silver'] < $cost)
		{
			$ret = array ($ERR_TOO_EXPENSIVE);
			break;
		}
		
		$player->data['silver'] -=$cost;
		
		/*$i = $item['slot'];
		$GLOBALS['ret'][$i] = $item['enchant'] + $item['item_type'];
		$GLOBALS['ret'][$i + 1] = $item['enchant_power'] + $item['item_id'];*/
		
		$item = new Item;
		if(!$item->get_item($player->id, ($item->get_enchant_id($in) + 1), "items"))
		{
			break;
		}
		
		switch($in)
		{
			case 1:
				$item->item['enchant'] = 855638016;
				$item->item['enchant_power'] = 65536;
			break;
			case 2:
				$item->item['enchant'] = 1694498816;
				$item->item['enchant_power'] = 1310720;
			break;
			case 3:
				$item->item['enchant'] = 1191182336;
				$item->item['enchant_power'] = 65536;
			break;
			case 4:
				$item->item['enchant'] = 1526726656;
				$item->item['enchant_power'] = 655360;
			break;
			case 5:
				$item->item['enchant'] = 687865856;
				$item->item['enchant_power'] = 655360;
			break;
			case 6:
				$item->item['enchant'] = 1023410176;
				$item->item['enchant_power'] = 655360;
			break;
			case 7:
				$item->item['enchant'] = 1358954496;
				$item->item['enchant_power'] = 655360;
			break;
			case 8:
				$item->item['enchant'] = 520093696;
				$item->item['enchant_power'] = 1638400;
			break;
			case 9:
				$item->item['enchant'] = 184549376;
				$item->item['enchant_power'] = 327680;
			break;
		}
		
		$item->update_item($item->item, "items");
		
		$db->query("UPDATE `user_data` SET `silver` = ".$player->data['silver']." WHERE `user_id` =".$player->id."");
		
		$player->login($SSID);
		$ret = $player->get_ret();
		
		$ret [0] = $SERVER_SCREEN_WITCH . $ret [0];

		$ret[] = $player->load_witch_data();
		
	break;
	case $ACT_WITCH_DONATE :
		//TODO
	break;
	case $ACT_REROLL_ITEMS:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['mushroom'] < 1)
		{
			$ret = array($ERR_NO_MUSH_MQ);
			break;
		}
		
		$shop_id = intval($DATA - 1);
		
		if($shop_id == 1)
		{
			$shop = "items_shakes";
			$act_shop = $ACT_ENTER_SHOP_SHAKES;
		}
		else if($shop_id == 0)
		{
			$shop = "items_fidget";
			$act_shop = $ACT_ENTER_SHOP_FIDGET;
		}
		else
		{
			break;
		}
		
		$db->query("UPDATE `user_data` SET `mushroom` = ". ($player->data['mushroom'] - 1) ." WHERE `user_id` = ".$player->id."");
		
		$player->reroll_items_shop($shop);
		
		$player->login($SSID);
		
		$ret = $player->get_ret();
		
		$ret[0] = $act_shop . $ret[0];
		
		$ret[] .= ";". Server::event();
		
    break;
	case $ACT_BUY_MOUNT:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$mount_select = $DATA;
		$mount_select_cost = Server::mount_cost($mount_select);
		
		if($player->data['mount'] > $mount_select)
		{
			break;
		}
		
		if($player->data['silver'] < $mount_select_cost['silver'])
		{
			$ret = array($ERR_TOO_EXPENSIVE);
            break;
		}
		
		if($player->data['mushroom'] < $mount_select_cost['mushroom'])
		{
			$ret = array($ERR_NO_MUSH_MQ);
            break;
		}
		
		$player->data['silver']-=$mount_select_cost['silver'];
		$player->data['mushroom']-=$mount_select_cost['mushroom'];
		$player->data['mount'] = $mount_select;
		if($player->data['mount_dur'] == 0)
		{
			$player->data['mount_dur'] += $player->data['mount_dur'] + ($GLOBALS['SERVER_TIME'] + 1209600);
		}
		else
		{
			$player->data['mount_dur'] += 1209600;
		}
		
		
		$qry = $db->prepare("UPDATE `user_data` SET `silver` = :silver, `mushroom` = :mushroom, `mount` = :mount, `mount_dur` = :mount_dur WHERE `user_id` = :uid");
		$qry->bindParam(":silver", $player->data['silver'], PDO::PARAM_INT);
		$qry->bindParam(":mushroom", $player->data['mushroom'], PDO::PARAM_INT);
		$qry->bindParam(":mount", $player->data['mount'], PDO::PARAM_INT);
		$qry->bindParam(":mount_dur", $player->data['mount_dur'], PDO::PARAM_INT);
		$qry->bindParam(":uid", $player->id, PDO::PARAM_INT);
		$qry->execute();
		
		$ret = $player->get_ret();
		
		$ret[0] = $ACT_HERO . $ret[0];
		
		$ret[] .= ";" . Server::utf8_format(urldecode($player->data['user_desc'])) . ";";
		
	break;
	case $ACT_SCREEN_PILZDEALER:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$ret = array($ACT_SCREEN_PILZDEALER);
		
	break;
	case $ACT_HERO:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$ret = $player->get_ret();
		
		$ret[0] = $ACT_HERO . $ret[0];
		
		$ret[513] .= ";" . Server::utf8_format(urldecode($player->data['user_desc'])) . ";";
		
	break;
	case $ACT_CHANGE_HERO_DESC:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$desc = urlencode($DATA);
		
		$qry = $db->prepare("UPDATE `user_data` SET `user_desc` = :desc WHERE `user_id` = ".$player->id."");
		$qry->bindParam(":desc", $desc);
		$qry->execute();
		
		$ret = array($SERVER_PLAYER_DESC_SUCCESS);
		
    break;
    case $ACT_BUY_STAT:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($DATA < 1 OR $DATA > 5)
		{
			break;
		}
		
		$DATA = htmlspecialchars($DATA);
		
		$stat = "attr_".$player->get_name_attr($DATA);
		
		if($player->data[$stat] >= 15000)
		{
			break;
		}
		
		$cost = ($player->data[$stat."_buy"] + 5) * 5;
		
		if($player->data['silver'] < $cost)
		{
			$ret = array($GLOBALS['ERR_TOO_EXPENSIVE']);
			break;
		}
		
		$player->data['silver']-=$cost;
		$player->data[$stat]+=1;
		$player->data[$stat."_buy"]+=1;
		
		$db->query("UPDATE `user_data` SET `silver` = ".$player->data['silver'].", `".$stat."` = ".$player->data[$stat].", `".$stat."_buy` = ".$player->data[$stat."_buy"]." WHERE `user_id` = ".$player->id."");
		
		$player->login($SSID);
		$ret = $player->get_ret();
		$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
		
	break;
	case $ACT_ALBUM:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$ret [0] = $SERVER_ALBUM;
		$ret[0] .="////////////////////////////////////////////////////////////";
		$ret[0].= "///////4AAAAAAAAAAAAAAAAA/wz/////////////AAAAAAAAAAAAAP8M//////gAAAAAP8I//////////////////";
		$ret[0].="///////AAAAAAAAAAAAAAAAAAAAAAAAA/wD////////AAAAAAAAP8A////////wAAAAAAAD/AP";
		$ret[0].="///////8AAAAAAAA/wD////////AAAAAAAAP8A////////wAAAAAAAD/AP";
		$ret[0].= "///////8AAAAAAAA/wD////////AAAAAAAAP8A////////wAAAAAAAD/CP";
		$ret[0].= "///////8AAAAAAAA/wD////////AAAAAAAAP8I////////wAAAAAAAD/CP";
		$ret[0].= "///////8AAAAAAAA/wD////////AAAAAAAAP8A////////wAAAAAAAD/CP";
		$ret[0].= "///////8AAAAAAAA/wj////////AAAAAAAAP8A////////wAAAAAAAD/CP";
		$ret[0].="///////8AAAAAAAA/wAA=="; 
			
	break;
	case $ACT_KILL_POTION:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$id = $DATA;
		
		$db->query("UPDATE `user_data` SET `potion_id".$id."` = 0, `potion_value".$id."` = 0, `potion_time".$id."` = 0 WHERE `user_id` = ".$player->id."");
		
		$player->login($SSID);
		$ret = $player->get_ret();
		$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
		
	break;
	case $ACT_INVITE_PLAYER:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$ret = array($SERVER_INVITE_SUCCESS);
		
	break;
	case $ACT_CHANGE_PASS:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$in = explode(';', $DATA);
        
        $password = $in[1];
        $newpassword = $in[2];
        $confirmnewpassword = $in[3];
		
		if(md5($password) != $player->data['password'])
		{
			$ret = array($ERR_WRONG_PASSWORD);
            break;
		}
		
		if(strlen($newpassword) < 4)
		{
			$ret = array($ERR_PASSWORD_TOO_SHORT);
			break;
		}
		
		if($newpassword != $confirmnewpassword)
		{
			break;
		}
		
		$ret = array($SERVER_CHANGE_PASS_OK);
		
		$db->query("UPDATE `user_data` SET `password` = '".md5($newpassword)."' WHERE `user_id` = ".$player->id."");
		
    break;
	case $ACT_FORGOT_PASSWORD:
		//TODO
    break;
    case $ACT_CHANGE_MAIL:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$in = explode(';', $DATA);

        $password = $in[1];
        $oldmail = strtolower($in[2]);
        $newmail = urlencode(strtolower($in[3]));
		
		if(md5($password) != $player->data['password'])
		{
			$ret = array($ERR_WRONG_PASSWORD);
            break;
		}
		
		if($player->data['email_validate'] == 1)
		{
			if($oldmail != $player->data['email'])
			{
				$ret = array($ERR_EMAIL_WRONG);
				break;
			}
		}
		else if($oldmail != $newmail)
		{
			break;
		}
		
		$ret = array($SERVER_CHANGE_MAIL_OK);
		
		$qry = $db->prepare("UPDATE `user_data` SET `email` = :email WHERE `user_id` = ".$player->id."");
		$qry->bindParam(":email", $newmail);
		$qry->execute();
		
    break;
    case $ACT_DELETE_ACCOUNT:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$in = explode(";", $DATA);
        
        $password = $in[1];
        $email = strtolower($in[2]);
		
		if(md5($password) != $player->data['password'])
		{
			$ret = array($ERR_WRONG_PASSWORD);
            break;
		}
		
		if($email != $player->data['email'])
		{
			$ret = array($ERR_EMAIL_WRONG);
            break;
		}
		
		if($player->data['guild_id'] != 0)
		{
			if($player->data['guild_rank'] == 1)
			{
				$qry = $db->query("SELECT * FROM `user_data` WHERE `guild_id` = ".$player->data['guild_id']." ORDER BY lvl, guild_rank DESC");
				if($qry->rowCount() > 1)
				{
					$guild_player = $qry->fetchAll();
					
					$db->query("UPDATE `guilds` SET `leader_id` = ".$guild_player[0]['user_id']." WHERE `guild_id` = ".$player->data['guild_id']."");
					$db->query("UPDATE `user_data` SET `guild_rank` = 1 WHERE `user_id` = ".$guild_player[0]['user_id']."");
				}
				else
				{
					$db->query("DELETE FROM `guilds` WHERE `guild_id` = ".$player->data['guild_id']."");
				}
			}
		}
		
		$db->query("DELETE FROM `messages` WHERE `reciver_id` = ".$player->id."");
		$db->query("DELETE FROM `items_shakes` WHERE `owner_id` = ".$player->id."");
		$db->query("DELETE FROM `items_fidget` WHERE `owner_id` = ".$player->id."");
		$db->query("DELETE FROM `items` WHERE `owner_id` = ".$player->id."");
		$db->query("DELETE FROM `user_data` WHERE `user_id` = ".$player->id."");
		
		$ret = array($SERVER_DELETE_ACCOUNT_OK);
		
    break;
    case $ACT_CHANGE_FACE:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$npe = explode(";", $DATA);

		$looks = explode("/", $npe[4]);
		
		$race = $npe [2];
		
		$gender = $npe [3];
		
		if($player->data['silver'] < 100)
		{
			$ret = array($ERR_GUILD_LACK_GOLD);
			break;
		}
		
		$player->data['silver'] -= 100;
		
		$qry = $db->prepare("UPDATE `user_data` SET `face1` = :face1, `face2` = :face2, `face3` = :face3, `face4` = :face4, `face5` = :face5, `face6` = :face6, `face7` = :face7, `face8` = :face8, `face9` = :face9, `face10` = :face10, `race` = :race, `gender` = :gender, `silver` = :silver WHERE `user_id` = :uid");
		$qry->bindParam(":uid", $player->id);
		$qry->bindParam(":silver", $player->data['silver']);
		$qry->bindParam(":gender", $gender);
		$qry->bindParam(":race", $race);
		for($i=1;$i<11;$i++)
		{
			$qry->bindParam(":face".$i, $looks[$i-1]);
		}
		$qry->execute ();
		
		$player->login($SSID);
		
		$ret = $player->get_ret();
		
		$ret [0] = $SERVER_CHANGE_FACE_OK . $ret [0];
		
		$ret[] .= ";" . Server::utf8_format(urldecode($player->data['user_desc'])) . ";";

	break;
	case $ACT_MAIL:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$ret = $player->load_mail_data(true);
		
    break;
    case $ACT_MAIL_SEND:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['lvl'] < 10)
		{
			$ret = array($ERR_MSG_LEVEL_TOO_LOW);
            break;
		}
		
		if($player->data['email_validate'] == 0)
		{
			$ret = array($ERR_MSG_NOT_VALIDATED);
            break;
		}
		
		$DATA = explode(';', $DATA);
		
		$to = $DATA[0];
		$subject = urlencode($DATA[1]);
		$desc = urlencode($DATA[2]);
		
		if(strlen($subject) < 4)
		{
			$ret = array($ERR_SUBJECT_TOO_SHORT);
            break;
		}
		
		if($to == $player->data['user_name'])
		{
			$ret = array($ERR_RECIPIENT_SELF);
            break;
		}
		
		$reciver = $db->prepare("SELECT `user_id` FROM `user_data` WHERE `user_name` = :name");
		$reciver->bindParam(":name", $to, PDO::PARAM_STR);
		$reciver->execute();
		
		if($reciver->rowCount() == 0)
		{
			$ret = array($ERR_RECIPIENT_NOT_FOUND);
            break;
		}
		
		$reciver = $reciver->fetch();
		$reciver = $reciver['user_id'];
		
		$count_messages = $db->query("SELECT `msg_id` FROM `messages` WHERE `reciver_id` = ".$reciver."");
		
		if($count_messages->rowCount() >= 100)
		{
			$ret = array($ERR_INBOX_FULL);
            break;
		}
		
		$send = $db->prepare("INSERT INTO `messages` (`sender_id`, `reciver_id`, `time`, `subject`, `msg`) VALUES (".$player->id.", ".$reciver.", ".$GLOBALS['SERVER_TIME'].", :subject, :desc);");
		$send->bindParam(":subject", $subject, PDO::PARAM_STR);
		$send->bindParam(":desc", $subject, PDO::PARAM_STR);
		$send->execute();
		
		$ret = array($SERVER_MESSAGE_SENT);
		
    break;
	case $ACT_MAIL_READ:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$message = $db->query("SELECT * FROM `messages` WHERE `reciver_id` = ".$player->id." ORDER BY `time` DESC");
		
		if($message->rowCount() == 0)
		{
			break;
		}
		
		if(!isset($DATA))
		{

		}
		
		$message = $message->fetchAll();
		
		$message = $message[$DATA - 1];
		
		$sender = $db->query("SELECT `user_name` FROM `user_data` WHERE `user_id` = ".$message['sender_id']."");
		$sender = $sender->fetch();
		
		$ret[0] = "201".$sender['user_name'];
		$ret[1] = Server::utf8_format(urldecode($message['subject']));
		$ret[2] = $message['time'];
		$ret[3] = Server::utf8_format(urldecode($message['msg']));
		
		if($ret[1] == 5)
		{
			$qry = $db->query("SELECT `guild_id` FROM `guilds` WHERE `name` = '".$message['msg']."'");
			$ret[3].= ";".$qry->fetch()['guild_id'];
		}
		
		$ret = array($ret[0] . ";" . $ret[1] . ";" . $ret[2] . ";" . $ret[3]);
		
    break;
    case $ACT_MAIL_DELETE:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$DATA = addslashes($DATA);
		
		if($DATA == "-1")
		{
			$db->query("DELETE FROM `messages` WHERE `reciver_id` = ".$player->id."");
		}
		else if(intval($DATA) AND $DATA < 101)
		{
			$messages_query = $db->query("SELECT * FROM `messages` WHERE `reciver_id` = ".$player->id." ORDER BY `time` DESC");
			if($messages_query->rowCount() >= $DATA)
			{
				$messages = $messages_query->fetchAll();
				$db->query("DELETE FROM `messages` WHERE `reciver_id` = ".$player->id." AND `msg_id` = ".$messages[$DATA - 1]['msg_id']."");
			}
		}
		
		$ret = $player->load_mail_data(false);
		
    break;
	case $ACT_ENTER_GUILD:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['guild_id'] > 0)
		{
			
			$guild_attack = $db->query("SELECT * FROM `guild_attacks` WHERE `guild_id` = ".$player->data['guild_id']." OR `target_id` = ".$player->data['guild_id']."");
			if($guild_attack->rowCount() >= 1)
			{
				$guild_attack = $guild_attack->fetch();
				
				if($SERVER_TIME >= $guild_attack['attack_time'])
				{
					$attack_guild_data = $db->query("SELECT * FROM `guilds` WHERE `guild_id` = ".$guild_attack['guild_id']."");
					$attack_guild_data = $attack_guild_data->fetch();
					$attack_guild = new Guild($attack_guild_data);
					
					$defend_guild_data = $db->query("SELECT * FROM `guilds` WHERE `guild_id` = ".$guild_attack['target_id']."");
					$defend_guild_data = $defend_guild_data->fetch();
					$defend_guild = new Guild($defend_guild_data);
					
					$qry = $db->query("SELECT `user_id` FROM `user_data` WHERE `guild_id` =".$attack_guild_data['guild_id']." ORDER BY `guild_rank` ASC, `lvl` ASC");
					$attack_members = $qry->fetchAll();
					
					$qry = $db->query("SELECT `user_id` FROM `user_data` WHERE `guild_id` =".$defend_guild_data['guild_id']." ORDER BY `guild_rank` ASC, `lvl` ASC");
					$defend_members = $qry->fetchAll();
					
					$data_war = Server::guild_war($attack_guild_data, $attack_members, $defend_guild_data, $defend_members);
					$data_guild_war_reverse = $data_war[1];
					$data_guild_war = $data_war[0];
					
					$win = $data_war[2] == $attack_guild_data['guild_id'] ? 'true' : 'false';
					
					$fight_data = join("/", $data_guild_war);
					$fight_data_reverse = join("/", $data_guild_war_reverse);
					
					$honor = Server::calculateGuildHonor($attack_guild_data, $defend_guild_data, $win);
					
					$db->query("INSERT INTO `guild_attacks_archive`( `guild_id`, `target_id`, `fight_data`, `fight_data_reverse`, `success`, `honor`) VALUES (".$attack_guild_data['guild_id'].", ".$defend_guild_data['guild_id'].", '".$fight_data."', '".$fight_data_reverse."', '".$win."', ".$honor.")");
					
					$db->query("UPDATE `user_data` SET `guild_attack` = 0 WHERE `guild_id` = ".$attack_guild_data['guild_id']."");
					$db->query("UPDATE `user_data` SET `guild_defend` = 0 WHERE `guild_id` = ".$defend_guild_data['guild_id']."");
					
					
					if($win)
					{
						$success = 'yes';
						$msg1 = '#a+#' . $defend_guild_data['name'] . '#' . $honor;
						$msg2 = '#d-#' . $attack_guild_data['name'] . '#' . $honor;
						$db->query("UPDATE `guilds` SET `honor` = `honor` + ".$honor." WHERE `guild_id` = ".$attack_guild_data['guild_id']."");
						$defend_guild_data['honor'] -=$honor;
						if($defend_guild_data['honor'] < 0)
						{
							$defend_guild_data['honor'] = 0;
						}
						$db->query("UPDATE `guilds` SET `honor` = ".$defend_guild_data['honor']." WHERE `guild_id` = ".$defend_guild_data['guild_id']."");
					}
					else
					{
						$success = 'no';
						$msg1 = '#a-#' . $defend_guild_data['name'] . '#' . $honor;
						$msg2 = '#d+#' . $attack_guild_data['name'] . '#' . $honor;
						$db->query("UPDATE `guilds` SET `honor` = `honor` + ".$honor." WHERE `guild_id` = ".$defend_guild_data['guild_id']."");
						
						$attack_guild_data['honor'] -=$honor;
						if($attack_guild_data['honor'] < 0)
						{
							$attack_guild_data['honor'] = 0;
						}
						$db->query("UPDATE `guilds` SET `honor` = ".$attack_guild_data['honor']." WHERE `guild_id` = ".$attack_guild_data['guild_id']."");
					}
					
					$db->query("INSERT INTO `guild_chat`(`guild_id`, `sender_id`, `reciver_id`, `time`, `type`, `msg`) VALUES (".$attack_guild_data['guild_id'].", 1, 0, 0, 1, '".$msg1."')");
					$db->query("INSERT INTO `guild_chat`(`guild_id`, `sender_id`, `reciver_id`, `time`, `type`, `msg`) VALUES (".$defend_guild_data['guild_id'].", 1, 0, 0, 1, '".$msg2."')");
					
					$db->query("DELETE FROM `guild_attacks` WHERE `attack_id` = ".$guild_attack['attack_id']."");
				}
			}
			
			$guild_attack_archive = $db->query("SELECT * FROM `guild_attacks_archive` WHERE `guild_id` = ".$player->data['guild_id']." OR `target_id` = ".$player->data['guild_id']." ORDER BY `attack_id` DESC LIMIT 1");
			
			if($guild_attack_archive->rowCount() > 0)
			{
				$guild_attack_archive = $guild_attack_archive->fetch();
				
				if($player->data['gattack_last'] != $guild_attack_archive['attack_id'])
				{
					
					if($player->data['guild_id'] == $guild_attack_archive['guild_id'])
					{
						$data_fight = $guild_attack_archive['fight_data'];
						if($guild_attack_archive['success'] == true)
						{
							$exp = $player->get_exp();
							$player->data['exp'] +=$exp;
						}
					}
					else
					{
						$data_fight = $guild_attack_archive['fight_data'];
						if($guild_attack_archive['success'] == true)
						{
							$exp = $player->get_exp();
							$player->data['exp'] +=$exp;
						}
					}
					
					$db->query("UPDATE `user_data` SET `gattack_last` = ".$guild_attack_archive['attack_id'].", `exp` = ".$player->data['exp']." WHERE `user_id` = ".$player->id."");
					
					$ret =  explode("/", $data_fight);
					$ret[0] = $SERVER_GUILD_FIGHT . $ret[0];
					$ret[count($ret) - 1] .= $exp . ";" . $guild_attack_archive['honor'];
					break;
				}
			}
		
			$guild = $db->query("SELECT * FROM `guilds` WHERE `guild_id` = ".$player->data['guild_id']."");
			if($guild->rowCount() == 1)
			{
				$g_data = $guild->fetch();
				
				$Guild = new Guild($g_data);
				$ret = $Guild->get_ret(true);
			}
		}
		else
		{
			$ret = array($ACT_SCREEN_GILDE_GRUENDEN);
            break;
		}
			
	break;
	case $ACT_CREATE_GUILD:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['guild_id'] != 0)
		{
			break;
		}
		
		$data = explode(";", $DATA);
		$guild_name = $data[1];
		
		if(strlen($guild_name) < 4)
		{
			$ret = array($ERR_GUILD_NAME_LENGTH);
			break;
		}
		
		$filter = array("`", "~", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "=", "[", "]", "{", "}", ";", ":", "'", '"', ",", ".", "/", "?", "<", ">", "|");
		
		for($i=0;$i<Count($filter);$i++)
		{
			if(stristr($guild_name, $filter[$i]))
			{
				$ret = array($ERR_GUILD_NAME_CHARACTERS);
				break;
			}
		}
		
		if($player->data['silver'] < 1000)
		{
			$ret = array($ERR_GUILD_LACK_GOLD);
			break;
		}
		
		$check_guild_name = $db->prepare("SELECT * FROM `guilds` WHERE `name` = :gname");
		$check_guild_name->bindParam(":gname", $guild_name, PDO::PARAM_STR);
		$check_guild_name->execute();
		
		if($check_guild_name->rowCount() > 0)
		{
			$ret = array($ERR_GUILD_NAME_REJECTED);
			break;
		}
		
		$arms_create = Guild::generate_arms(1, 14)."". Guild::generate_arms(1, 21)."". Guild::generate_arms(1, 16). Guild::generate_arms(1, 17). Guild::generate_arms(1, 43)."000". rand(1,9) ."0". rand(1,9) ."0". rand(1,9) ."Â";
    
        $qry = $db->prepare("INSERT INTO guilds(name, leader_id, arms) 
				VALUES(:name, :lid, :arms)");
        $qry->bindParam(':name', $guild_name);
        $qry->bindParam(':lid', $player->id);
		$qry->bindParam(':arms', $arms_create);
        $qry->execute();
		
		$player->data['silver'] -=1000;
		
		$qry = $db->prepare("UPDATE `user_data` SET `silver` = ".$player->data['silver'].", `guild_rank` = 1, `guild_join_time` = ".$GLOBALS['SERVER_TIME'].", `guild_id` = (SELECT `guild_id` FROM `guilds` WHERE `name` = :gname) WHERE `user_id` = ".$player->id."");
		$qry->bindParam(":gname", $guild_name, PDO::PARAM_STR);
		$qry->execute();
		
		$ret = array($SERVER_GUILD_FOUND_SUCCESS);
		
    break;
	case $ACT_GUILD_SET_DESC:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['guild_rank'] != 1)
		{
			break;
		}
		
		
		$ndata = explode(';', $DATA);
        $desc = urldecode('%A7');
        $desc = explode($desc, $ndata[2]);
		
		if(strlen($desc[1]) > 999)
		{
			$ret = array($ERR_GUILD_DESCR_TOO_LONG);
			break;
		}
		
		$desc[1] = Server::utf8_format(urlencode($desc[1]));
		
		$qry = $db->prepare("UPDATE `guilds` SET `arms` = :arms, `description` = :desc WHERE `guild_id` = ".$player->data['guild_id']."");
		$qry->bindParam(":arms", $desc[0]);
		$qry->bindParam(":desc", $desc[1]);
		$qry->execute();
		
		$ret[0] = $SERVER_GUILD_CHANGE_DESC_SUCCESS. $ret[0];
		
	break;
	case $ACT_GUILD_COMMENCE_ATTACK:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['guild_id'] == 0)
		{
			break;
		}
		
		if($player->data['guild_rank'] > 2)
		{
			break;
		}
		
		if($player->data['guild_join_time'] > $SERVER_TIME)
		{
			break;
		}
		
		$guild_attack = $db->query("SELECT * FROM `guild_attacks` WHERE `guild_id` = ".$player->data['guild_id']."");
		if($guild_attack->rowCount() == 1)
		{
			$ret = array($ERR_GUILD_ATTACK_STATUS);
			break;
		}
		
		if($DATA == "-1")
		{
			break;
		}
		
		$g_data = $player->get_guild();
		
		$guild = new Guild($g_data);
		
		if($g_data['silver'] < $guild->get_guild_war_cost())
		{
			$ret = array($ERR_GUILD_TOO_EXPENSIVE);
			break;
		}
		
		$guild_select_name = addslashes($DATA);
		
		$qry = $db->prepare("SELECT * FROM `guilds` WHERE `name` = :name");
		$qry->bindParam(":name", $guild_select_name, PDO::PARAM_STR);
		$qry->execute();
		if($qry->rowCount() == 0)
		{
			break;
		}
		
		$g_select = $qry->fetch();
		
		$guild_select = new Guild($g_select);
		
		$attack_time = $SERVER_TIME + 36000;
		
		$g_data['silver']-=$guild_select->get_guild_war_cost();
		
		$db->query("INSERT INTO `guild_attacks`(`attack_id`, `guild_id`, `target_id`, `initiater_id`, `attack_time`, `fight`) VALUES (NULL, ".$g_data['guild_id'].", ".$g_select['guild_id'].", ".$player->id.", ".$attack_time.", 0)");
		$db->query("UPDATE `guilds` SET `silver` = ".$g_data['silver']." WHERE `guild_id` = ".$g_data['guild_id']."");
		$db->query("UPDATE `user_data` SET `guild_attack` = 1 WHERE `user_id` = ".$player->id."");
		
		$ret = $player->get_ret();
		
		$ret[0] = $SERVER_GUILD_COMMENCE_ATTACK_OK . $ret[0];
		
	break;
	case $ACT_GUILD_INVITE:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		
		if($player->data['guild_rank'] > 2)
		{
			exit("Error: Player not found !");
		}
		
		$guild = $player->get_guild();
		
		if($guild)
		{
			$in = explode(';', $DATA);
			$qry = $db->query("SELECT COUNT(*) FROM `user_data` WHERE `guild_id` =".$player->data['guild_id']."");
			if($qry->rowCount() >= $guild['fortress'])
			{
				exit("Error: Guild is full !");
			}
			
			$Player_invite = new Player;
			
			if($Player_invite->login_username($in[2]))
			{
				$ret = array($ERR_GUILD_PLAYER_NOT_FOUND);
				break;
			}
			$qry = $db->query("SELECT * FROM `guild_invites` WHERE `guild_id` = ".$guild['guild_id']." AND `user_id` = ".$Player_invite->data['user_id']."");
			if($qry->rowCount() == 0)
			{
				$db->query("INSERT INTO guild_invites(guild_id, user_id) VALUES(".$guild['guild_id'].", ".$Player_invite->data['user_id'].")");
				$db->query("INSERT INTO messages(sender_id, reciver_id, time, subject, msg) VALUES(".$player->id.", ".$Player_invite->data['user_id'].", ".$SERVER_TIME.", 5, '".$guild['name']."')");
			}
		}
		
		$ret = array($SERVER_GUILD_INVITE_SUCCESS);
		
    break;
	case $ACT_GUILD_SET_OFFICER:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$data = explode(";", $DATA);
		
		if($player->data['guild_rank'] == 1)
		{
			if($player->data['user_name'] == addslashes($data[2]))
			{
				exit("Error: Leader !");
			}
			
			$qry = $db->prepare("SELECT * FROM `user_data` WHERE `user_name` = :uname AND `guild_id` = :gid LIMIT 1");
			$qry->bindParam(":uname", $data[2], PDO::PARAM_STR);
			$qry->bindParam(":gid", $player->data['guild_id']);
			$qry->execute();
			
			if($qry->rowCount() == 1)
			{
				$setplayer_data = $qry->fetch();
				
				if($setplayer_data['guild_id'] == $player->data['guild_id'])
				{
					$rank = $setplayer_data['guild_rank'] == 3 ? 2 : 3;
					$db->query("UPDATE `user_data` SET `guild_rank` = ".$rank." WHERE `user_id` = ".$setplayer_data['user_id']."");
					
					$ret = array($SERVER_GUILD_OFFICER_SUCCESS);
				}
				else 
				{
					break;
				}
			}
			else
			{
				exit("Error: Player not found !");
			}
		}
		else
		{
			exit("Error: No permissions !");
		}
		
    break;
    case $ACT_GUILD_SET_MASTER:
	
	$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$data = explode(";", $DATA);
		
		if($player->data['guild_id'] == 0)
		{
			exit("Error: Player has not guild !");
		}
		
		if($player->data['guild_rank'] != 1)
		{
			exit("Error: Player dont have rank leader !");
		}
		
		$qry = $db->prepare("SELECT * FROM `user_data` WHERE `user_name` = :uname AND `guild_id` = :gid LIMIT 1");
		$qry->bindParam(":uname", $data[2], PDO::PARAM_STR);
		$qry->bindParam(":gid", $player->data['guild_id']);
		$qry->execute();
	
		if($qry->rowCount() == 1)
		{
			$newleader = $qry->fetch();
			
			$db->query("UPDATE `user_data` SET `guild_rank` = 1 WHERE `user_id` = ".$newleader['user_id']."");
			$db->query("UPDATE `user_data` SET `guild_rank` = 3 WHERE `user_id` = ".$player->id."");
			$db->query("UPDATE `guilds` SET `leader_id` = ".$newleader['user_id']." WHERE `guild_id` = ".$newleader['guild_id']."");
		}
		
		$ret = array($SERVER_GUILD_MASTER_SUCCESS);
		
    break;
    case $ACT_GUILD_EXPEL:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$in = explode(';', $DATA);
		
		if(!isset($in[2]))
		{
			exit("Error: Parametrs not found !");
		}
		
		$Player_kick = new Player;
		if($Player_kick->login_username($in[2]))
		{
			exit("Error: Player not found !");
		}
		
		$kick_player = $Player_kick->data;
		
		if($player->data['guild_rank'] == 1)
		{
			if($kick_player['user_id'] == $player->id)
			{
				$qry = $db->query("SELECT * FROM `user_data` WHERE `guild_id` = ".$player->data['guild_id']." ORDER BY lvl, guild_rank DESC");
				if($qry->rowCount() > 1)
				{
					$guild_player = $qry->fetchAll();
					
					$db->query("UPDATE `guilds` SET `leader_id` = ".$guild_player[0]['user_id']." WHERE `guild_id` = ".$player->data['guild_id']."");
					$db->query("UPDATE `user_data` SET `guild_rank` = 1 WHERE `user_id` = ".$guild_player[0]['user_id']."");
					$db->query("UPDATE `user_data` SET `guild_id` = 0, `guild_rank` = 0, `guild_mushroom` = 0, `guild_silver` = 0,  `guild_join_time` = 0, `guild_defend` = 0, `guild_attack` = 0, `gchat_last` = 0, `gattack_last` = 0 WHERE `user_id` = ".$kick_player['user_id']."");
				}
				else
				{
					$db->query("DELETE FROM `guilds` WHERE `guild_id` = ".$player->data['guild_id']."");
					$db->query("UPDATE `user_data` SET `guild_id` = 0, `guild_rank` = 0, `guild_mushroom` = 0, `guild_silver` = 0,  `guild_join_time` = 0, `guild_defend` = 0, `guild_attack` = 0, `gchat_last` = 0, `gattack_last` = 0 WHERE `user_id` = ".$kick_player['user_id']."");
				}
			}
			else
			{
				if($kick_player['guild_id'] != $player->data['guild_id'] OR $kick_player['guild_id'] == 0)
				{
					$db->query("DELETE FROM `guild_invites` WHERE `user_id` = ".$kick_player['user_id']." AND `guild_id` =".$player->data['guild_id']."");
				}
				else
				{
					$db->query("UPDATE `user_data` SET `guild_id` = 0, `guild_rank` = 0, `guild_mushroom` = 0, `guild_silver` = 0,  `guild_join_time` = 0, `guild_defend` = 0, `guild_attack` = 0, `gchat_last` = 0, `gattack_last` = 0 WHERE `user_id` = ".$kick_player['user_id']."");
				}
			}
		}
		else if($player->data['guild_rank'] == 2)
		{
			if($kick_player['guild_rank'] > 2)
			{
				if($kick_player['guild_id'] != $player->data['guild_id'] OR $kick_player['guild_id'] == 0)
				{
					$db->query("DELETE FROM `guild_invites` WHERE `user_id` = ".$kick_player['user_id']." AND `guild_id` =".$player->data['guild_id']."");
				}
				else
				{
					$db->query("UPDATE `user_data` SET `guild_id` = 0, `guild_rank` = 0, `guild_mushroom` = 0, `guild_silver` = 0,  `guild_join_time` = 0, `guild_defend` = 0, `guild_attack` = 0, `gchat_last` = 0, `gattack_last` = 0 WHERE `user_id` = ".$kick_player['user_id']."");
				}
			}
			else
			{
				exit("Error: No permissions !");
			}
		}
		else if($player->data['guild_rank'] == 3)
		{
			if($kick_player['user_id'] == $player->id)
			{
				$db->query("UPDATE `user_data` SET `guild_id` = 0, `guild_rank` = 0, `guild_mushroom` = 0, `guild_silver` = 0,  `guild_join_time` = 0, `guild_defend` = 0, `guild_attack` = 0, `gchat_last` = 0, `gattack_last` = 0 WHERE `user_id` = ".$kick_player['user_id']."");
			}
			else
			{
				exit("Error: No permissions !");
			}
		}
		else
		{
			exit("Error: No permissions !");
		}
		
		$ret = array($SERVER_GUILD_EXPEL_SUCCESS);
		
    break;
    case $ACT_GUILD_JOIN:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$in = explode(';', $DATA);
		//$in[1] = guild_id
		
		$qry = $db->prepare("SELECT * FROM `guild_invites` WHERE `guild_id` = :gid AND `user_id` = ".$player->id."");
		$qry->bindParam(":gid", $in[1], PDO::PARAM_INT);
		$qry->execute();
		
		if($qry->rowCount() != 1)
		{
			exit("Error: No invite !");
		}

		if($player->data['guild_id'] != 0)
		{
			exit("Error: Player has guild !");
		}
		
		$invite = $qry->fetch();
		
		$qry = $db->prepare("SELECT * FROM `guilds` WHERE `guild_id` = :gid LIMIT 1");
		$qry->bindParam(":gid", $in[1], PDO::PARAM_INT);
		$qry->execute();
		
		if($qry->rowCount() != 1)
		{
			exit("Error: Guild not found !");
		}
		
		$guild = $qry->fetch();
		
		$qry = $db->query("SELECT COUNT(*) FROM `user_data` WHERE `guild_id` = ".$guild['guild_id']."");
		
		if($qry->rowCount() >= $guild['fortress'])
		{
			exit("Error: Guild is full !");
		}
		
		$db->query("UPDATE `user_data` SET `guild_id` = ".$guild['guild_id'].", `guild_rank` = 3, `guild_join_time` =".($SERVER_TIME + 86400)." WHERE `user_id` = ".$player->id."");
		
		$db->query("DELETE FROM `guild_invites` WHERE `invite_id` = ".$invite['invite_id']."");
		
		$ret = $player->get_ret();
		
		$ret[0] = $SERVER_GUILD_JOIN_SUCCESS . $ret[0];
		
	break;
    case $ACT_GUILD_DONATE:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['email_validate'] != 1)
		{
			$ret = array($ERR_GUILD_EMAIL_VALIDATE);
			break;
		}
		
		$in = explode(';', $DATA);
		
		$type =  round(addslashes($in[0]));
		$count = round(addslashes($in[1]));
		
		if($count < 0)
		{
			break;
		}

		if(!intval($type))
		{
			break;
		}
		
		if($type < 0)
		{
			$ret = array($ERR_GUILD_DONATE_NEG);
		}
		
		$donation = array(
            'silver' => 0,
            'mushroom' => 0
        );
		
		if ($type == 1) 
		{
			$donation['silver'] = $count;
			if($player->data['silver'] < $donation['silver'])
			{
				$ret = array($ERR_TOO_EXPENSIVE);
				break;
			}
			
			$donation_name = "złota";
			$donate_amount = $donation['silver'];
			$player->data['silver'] -=$donation['silver'];
			$player->data['guild_silver'] +=$donation['silver'];
		}
		else if($type == 2)
		{
			$donation['mushroom'] = $count;
			if($player->data['mushroom'] < $donation['mushroom'])
			{
				$ret = array($ERR_GUILD_LACK_MUSH);
				break;
			}
			else if($player->data['mushroom_buy'] < $donation['mushroom'])
			{
				$ret = array($ERR_GUILD_MUSH_FREE);
				break;
			}
			
			$donation_name = "grzybów";
			$donate_amount = $donation['mushroom'];
			$player->data['mushroom'] -=$donation['mushroom'];
			$player->data['guild_mushroom'] +=$donation['mushroom'];
		}
		else
		{
			break;
		}
		
		$timeR = Date("H:i", Time());
		$msg = "  wpłacił(a) ". ($in[1] / 100) . " " . $donation_name;
		
		Guild::send_chat_other($player->data['guild_id'], $player->id, 0, $timeR, $msg, 0);
		$db->query("UPDATE `guilds` SET `mushroom` = mushroom + ".$donation['mushroom'].", `silver` = silver + ".$donation['silver']." WHERE `guild_id` = ".$player->data['guild_id']."");
		$db->query("UPDATE `user_data` SET `silver` =".$player->data['silver'].", `mushroom` = ".$player->data['mushroom'].", `guild_mushroom` = ".$player->data['guild_mushroom'].", `guild_silver` = ".$player->data['guild_silver']." WHERE `user_id` = ".$player->id."");
		
		$ret = $player->get_ret();
		$ret[0] = $SERVER_GUILD_DONATE_SUCCESS . $ret[0];
		
    break;
	case $ACT_GUILD_JOIN_ATTACK:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['guild_id'] == 0)
		{
			break;
		}
		
		$qry = $db->query("SELECT * FROM `guild_attacks` WHERE `guild_id` = ".$player->data['guild_id']."");
		if($qry->rowCount() == 0)
		{
			break;
		}
		
		if($player->data['guild_join_time'] > $SERVER_TIME)
		{
			break;
		}
		
		$db->query("UPDATE `user_data` SET `guild_attack` = 1 WHERE `user_id` = ".$player->id."");
		
		$player->login($SSID);
		
		$ret = $player->get_ret();
		
		$ret[0] = '+' . $SERVER_GUILD_JOIN_ATTACK_OK . $ret[0];
		
	break;
    case $ACT_GUILD_JOIN_DEFENSE:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['guild_id'] == 0)
		{
			break;
		}
		
		$qry = $db->query("SELECT * FROM `guild_attacks` WHERE `target_id` = ".$player->data['guild_id']."");
		if($qry->rowCount() == 0)
		{
			break;
		}
		
		if($player->data['guild_join_time'] > $SERVER_TIME)
		{
			break;
		}
		
		$db->query("UPDATE `user_data` SET `guild_defend` = 1 WHERE `user_id` = ".$player->id."");
		
		$player->login($SSID);
		
		$ret = $player->get_ret();
		
		$ret[0] = '+' . $SERVER_GUILD_JOIN_DEFENSE_OK . $ret[0];
		
    break;
	case $ACT_LOAD_CATAPULT:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		
	break;
	case $ACT_SEND_CHAT:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['guild_id'] == 0)
		{
			exit("Error: No guild !");
		}
		
		$data = explode(";", $DATA);
		
		$Guild = new Guild($player->get_guild());
		$Guild->send_chat($player, $data);
		
		$ret = $Guild->get_chat_history($player);
		
    break;
	case $ACT_GET_CHAT_HISTORY:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['guild_id'] == 0)
		{
			exit("Error: No guild !");
		}
		
		$in = intval($DATA);
		
		if($in != "0")
		{
			$Guild = new Guild($player->get_guild());
		
			$ret = $Guild->get_chat_history($player);
		}
		else
		{
			$qry = $db->query("SELECT * FROM `guild_attacks` WHERE `guild_id` = ".$player->data['guild_id']." OR `target_id` = ".$player->data['guild_id']."");
			if($qry->rowCount() >= 1)
			{
				if($qry->fetch()['guild_id'] == $player->data['guild_id'] AND $player->data['guild_attack'] == 0 OR $qry->fetch()['target_id'] == $player->data['guild_id'] AND $player->data['guild_defend'] == 0)
				{
					$ret = array("+" . $SERVER_REQUEST_GUILD);
					break;
				}
			}
			
			$qry = $db->query("SELECT * FROM `guild_attacks_archive` WHERE `guild_id` = ".$player->data['guild_id']." OR `target_id` = ".$player->data['guild_id']." ORDER BY `attack_id` ASC");
			if($qry->rowCount() >= 1)
			{
				if($player->data["gattack_last"] != $qry->fetch()["attack_id"])
				{
					$ret = array("+" . $SERVER_REQUEST_GUILD);
					break;
				}
			}
		}
		
    break;
	case $ACT_POST_SEND_GUILD:
    break;
	case $ACT_REWATCH_BATTLE:
	break;
    case $ACT_GUILD_INVITE_PLAYER:
		//WTF ?$?$?$?
    break;
    case $ACT_GUILD_IMPROVE:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['guild_id'] == 0)
		{
			break;
		}
		
		if($player->data['guild_rank'] > 2)
		{
			break;
		}
		
		$g_data = $player->get_guild();
		
		$guild = new Guild($g_data);
		
		$keys = array("fortress", "treasure", "instructor");
		
		$data = explode(";", $DATA);
		
		$key = $keys[$data[3] - 1];
		
		if($g_data[$key] >= 50)
		{
			break;
		}
		
		$cost = $guild->get_guild_build_cost($g_data[$key]);
		
		if($cost['mushroom'] > $g_data['mushroom'])
		{
			$ret = array($ERR_GUILD_LACK_MUSH);
			break;
		}
		
		if($cost['silver'] > $g_data['silver'])
		{
			$ret = array($ERR_GUILD_LACK_GOLD);
			break;
		}
		
		$msg = "";
		
		switch($key)
		{
			case "fortress":
				$msg = "#1";
			break;
			case "treasure":
				$msg = "#2";
			break;
			case "instructor":
				$msg = "#3";
			break;
		}
		
		$g_data[$key]++;
		$g_data['silver'] -=$cost['silver'];
		$g_data['mushroom'] -=$cost['mushroom'];
		
		$time = "#bd#" . $SERVER_TIME_HOURS;
		
		$guild->send_chat_other($player->data['guild_id'], $player->id, 0, $time, $msg, 0);
		$db->query("UPDATE `guilds` SET `".$key."` = ".$g_data[$key].", `mushroom` = ".$g_data['mushroom'].", `silver` = ".$g_data['silver']." WHERE `guild_id` = ".$player->data['guild_id']."");
		
		$ret = array($SERVER_GUILD_IMPROVE_SUCCESS);
		
	break;
	case $ACT_REQUEST_GUILD_NAMES:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$in = explode(';', $DATA);
		
		$attackGuild = '';
        $defendGuild = '';
        $initiaterName = '';
		
		if($in[0] != "")
		{
			//in[0] = guild_id
			
			if($in[2] == "0" OR $in[2] == "1")
			{
				$qry = $db->prepare("SELECT `name` FROM `guilds` WHERE `guild_id` = :gid");
				$qry->bindParam(":gid", $in[0], PDO::PARAM_INT);
				$qry->execute();
				if($qry->rowCount() == 1)
				{
					$attackGuild = $qry->fetch()['name'];
				}
				
				if($in[2] == "0")
				{
					$qry = $db->prepare("SELECT `user_data`.`user_name` FROM `user_data` 
					WHERE `user_data`.`user_id` = (SELECT `guild_attacks`.`initiater_id` FROM `guild_attacks`
					WHERE `guild_attacks`.`target_id` = :gid)");
					$qry->bindParam(":gid", $in[0], PDO::PARAM_INT);
					$qry->execute();
					if($qry->rowCount() == 1)
					{
						$initiaterName = $qry->fetch()['user_name'];
					}
				}
			}
		}
		
		if($in[1] != "")
		{
			$qry = $db->prepare("SELECT `name` FROM `guilds` WHERE `guild_id` = :gid");
			$qry->bindParam(":gid", $in[1], PDO::PARAM_INT);
			$qry->execute();
			if($qry->rowCount() == 1)
			{
				$defendGuild = $qry->fetch()['name'];
			}
		}
		
		$ret = array ($SERVER_GUILD_NAMES . $attackGuild . ';' . $defendGuild . ';' . $initiaterName);
		
    break;
	case $ACT_GUILD_RANKING: 
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$input = explode(';', $DATA);
		
        $guildname = $input[0];
        
        $guildid = (int) $input[1];
        
        $pos = ( int ) $input[2];
        
        if ($guildname != "")
		{
            $qry = $db->prepare("SELECT pos FROM ( SELECT honor, dung, guild_id, name, @r:=@r+1 as pos FROM guilds, (select @r:=0) r0
			ORDER BY honor DESC, dung DESC, guild_id DESC)  user_data_ordered WHERE name = :name");
            $qry->bindParam(':name', $guildname);
            $qry->execute();
            $pos = $qry->fetchAll();
            $pos = $pos[0][0];
        }
        else if($guildid != 0)
		{
            $qry = $db->prepare("SELECT pos FROM ( SELECT honor, dung, guild_id, name, @r:=@r+1 as pos FROM guilds, (select @r:=0) r0 
			ORDER BY honor DESC, dung DESC, guild_id DESC)  user_data_ordered WHERE guild_id = :gid");
            $qry->bindParam(':gid', $guildid);
            $qry->execute();
            
            $pos = $qry->fetchAll();
            $pos = $pos[0][0];
        }
        else if ($pos == '' || $pos == 0)
		{
            $pos = 1;
        }

        if ($pos < 8)
		{
            $pos = 8;
        }
        
        $qry = $db->prepare("SELECT Count(*) FROM guilds");
        $qry->execute();
        $guildsCount = $qry->fetchAll();
        $guildsCount = $guildsCount[0][0];
        
        if ($pos >= $guildsCount && $guildsCount > 8)
		{
            $pos = $guildsCount-7;
        }
        
        if ($pos > $guildsCount && $guildsCount < 8)
		{
            $pos = 8;
        }
        
        $posFrom = $pos - 8;
        
        $qry = $db->prepare("SELECT name, honor, guild_id, 
				(SELECT user_data.user_name FROM user_data WHERE user_data.user_id = leader_id) AS leader, 
				(SELECT Count(user_data.guild_id) FROM user_data WHERE user_data.guild_id = guilds.guild_id) AS members,
				(SELECT Count(*) FROM guild_attacks WHERE guild_attacks.target_id = guilds.guild_id) AS warstatus
				FROM guilds 
				ORDER BY honor DESC, dung DESC, guild_id DESC LIMIT :pos, 15");
        $qry->bindParam(':pos', $posFrom, PDO::PARAM_INT);
        $qry->execute();
        $res = $qry->fetchAll();
        
        $ret = array("170");
        
        $pos -= 7;
		$war = "";
		
        for ($i = 0; $i < count($res); $i++)
		{
            
            $a = $i * 5;
            $ret[$a] = urldecode($pos);
            $ret[$a + 1] = $res[$i]['leader'];
            $ret[$a + 2] = $res[$i]['name'];

            if ((int) $res[$i]['warstatus'] > 0)
			{
                $war = '-';
            }
            
            $ret[$a + 3] = $war . $res[$i]['members'];
            $ret[$a + 4] = $res[$i]['honor'];

            $pos++;
        }
        
        $ret[0] = "170" . $ret[0];
		
	break;
    case $ACT_RANKING:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$res;
        $pos;
        $posFrom;
        
        
        $in = explode(';', $DATA);
        
        if (ctype_digit($in[1]))
		{
            $pos = ( int ) str_replace(';', '', $DATA);
        }
		else
		{
            $nick = $in[0];
            
            $qry = $db->prepare("SELECT pos FROM (SELECT honor, lvl, user_name, user_id, @r:=@r+1 as pos FROM user_data, (select @r:=0) r0
			ORDER BY honor DESC, lvl DESC, user_id DESC) user_data_ordered WHERE user_name = :name");
            $qry->bindParam(':name', $nick);
            $qry->execute();
            $pos = $qry->fetchAll();
            $pos = $pos[0][0];
        }
        
        if ($pos < 8)
		{
            $pos = 8;
        }

        $playerCount = Server::players_count();
        
        if ($pos > $playerCount && $playerCount > 8)
		{
            $pos = $playerCount;
        }
        
        if ($pos > $playerCount && $playerCount < 8)
		{
            $pos = 8;
        }
        
        $posFrom = $pos - 8;
        
        $qry = $db->prepare("SELECT *, (SELECT guilds.name FROM guilds WHERE guilds.guild_id = user_data.guild_id) AS guild
		FROM user_data ORDER BY honor DESC, lvl DESC, user_id DESC LIMIT :pos1, 15");
        $qry->bindParam(':pos1', $posFrom, PDO::PARAM_INT);
        $qry->execute();
        $res = $qry->fetchAll();
        
		
        $ret = array( $ACT_RANKING );
        
        $pos -= 7;
        $index = 0;
        
        for ($i = 0; $i < count($res); $i++)
		{
            $data = $res[$i];
            $ret[$index] = urldecode($pos);
            $ret[$index + 1] = $res[$i]['user_name'];
            $ret[$index + 2] = $res[$i]['guild']; 
            $ret[$index + 3] = $res[$i]['lvl'];
            $ret[$index + 4] = $res[$i]['honor'];
            $class = ( int ) $res[$i]['class'];

            if ($class == 2)
			{
                $ret[$index + 3] = "-" . $ret[$index + 3];
            }
			else if ($class == 3)
			{
                $ret[$index] = "-" . $ret[$index];
            }
            
            $pos++;
            $index += 5;
        }
        
        $ret[0] = $ACT_RANKING . $ret[0];
        $ret[] = ";";
		
	break;
	case $ACT_VIEW_CHAR:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$char = new Player;
		
		if($char->login_username($DATA))
		{
			break;
		}
		
		$ret = $char->get_ret();
		$ret [0] = "1110000000000";
		
		if($char->get_guild())
		{
			$gname = $char->get_guild()['name'];
		}
		else
		{
			$gname = "";
		}
		
		$ret[511] = ";" . Server::utf8_format(urldecode($char->data['user_desc'])) . ";" . $gname . ";";
		
    break;
	case $ACT_VIEW_GUILD:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$gname = $DATA;
		
		$qry = $db->prepare("SELECT * FROM `guilds` WHERE `name` = :name");
		$qry->bindParam(":name", $gname, PDO::PARAM_STR);
		$qry->execute();
		
		if($qry->rowCount() == 1)
		{
			$Guild = new Guild($qry->fetch());
			$ret = $Guild->get_ret(false);
		}
		else
		{
			exit("Error: Guild not found !");
		}
		
    break;
    case $ACT_SCREEN_TOWER:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$ret = $player->get_ret();
		
		$ret[0] = $SERVER_TOWER_SAVE . $ret[0];
		
		if($player->data['tower_level'] >= 150)
		{
			$ret = array($ERR_ATTACK_AGAIN);
			break;
		}
		
		$tower = new Tower($player);
		$ret[] = $tower->get_ret();
		
	break;
    case $ACT_MOVE_COPYCAT_ITEM: 
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$data = explode(';', $DATA);
		
		if(!intval($data[0]) OR !intval($data[1]) OR !intval($data[2]) OR !intval($data[3]))
		{
			break;
		}
		
		$owner_item = $data[0];
		//2 - player
		//10 (1-woj 2-mag 3-low) - helper
		$newoner_item = $data[2];
		$slot = $data[1];
		$nslot = $data[3];
		
		if($slot < 1 OR $slot > 9)
		{
			break;
		}
		if($nslot < "-1" OR $nslot > 9)
		{
			break;
		}
		
		if($newoner_item == 101 OR $owner_item == 101)
		{
			$helper = new Helper($player, 1);
		}
		else if($newoner_item == 102 OR $owner_item == 102)
		{
			$helper = new Helper($player, 2);
		}
		else if($newoner_item == 103 OR $owner_item == 103)
		{
			$helper = new Helper($player, 3);
		}
		
		$item = new Item;
		
		switch($owner_item)
		{
			case 2:
			
				$slot +=10;
				if($item->get_item($player->id, $slot, "items"))
				{
					$item_class = round($item->item['item_id'] / 1000) + 1;
					
					switch($newoner_item)
					{
						case 2:
						$nslot +=10;
							if($player->check_slot($nslot))
							{
								$item->item['slot'] = $nslot;
								$item->update_item($item->item, "items");
							}
						break;
						case 101:
						case 102:
						case 103:
							if($item_class != $helper->class OR $item->item['item_type'] > 10 OR $item->item['item_type'] < 1)
							{
								break;
							}
							if($helper->check_slot($item->get_slot_item($item->item['item_type'])))
							{
								$item->item['slot'] = $item->get_slot_item($item->item['item_type']);
								$helper->insert_item($item->item);
								$item->delete_item($item->item, "items");
							}
						break;
					}
				}
			break;
			case 101:
			case 102:
			case 103:
			
				$nslot +=10;
				if($helper->get_item($slot))
				{
					if($player->check_slot($nslot))
					{
						$helper->item['slot'] = $nslot;
						$helper->item['owner_id'] = $player->id;
						$item->insert_item($helper->item, "items");
						$helper->delete_item($helper->item, "tower_helper_items");
					}
				}
			break;
		}
		
		$ret = $player->get_ret();
		$ret[0] = $SERVER_MOVE_TOWER_ITEM . $ret[0];
		$tower = new Tower($player);
		$ret[] = $tower->get_ret();
		
	break;
    case $ACT_COPYCAT_BOOST:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$ret[0] = $SERVER_TOWER_SAVE . $ret[0];
		$helper_id = (int) explode(";", $DATA)[0];
		$lvls = explode(";", $player->data['copycat_lvl']);
        $cost = (73516 + 2053 * ($lvls[$helper_id - 1] - 150)) * 100;
		
		if($player->data['silver'] < $cost)
		{
			$ret = array($ERR_TOO_EXPENSIVE);
			break;
		}
		
		$lvls[$helper_id - 1] +=1;
		
		$db->query("UPDATE `user_data` SET `copycat_lvl` = '".$lvls[0].";".$lvls[1].";".$lvls[2]."', `silver` = `silver` - ".$cost." WHERE `user_id` = ".$player->id."");
		
		$player->login($SSID);
		$ret = $player->get_ret();
		$ret[0] = $SERVER_TOWER_SAVE . $ret[0];
		$tower = new Tower($player);
		$ret[] = $tower->get_ret();
		
	break;
    case $ACT_TOWER_TRY:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['tower_level'] >= 101)
		{
			break;
		}
		
		if(!$player->get_free_slot())
		{
			$ret = array($ERR_INVENTORY_FULL);
			break;
		}
		
		if($GLOBALS['SERVER_TIME'] < $player->data['dungeon_time'])
		{
			if($player->data['mushroom'] < 1)
			{
				$ret = array($ERR_NO_MUSH_MQ);
				break;
			}
			else
			{
				$player->data['mushroom']--;
			}
		}
		else
		{
			$player->data['dungeon_time'] = $GLOBALS['SERVER_TIME'] + 3600;
		}
		
		$monster = new Monster;
		$monster->load_monster_tower($player);
		
		$tower = new Tower($player);
		
		$load_player_data = $player->get_ret();
		
		$ret = $tower->fight($monster, $player);
		
		$player_ret = $player->get_ret();
		
		$ret[count($ret) - 1] .= $player_ret[0];
		
		for($i=1;$i<Count($player_ret);$i++)
		{
			array_push($ret, $player_ret[$i]);
		}
		
		$ret[0] = $SERVER_TOWER_FIGHT . $ret[0];
		
	break;
    case $ACT_ENTER_DUNGEON:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['magic_mirror'] != "1111111111111")
		{
			$ret = $player->get_ret();
			if($player->data['status'] == 2)
			{
				$ret[0] = Server::quest_skip() . $ret[0];
				break;
			}
			else if($player->data['status'] == 1)
			{
				$ret = array($ACT_WORK_ENTER . $player->get_gold($quest = false) . ";0");
				break;
			}
		}
		
		$ret = $player->get_ret();
		
		for($i=1;$i<14;$i++)
		{
			if($player->data["dungeon_".$i] == 1 OR $i > 9 AND $player->data["dungeon_".$i] == 0 AND $player->dungeon_done == (90 + (10 * ($i - 10))))
			{
				$db->query("UPDATE `user_data` SET `dungeon_".$i."` = 2 WHERE `user_id` = ".$player->id."");
			}
		}
		
		$ret[0] = $ACT_ENTER_DUNGEON . $ret[0];
		
		$monster = new Monster;
		
		for($i=1;$i<14;$i++)
		{
			$monster->load_monster_dung($i, $player);
			if($i == 9 AND $player->data['dungeon_9'] == 11)
			{
				$look = "-1";
			}
			else
			{
				$look = $monster->face[1];
			}
			
			if($i == 1)
			{
				$ret[Count($ret) - 1] .= ";". $look;
			}
			else
			{
				array_push($ret, $look);
			}
		}
		
    break;
    case $ACT_MAINQUEST:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if(!$player->get_free_slot())
		{
			$ret = array($ERR_INVENTORY_FULL);
			break;
		}
		
		$load_player = $player->get_ret();
		
		$dungeon_select = $DATA;
		
		if(!intval($dungeon_select))
		{
			break;
		}
		
		if($GLOBALS['SERVER_TIME'] < $player->data['dungeon_time'])
		{
			if($player->data['mushroom'] < 1)
			{
				$ret = array($ERR_NO_MUSH_MQ);
				break;
			}
			else
			{
				$player->data['mushroom']--;
			}
		}
		else
		{
			$player->data['dungeon_time'] = $GLOBALS['SERVER_TIME'] + 3600;
		}
		
		if($player->data['dungeon_'.$dungeon_select] >= 12 OR $player->data['dungeon_'.$dungeon_select] < 1)
		{
			break;
		}
		
		if($dungeon_select == 10 AND $player->dungeon_done < 90)
		{
			break;
		}
		else if($dungeon_select == 11 AND $player->data['dungeon_10'] != 12)
		{
			break;
		}
		else if($dungeon_select == 12 AND $player->data['dungeon_11'] != 12)
		{
			break;
		}
		else if($dungeon_select == 13 AND $player->data['dungeon_12'] != 12)
		{
			break;
		}
		
		$ret = array();
		
		$slot = "-1";
		$monster_gold = $player->get_gold(true);
		
		$monster = new Monster;
		$monster->load_monster_dung($dungeon_select, $player);
		
		array_push($ret, $player->hp, $player->attr_str, $player->attr_agi, $player->attr_int, $player->attr_wit, $player->attr_luck);
		
		array_push($ret, $monster->hp, $monster->attr_str,  $monster->attr_agi, $monster->attr_int, $monster->attr_wit, $monster->attr_luck . ";");
		
		$fight = new Fight;
		
		$sim = $fight->fight_cal($player, $monster);
		$sim_reverse = $sim[1];
		$sim = $sim[0];
		
		$win = $sim[count($sim) - 1][0] <= 0 ? true : false;
		
		if($win)
		{
			$player->data['exp'] += $monster->exp;
			while($player->data['exp'] > Server::get_exp($player->data['lvl']))
			{
				$player->data['exp'] -= Server::get_exp($player->data['lvl']);
				$player->data['lvl']++;
			}
			
			$player->add_stats_archivment_dung();
			
			if($player->data['dungeon_'.$dungeon_select] == 11 OR rand(1, 2) == 2)
			{
				$slot = $player->get_free_slot();
				if($slot)
				{
					$item = new Item;
					$item->gen_item([1,2,3,4,5,6,7,8,9,10][rand(0, 9)], $player->data['lvl'], $player->data['class']);
					$itemwin = $item->item;
					$itemwin['slot'] = $slot;
					$itemwin['mush'] = 0;
					$itemwin['owner_id'] = $player->id;
					$item->insert_item($itemwin, "items");
				}
			}
			$player->data['dungeon_'.$dungeon_select]++;
			$player->data['silver']+= $monster_gold;
		}
		
		$GLOBALS['db']->query("UPDATE `user_data` SET 
			`exp` = '".$player->data['exp']."',
			`silver` = '".$player->data['silver']."', 
			`mushroom` = ".$player->data['mushroom'].",
			`lvl` = ".$player->data['lvl'].",
			`dungeon_time` = ".$player->data['dungeon_time'].",
			`dungeon_".$dungeon_select."` = ".$player->data['dungeon_'.$dungeon_select]."
			WHERE `user_id` = ".$player->id."");
		
		$ret[count($ret) - 1] .= $sim[0][0] . "/" . $sim[0][1] . "/" . $sim[0][2];
		
		for($i=1;$i<count($sim);$i++) 
		{
			array_push($ret, $sim[$i][0], $sim[$i][1], $sim[$i][2]);
		}
		
		if($dungeon_select == 9 AND $player->data['dungeon_9'] == 11)
		{
			$monster->face = $monster->face;
			$monster->face[0] = "-".$monster->face[0];
		}
		else
		{
			$monster->face[1] = "-".$monster->face[1];
		}
		
		$ret[count($ret) -1] .= $fight->load_view($player, $monster);
		
		$player->login($SSID);
		
		$load_player = $player->get_ret();
		
		$ret[] = "0;3;0;" . $monster->exp . ";" . $monster_gold . ";" . ($slot - 11) . ";";
		
		$ret[Count($ret) - 1] = $ret[Count($ret) - 1] . $load_player[0];
		
		for($i=1;$i<Count($load_player) - 1;$i++)
		{
			array_push($ret, $load_player[$i]);
		}
		
		$ret[0] = $SERVER_MAINQUEST . $ret[0];
		
	break;
    case $ACT_GAMBLE:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$bet = explode(';', $DATA);
        
        if ($bet[0] > $player->data['silver']) 
		{
            break;
        }
        
        if ($bet[1] > $player->data['mushroom'] OR $bet[1] > $player->data['mushroom_buy'])
		{
            break;
        }
        
        $r = mt_rand(1, 3);
		
		if ($r == 3)
		{
            $player->data['silver'] += $bet[0];
            $player->data['mushroom'] += $bet[1];
        }
        else
		{
            $player->data['silver'] -= $bet[0];
            $player->data['mushroom'] -= $bet[1];
        }
		
		$db->query("UPDATE `user_data` SET `silver` = ".$player->data['silver'].", `mushroom` =".$player->data['mushroom']." WHERE `user_id`=".$player->id."");
		
		$player->login($SSID);
		$ret = $player->get_ret();
		
		if($r == 3)
		{
			$ret[0] = $SERVER_BET_WON . $ret[0];
		}
		else
		{
			$ret[0] = $SERVER_BET_LOST . $ret[0];
		}
		
    break;
    case $ACT_USE_ITEM:
	
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		$data = explode(';', $DATA);
		
		$type = htmlspecialchars($data[0]); // 1 move item  z hero //  2 move item z backpack // 3, 4 item z shop shakes or fidget
		$slot = htmlspecialchars($data[1]); // select slot
		$act = htmlspecialchars($data[2]); // sell // move
		$nslot = htmlspecialchars($data[3]); // new select slot
		
		switch($type)
		{
			case 1://z hero
				switch($act)
				{
					case 0:
						$ret = $player->get_ret();
						$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
					break;
					case 2:
					
						$nslot += 10;
						
						if($slot < 1 OR $slot > 10 OR $nslot < 11 OR $nslot > 16)
							break;
						
						$item = new Item;
							
						$check_item = $item->get_item($player->id, $slot, "items");
							
						if($check_item)
						{
							$select_item = $item->item;
							$select_item['slot'] = $nslot;
							$item->update_item($select_item, "items");
						}
							
						$ret = $player->get_ret();
						$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
						
					break;
				}
			break;
			case 2://z backpack
				switch($act)
				{
					case 0:
					
						$slot +=10;
						
						if($slot < 11 OR $slot > 15)
							break;
						
						$item = new Item;
						
						$check_item = $item->get_item($player->id, $slot, "items");
							
						if($check_item)
						{
							$select_item = $item->item;
							$db->query("DELETE FROM `items` WHERE `id` = ".$select_item['id']."");
							$db->query("UPDATE `user_data` SET `silver` = `silver` + ".$select_item['gold']." WHERE `user_id` =".$player->id."");
						}
						
						$player->login($SSID);
						$ret = $player->get_ret();
						$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
						
					break;
					case 1:
					
						$slot += 10;
						
						if($nslot != "-1" OR $slot < 11 OR $slot > 15)
							break;
						
						$item = new Item;
							
						$check_item = $item->get_item($player->id, $slot, "items");
							
						if($check_item)
						{
							$select_item = $item->item;
							
							if($select_item['item_type'] < 11)
							{
								if($select_item['item_type'] > 0 AND $select_item['item_type'] < 8)
								{
									$item_class = round($select_item['item_id'] / 1000) + 1;
									if($player->data['class'] != $item_class)
									{
										$player->login($SSID);
										$ret = $player->get_ret();
										$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
										break;
									}
								}
								
								$check_item_hero = $item->get_item($player->id, $item->get_slot_item($select_item['item_type']), "items");
								if(!$check_item_hero)
								{
									$select_item['slot'] = $item->get_slot_item($select_item['item_type']);
									$item->update_item($select_item, "items");
								}
								else
								{
									$hero_item = $item->item;
									$backpack_item = $select_item;
									
									$db->query("UPDATE `items` SET `slot` = ".$select_item['slot']." WHERE `id` = ".$hero_item['id']."");
									$db->query("UPDATE `items` SET `slot` = ".$hero_item['slot']." WHERE `id` = ".$backpack_item['id'].""); 
								}
								$player->login($SSID);
								$ret = $player->get_ret();
								$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
							}
							else if($select_item['item_type'] == 11)
							{
								if($select_item['item_id'] == 20)
								{
									if($player->data['toilet'] == 0)
									{
										$db->query("UPDATE `user_data` SET `toilet` = 1 WHERE `user_id` = ".$player->id."");
										$db->query("DELETE FROM `items` WHERE `id` = ".$select_item['id']."");
										$ret = $player->get_ret();
										$ret[0] = $SERVER_TOILET_UNLOCKED . $ret[0];
										$ret[] = ";" . $player->data['toilet_full'] . ";" . $player->data['aura'] . ";" . $player->data['fill_level'] . ";" . $player->data['fill_level_next'];
									}
									break;
								}
								
								if($player->data['magic_mirror'] != "1111111111111")
								{
									if($player->data['status'] == 2)
									{
										$ret[0] = $SERVER_QUEST_SKIP_ALLOWED . $ret[0];
										break;
									}
									else if($player->data['status'] == 1)
									{
										$ret[0] = $ACT_WORK_ENTER . $ret[0];
										break;
									}
								}
								
								if($player->data['dungeon_'.$select_item['item_id']] == 0)
								{
									$db->query("UPDATE `user_data` SET `dungeon_".$select_item['item_id']."` = 1 WHERE `user_id` = ".$player->id."");
								}
								
								$db->query("DELETE FROM `items` WHERE `id` = ".$select_item['id']."");
								
								$player->login($SSID);
								$ret = $player->get_ret();
								
								$ret[0] = $ACT_ENTER_DUNGEON . $ret[0];
								
								for($i=1;$i<10;$i++)
								{
									if($player->data["dungeon_".$i] == 1)
									{
										$db->query("UPDATE `user_data` SET `dungeon_".$i."` = 2 WHERE `user_id` = ".$player->id."");
									}
								}
								
								$player->login($SSID);
								$load_data = $player->get_ret();
								
								$monster = new Monster;
								
								for($i=1;$i<14;$i++)
								{
									$monster->load_monster_dung($i, $player);
									if($i == 9 AND $player->data['dungeon_9'] == 11)
									{
										$look = "-1";
									}
									else
									{
										$look = $monster->face[1];
									}
									
									if($i == 1)
									{
										$ret[Count($ret) - 1] .= ";". $look;
									}
									else
									{
										array_push($ret, $look);
									}
								}
								
							}
							else if($select_item['item_type'] == 12)
							{
								$potion_slot = 0;
								$potion_time = ($select_item['attr_val_1'] * 3600);
								
								$qry = $db->query("SELECT * FROM `user_potion` WHERE `owner` = ".$player->id." AND `potion_id` = ".$select_item['item_id']." OR `potion_id` = ".$select_item['item_id'] + 5 ." `potion_id` = ".$select_item['item_id'] + 10 ." ORDER BY `potion_id` DESC");
								if($qry->rowCount() > 0)
								{
									
								}
								else
								{
									
								}
								
								
								if($potion_slot > 0)
								{
									$db->query("UPDATE `user_data` SET `potion_id".$potion_slot."` = ".$select_item['item_id'].", `potion_value".$potion_slot."` = ".$select_item['attr_val_2'].", `potion_time".$potion_slot."` = ".$potion_time."  WHERE `user_id` = ".$player->id."");
									$db->query("DELETE FROM `items` WHERE `id` = ".$select_item['id']."");
								}
								
								$player->login($SSID);
								$ret = $player->get_ret();
								$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
						
							}
						}
						
					break;
					case 2:
						
						$slot += 10;
						$nslot += 10;
						
						if($slot < 11 OR $slot > 15 OR $nslot < 11 OR $nslot > 15)
							break;
						
						$item = new Item;
							
						$check_item = $item->get_item($player->id, $slot, "items");
						
						if($check_item)
						{
							$select_item = $item->item;

							$check_item_hero = $item->get_item($player->id, $nslot, "items");
							
							if(!$check_item_hero)
							{
								$select_item['slot'] = $nslot;
								$item->update_item($select_item, "items");
							}
							else
							{
								//TODO
							}
						}
						
						$ret = $player->get_ret();
						$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
						
					break;
					case 10:
						
						$slot += 10;
						
						$item = new Item;
						$check_item = $item->get_item($player->id, $slot, "items");
						$ret = $player->get_ret();
						
						if($check_item)
						{
							if($player->data['fill_level'] == $player->data['fill_level_next'])
							{
								$ret[0] = $SERVER_TOILET_TANKFULL . $ret[0];
								$ret[] = ";" . $player->data['toilet_full'] . ";" . $player->data['aura'] . ";" . $player->data['fill_level'] . ";" . $player->data['fill_level_next'];
								break;
							}
							
							$select_item = $item->item;
							
							if($select_item['toilet'] == 1)
							{
								$ret[0] = $ERR_ITEM_TOILET . $ret[0];
								$ret[] = ";" . $player->data['toilet_full'] . ";" . $player->data['aura'] . ";" . $player->data['fill_level'] . ";" . $player->data['fill_level_next'];
								break;
							}
							
							if($player->data['toilet_full'] == 0)
							{
								$value = 0;
							
								if($select_item['item_type'] < 8)
								{
									$oldClass = round($select_item['item_id'] / 1000);
									$item_id = $select_item['item_id'] - (1000 * $oldClass);
									if($item_id > 49 AND $item_id < 61)
									{
										$value = 50;
									}
									else
									{
										$value = 25;
									}
								}
								else if($select_item['item_type'] < 11)
								{
									$value = 25;
								}
								else
								{
									$value = 10;
								}
								
								$player->data['fill_level'] += $value;
								if($player->data['fill_level'] > $player->data['fill_level_next'])
								{
									$player->data['fill_level'] = $player->data['fill_level_next'];
								}
								$db->query("UPDATE `user_data` SET `toilet_full` = 1, `fill_level` = ".$player->data['fill_level']." WHERE `user_id` = ".$player->id."");
							}
							
							$item->change_item($select_item, $player->data['lvl']);
							$item->update_item($item->item, "items");
						}
						
						$player->login($SSID);
						$ret = $player->get_ret();
						$ret[0] = $SERVER_TOILET_DROPPED . $ret[0];
						$ret[] = ";" . $player->data['toilet_full'] . ";" . $player->data['aura'] . ";" . $player->data['fill_level'] . ";" . $player->data['fill_level_next'];
					break;
				}
			break;
			case 3://z shop
			case 4://z shop
				
				if($slot < 1 OR $slot > 6)
					break;
				
				$type -=3;
				$table = $type == 0 ? "items_shakes" : "items_fidget";
				$items_type = $type == 0 ? rand(1, 7) : rand(8, 10);
				
				$item = new Item;
				
				$check_item = $item->get_item($player->id, $slot, $table);
				
				if(!$check_item)
				{
					break;
				}
				
				$select_item = $item->item;
					
				if($player->data['mushroom'] < $select_item['mush'])
				{
					$ret = array($ERR_NO_MUSH_MQ);
					break;
				}
						
				if($player->data['silver'] < $select_item['gold'])
				{
					$ret = array($ERR_TOO_EXPENSIVE);
					break;
				}
				
				$item_gold = $select_item['gold'];
				$item_mush = $select_item['mush'];
					
				switch($act)
				{
					case 1:
							
						if($nslot != "-1")
						break;
						
						if($select_item['item_type'] < 11)
						{
							$select_item['slot'] = $item->get_slot_item($select_item['item_type']);
							if($player->check_slot($select_item['slot']))
							{
								$select_item['gold'] = round($select_item['gold'] / 2);
								$select_item['mush'] = 0;
								$select_item['owner_id'] = $player->id;
								$item->insert_item($select_item, "items");
								$db->query("DELETE FROM `".$table."` WHERE `id` = ".$select_item['id']."");
									
								$item->gen_item($items_type, $player->data['lvl'], $player->data['class']);
								$new_item = $item->item;
								$new_item['slot'] = $slot;
								$item->insert_item($new_item, $table);
								$db->query("UPDATE `user_data` SET `silver` = `silver` - ".$item_gold.", `mushroom` = `mushroom` - ".$item_mush." WHERE `user_id` = ".$player->id."");
							}
						}
						else if($select_item['item_type'] == 11)
						{
							//TODO
						}
						else if($select_item['item_type'] == 12)
						{
							//TODO
						}
						
					break;
					case 2:
						
						$nslot += 10;
					
						if($player->check_slot($nslot))
						{
							$select_item['slot'] = $nslot;
							$select_item['gold'] = round($select_item['gold'] /= 2);
							$select_item['mush'] = 0;
							$select_item['owner_id'] = $player->id;
							$item->insert_item($select_item, "items");
							$db->query("DELETE FROM `".$table."` WHERE `id` = ".$select_item['id']."");
								
							$item->gen_item($items_type, $player->data['lvl'], $player->data['class']);
							$new_item = $item->item;
							$new_item['slot'] = $slot;
							$item->insert_item($new_item, $table);
							$db->query("UPDATE `user_data` SET `silver` = `silver` - ".$item_gold.", `mushroom` = `mushroom` - ".$item_mush." WHERE `user_id` = ".$player->id."");
						}
					break;
				}
				
				$player->login($SSID);
				$ret = $player->get_ret();
				$ret[0] = $SERVER_SAVEGAME_STAY . $ret[0];
				
			break;
		}
		
	break;
	case $ACT_RESEND_EMAIL:
	break;
	case $ACT_BUY_LUXURY:
		
		$player = new Player;
		
		if($player->login($SSID))
		{
			$ret = array($ERR_SESSION_ID_EXPIRED);
            break;
		}
		
		if($player->data['golden_frame'] == 32)
		{
			break;
		}
		
		if($player->data['mushroom'] < 1000)
		{
			break;
		}
		
		$player->data['mushroom'] -= 1000;
		
		$db->query("UPDATE `user_data` SET `golden_frame` = 32, `mushroom` = ".$player->data['mushroom']." WHERE `user_id` = ".$player->id."");
		
		$player->login($SSID);
		
		$ret = $player->get_ret();
		
		$ret [0] = $ACT_HERO . $ret [0];
		
		$ret[] .= ";" . Server::utf8_format(urldecode($player->data['user_desc'])) . ";";
		
	break;
	case $ACT_SETTINGS:
		$ret = array($ACT_SETTINGS);
    break;
	default:
		$ret = array($ERR_SESSION_ID_EXPIRED);
    break;
}

echo join("/", $ret);

?>