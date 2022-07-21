<?php
class Guild
{
	public $members;
	public $g_data;
	
	function __construct($guild)
	{
		global $db;
		
		$this->g_data = $guild;
		
		$members = $db->query("SELECT * FROM `user_data` WHERE `guild_id` = ".$this->g_data['guild_id']." ORDER BY `guild_rank` ASC, `lvl` ASC");
		$this->members = $members->fetchAll();
	}
	
	public function get_ret($member)
	{
		global $db;
		
		$ret = array_fill(0, 372, "0");
		
		$members_invite = array();
		$qry = $db->query("SELECT * FROM `guild_invites` WHERE `guild_id` = ".$this->g_data['guild_id']."");
		if($qry->rowCount() > 0)
		{
			$invites = $qry->fetchAll();
			for($i=0;$i<$qry->rowCount();$i++)
			{
				$qry_i = $db->query("SELECT * FROM `user_data` WHERE `user_id` = ".$invites[$i]['user_id']."");
				$members_invite[$i] = $qry_i->fetch();
			}
		}
		
		$guild_position = $db->query("SELECT pos FROM (SELECT honor, dung, guild_id, name, @r:=@r+1 as pos 
		FROM guilds, (select @r:=0) r0
		ORDER BY honor DESC, dung DESC, guild_id DESC)  user_data_ordered WHERE `guild_id` = ".$this->g_data['guild_id']."");
		$guild_position = $guild_position->fetchAll();
		$guild_position = $guild_position[0][0];
		
		$guild_attack = $db->query("SELECT * FROM `guild_attacks` WHERE `guild_id` = ".$this->g_data['guild_id']."");
		$guild_attack = $guild_attack->fetch();
		
		$guild_target = $db->query("SELECT * FROM `guild_attacks` WHERE `target_id` = ".$this->g_data['guild_id']."");
		$guild_target = $guild_target->fetch();
		
		if($member)
		{
			$ret[0] = "006" . $this->g_data['guild_id'];
			$ret[1] = $this->g_data['silver'];
			$ret[2] = $this->g_data['mushroom'];
			$ret[4] = $this->g_data['catapult'];
			$ret[5] = $this->g_data['fortress'];
			$ret[6] = $this->g_data['treasure'];
			$ret[7] = $this->g_data['instructor'];
		}
		else
		{
			$ret[0] = "172" . $this->g_data['guild_id'];
		}
		
		$ret[3] = Count($this->members) + Count($members_invite);
		$ret[8] = $this->g_data['dung'];
		$ret[9] = 0; // ATAK NA LOCHY
		$ret[10] = 2;
		$ret[11] = 1407846286;
		$ret[12] = 1407846286;
		$ret[13] = $this->g_data['honor'];
		
		$e = 0;
		for($i=14;$i<Count($this->members) + 14;$i++)
		{
			$ret[$i] = $this->members[$e]['user_id'];
			$e++;
		}
		
		$e = 0;
		for($i=Count($this->members) + 16;$i<Count($members_invite) + Count($this->members) + 16;$i++)
		{
			$ret[$i] = $members_invite[$e]['user_id'];
			$e++;
		}
		
		$e = 0;
		for($i=64;$i<Count($this->members) + 64;$i++)
		{
			$ret[$i] = $this->get_attack_join_info($this->members[$e]) . $this->members[$e]['lvl'];
			$e++;
		}
		
		$e = 0;
		for($i=Count($this->members) + 64;$i<Count($members_invite) + Count($this->members) + 64;$i++)
		{
			$ret[$i] = $members_invite[$e]['lvl'];
			$e++;
		}
		
		if($member)
		{
			$e = 0;
			for($i=114;$i<Count($this->members) + 114;$i++)
			{
				$ret[$i] = $this->members[$e]['last_activ'];
				$e++;
			}
			
			$e = 0;
			for($i=214;$i<Count($this->members) + 214;$i++)
			{
				$ret[$i] = $this->members[$e]['guild_silver'];
				$e++;
			}
			
			$e = 0;
			for($i=264;$i<Count($this->members) + 264;$i++)
			{
				$ret[$i] = $this->members[$e]['guild_mushroom'];
				$e++;
			}
		}
		
		$e = 0;
		for($i=314;$i<Count($this->members) + 314;$i++)
		{
			$ret[$i] = $this->members[$e]['guild_rank'];
			$e++;
		}
		for($i=Count($this->members) + 314;$i<Count($members_invite) + Count($this->members) + 314;$i++)
		{
			$ret[$i] = 4;
		}
		
		$ret[364] = $guild_attack['target_id'];
		$ret[365] = $guild_attack['attack_time'];
		
		$ret[366] = $guild_target['guild_id'];
		$ret[367] = $guild_target['attack_time'];
		
		$ret[368] = 0; //DUNGEON ATTACK 1 ? 0
		$ret[369] = 0; //DUNGEON ATTACK TIME

		$ret[370] = $GLOBALS['SERVER_TIME'] . ";" . urldecode($this->g_data['arms']) ."Â§" . Server::utf8_format(urldecode($this->g_data['description'])) . ";";
		
		$e = 0;
		for($i=371;$i<Count($this->members) + 371;$i++)
		{
			$nick = $db->query("SELECT `user_name` FROM `user_data` WHERE `user_id` = ".$this->members[$e]['user_id']."");
			$nick = $nick->fetch();
			$ret[$i] = $nick['user_name'];
			$e ++;
		}
		$e = 0;
		for($i=Count($this->members) + 371;$i<Count($members_invite) + Count($this->members) + 371;$i++)
		{
			$nick = $db->query("SELECT `user_name` FROM `user_data` WHERE `user_id` = ".$members_invite[$e]['user_id']."");
			$nick = $nick->fetch();
			$ret[$i] = $nick['user_name'];
			$e++;
		}
		
		$ret[Count($ret) - 1] .= ";" . $this->g_data['name'] . ";" . $this->g_data['honor'] . ";" . $guild_position . ";";
		
		if(!$member)
		{
			$ret[Count($ret) - 1] .= $this->get_guild_war_cost();
		}
		
		return $ret;
	}
	
