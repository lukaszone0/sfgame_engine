<?php
class Account
{
	public function register($data)
	{
		global $db, $SERVER_IP, $SERVER_TIME;
		
		$nick = $data[0];
		$password = md5($data[1]);
		$email = $data[2];
		$race = $data[5];
		$gender = $data[6];
		$class = $data[7];
		$face = explode("/", $data[8]);
		$email_code = Account::get_code();
		$stats = Account::get_default_stats($class, $race);
		
		$qry = $db->prepare("INSERT INTO user_data (`user_name`, `password`, `email`, `email_code`, `reg_ip`, `face1`, `face2`, `face3`, `face4`, `face5`, `face6`, `face7`, `face8`, `face9`, `face10`, `reg_date`, `class`, `race`, `gender`, `attr_str`, `attr_agi`, `attr_int`, `attr_wit`, `attr_luck`) 
		VALUES (:name, :pass, :email, :emailcode, :regip, :face1, :face2, :face3, :face4, :face5, :face6, :face7, :face8, :face9, :face10, :regdate, :class, :race, :gender, :str, :agi, :int, :wit, :luck)");
		$qry->bindParam(":name", $nick);
		$qry->bindParam(":pass", $password);
		$qry->bindParam(":email", $email);
		$qry->bindParam(":emailcode", $email_code);
		$qry->bindParam(":regip", $SERVER_IP);
		for($i = 1; $i < 11; $i ++) 
		{
			$qry->bindParam( ":face" . $i, $face[$i - 1]);
		}
		$qry->bindParam(":regdate", $SERVER_TIME);
		$qry->bindParam(":class", $class);
		$qry->bindParam(":race", $race);
		$qry->bindParam(":gender", $gender);
		$qry->bindParam(':str', $stats[0]);
		$qry->bindParam(':agi', $stats[1]);
		$qry->bindParam(':int', $stats[2]);
		$qry->bindParam(':wit', $stats[3]);
		$qry->bindParam(':luck', $stats[4]);
		$qry->execute();
		
		$qry = $db->prepare("SELECT `user_id` FROM `user_data` WHERE `user_name` = :nick LIMIT 1");
		$qry->bindParam(':nick', $nick);
		$qry->execute();
		$user_id = $qry->fetch(PDO::FETCH_ASSOC)['user_id'];
		
		$item = new Item;
		$item->gen_item(1, 1, $class);
		$weapon = $item->item;
		$weapon['owner_id'] = $user_id;
		$weapon['slot'] = 9;
		$weapon['item_id'] = 1 + ( 1000 * ($class - 1));
		$item->insert_item($weapon, "items");
		
		return $user_id;
	}
	
	public function check_nick($nick)
	{
		$qry = $GLOBALS['db']->prepare("SELECT `user_id` FROM `user_data` WHERE `user_name` = :name");
		$qry->bindParam(':name', $nick);
		$qry->execute();
			
		if($qry->rowCount() > 0) 
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	public function check_email($email)
	{
		$qry = $GLOBALS['db']->prepare("SELECT `user_id` FROM `user_data` WHERE `email` = :email");
		$qry->bindParam(':email', $email);
		$qry->execute();
			
		if($qry->rowCount() > 0) 
		{
			return false;
		}
		else
		{
			return true;
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
	
	public function send_mail($code, $uid, $nick, $password, $email)
	{
		global $SERVER_ADDRES;
			
		$subiect = "Shakes & Fidget: Witamy w Shakes & Fidget!";	
		$message = 
		'<html>
			<head>
				<title>Shakes & Fidget: Witamy w Shakes & Fidget!</title>
				<meta name="description" content="The fun Shakes & Fidget browser game">
				<meta content="ZeuSxp" name="author"></meta>
				<meta charset="UTF-8"></meta>
			</head>
			<body>
				<div class="bbtext" style="padding: 10px;">
					Witaj '.$nick.',<br>
					<br> witamy w Shakes &amp; Fidget! Prze¿yj wspania³e przygody i walcz z przeciwnikami na Arenie.<br>
					<br> Jako prezent powitalny otrzymujesz 25 Grzybów. Kliknij <a href="http://'.$SERVER_ADDRES.'/?code='.$code.'&user='.$uid.'">tutaj</a> aby odebraæ nagrodê.<br>
					<br> Twoje dane do logowania:<br>Nazwa: '.$nick.'<br>Has³o: '.$password.'<br>
					<br> ¯yczymy dobrej zabawy!<br>Twój Shakes &amp; Fidget Zespó³<br>
					<hr> Otrzymujesz tego maila poniewa¿ zarejestrowa³eœ siê w grze *Shakes &amp; Fidget * podaj¹c adres email '.$email.'.<br>
					<br> Playa Games GmbH | Alstertor 9 | D-20095 Hamburg | Support: http://'.$SERVER_ADDRES.'/support<br>CEO: Hannes Beuck | Amtsgericht Hamburg, HRB 109725 | VAT ID: DE815081395
				</div>
			</body>
			</html>';
			
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

		if(mail($email, $subiect, $message, $headers))
		{
			return true;
		}
		else
		{
			return false;
		}
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