	public function get_attack_join_info($player)
	{
		$action = "";
		
		if($player['guild_attack'] == 1 && $player['guild_defend'] == 0)
		{
			$action = 1;
		}
		else if($player['guild_attack'] == 0 && $player['guild_defend'] == 1)
		{
			$action = 2;
		}
		else if($player['guild_attack'] == 1 && $player['guild_defend'] == 1)
		{
			$action = 3;
		}

		if($player['lvl'] < 100 && $player['lvl'] >= 10 )
		{
			return  $action . 0 ;
		}
		else if($player['lvl'] >= 100)
		{
			return  $action ;
		}
		else if($player['lvl'] < 10)
		{
			return  $action . 0 . 0  ;
		}
	}
	
	public function generate_arms($min, $max)
	{
		$number = rand($min, $max);
	
		if($number < 10)
		{
			$number = "0".$number;
		}
		
		return $number;
	}
	
	public function send_chat($player, $data)
	{
		global $db;
		
		$timeWR = Date("H:i", Time());
		
		$message = htmlspecialchars($data[0]);
		
		$other = htmlspecialchars($data[1]);
		
		$qry = $db->prepare("INSERT INTO `guild_chat` (`sender_id`, `reciver_id`, `guild_id`, `msg`, `time`) VALUES(:uid, :rid, :gid, :msg, :time)");
		$qry->bindParam(":uid", $player->data['user_id']);
		$qry->bindParam(":rid", $other, PDO::PARAM_INT);
		$qry->bindParam(":gid", $player->data['guild_id']);
		$qry->bindParam(":msg", $message, PDO::PARAM_STR);
		$qry->bindParam(":time", $timeWR);
		$qry->execute();
	}
	
	public function send_chat_other($gid, $uid, $rid, $time, $msg, $type)
	{
		global $db;
		
		$qry = $db->prepare("INSERT INTO `guild_chat` (`guild_id`, `sender_id`, `reciver_id`, `time`, `type`, `msg`) VALUES(:gid, :uid, :rid, :time, :type, :msg)");
		$qry->bindParam(":gid", $gid, PDO::PARAM_INT);
		$qry->bindParam(":uid", $uid, PDO::PARAM_INT);
		$qry->bindParam(":rid", $rid, PDO::PARAM_INT);
		$qry->bindParam(":time", $time, PDO::PARAM_INT);
		$qry->bindParam(":msg", $msg, PDO::PARAM_STR);
		$qry->bindParam(":type", $type, PDO::PARAM_INT);
		$qry->execute();
	}
	
	public function get_chat_history($player)
	{
		global $db;
		
		$qry = $db->query("SELECT * FROM `guild_chat` WHERE `guild_id` = ".$player->data['guild_id']." ORDER BY `msg_id` DESC");
		$chat = $qry->fetchAll();
		
		if($qry->rowCount() == 0 OR $player->data['gchat_last'] == $chat[0]['msg_id'])
		{
			return array($GLOBALS['ERR_NO_CHAT_INFO']);
		}
		
		$messages = array_fill(0, 5, "");
		
		if(Count($chat) < 5)
		{
			array_push($messages, "#sr#". Server::start_time());
		}
		
		for($i = 0; $i<count($chat);$i++)
		{
			
			$messages[$i] = $chat[$i]['time'] ." ". Player::get_player_name($chat[$i]['sender_id']) . ": " . $chat[$i]['msg']; 
		}
		
		$messages[0] = "161".$messages[0];
		
		return $messages;
	}
	
	public function get_guild_build_cost($lvl)
	{
		$lvl++;
		$cost['silver'] = [500, 900, 1500, 2200, 3200, 4500, 6000, 7800, 10100, 12800, 16000, 19700, 24000, 29100, 34800, 41200, 48700, 57000, 66400, 77000, 88800, 101800, 116400, 132500, 150200, 169900, 191400, 214900, 240800, 269000, 299600, 333000, 369200, 408300, 450900, 496800, 546100, 599600, 656900, 718400, 784700, 855700, 931500, 1012900, 1099700, 1192200, 1291200, 1396500, 1508200, 1627700][$lvl];
		if($lvl >= 25)
		{
			$cost['mushroom'] = round(($lvl - 25) * 5);
		}
		else
		{
			$cost['mushroom'] = 0;
		}
		return $cost;
	}
	
	public function get_guild_war_cost()
	{
		return [150, 175, 200, 250, 300, 350, 400, 500, 575, 675, 800, 900, 1000, 1150, 1300, 1500, 1700, 1900, 2150, 2400, 2700, 3000, 3350, 3700, 4100, 4500, 5000, 5500, 6000, 6600, 7200, 7850, 8600, 9300, 10000, 11000, 12500, 14500, 17500, 20000, 25000][($this->g_data['fortress'] / 10) - 1] * 100;
	}
}
?>