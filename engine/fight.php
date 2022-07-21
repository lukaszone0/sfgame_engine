<?php
class Fight
{
	
	public function fight_cal($asdf, $qwer)
	{
		$logs = array();
		$logs_reverse = array();
		
		$first = rand(0, 2) > (1 - $asdf->bonus_reaction) ? FALSE: TRUE;
		
		$round = 1;
		
		while($asdf->hp > 0 AND $qwer->hp > 0)
		{
			$log1 = array(0, 0, 0);
			$log2 = array(0, 0, 0);
			$log1_reverse = array(0, 0, 0);
			$log2_reverse = array(0, 0, 0);
			
			$asdf_dmg = $this->damage_cal($asdf, $qwer);
			$qwer_dmg = $this->damage_cal($qwer, $asdf);
			
			if($first)
			{
				if($asdf->hp > 0)
				{
					$log1 = $asdf_dmg;
					$qwer->hp -= $log1[1];
					$log2_reverse = $asdf_dmg;
				}
			}
			
			if($qwer->hp > 0)
			{
				$log2 = $qwer_dmg;
				$asdf->hp -= $log2[1];
				if($round > 1 OR $first == false)
				{
					$log1_reverse = $qwer_dmg;
				}
			}
			
			
			$log1[0] = $asdf->hp;
			$log2[0] = $qwer->hp;
			
			$log1_reverse[0] = $qwer->hp;
			$log2_reverse[0] = $asdf->hp;
			
			$first = TRUE;
			
			array_push($logs, $log1, $log2);
			array_push($logs_reverse, $log1_reverse, $log2_reverse);
			
			$round++;
		}
		
		return array($logs, $logs_reverse);
	}
	
	private function damage_cal($attack_player, $targer_player)
	{
		$log = array();
		
		$shield = 1;
		$avoidance = 2;
		$critic = 3;
		
		$log[0] = $attack_player->hp;
		$log[1] = rand($attack_player->dmg_min, $attack_player->dmg_max);
		$log[2] = 1;
		
		if($log[1] > (($attack_player->dmg_min + $attack_player->dmg_max) / 2))
		{
			$log[2] = $critic;
		}
		
		if($attack_player->class != 2)
		{
			if($targer_player->class == 1)
			{
				if(rand(1, 50) <= $targer_player->shield['dmg_min'])
				{
					$log[1] = 0;
					$log[2] = $shield;
				}
				else
				{
					if($attack_player->lvl == $targer_player->lvl)
					{
						$armor_neg = round($targer_player->armor / $attack_player->lvl);
						if($armor_neg > 50)
							$armor_neg = 50;
						
						$log[1] -= $log[1] * ($armor_neg / 100);
					}
				}
			}
			else if($targer_player->class == 3)
			{
				if(rand(1, 2) == 1)
				{
					$log[1] = 0;
					$log[2] = $avoidance;
				}
				else
				{
					if($attack_player->lvl == $targer_player->lvl)
					{
						$armor_neg = round($targer_player->armor / $attack_player->lvl);
						if($armor_neg > 25)
							$armor_neg = 25;
						
						$log[1] -= $log[1] * ($armor_neg / 100);
					}
				}
			}
			else if($targer_player->class == 2)
			{
				if($attack_player->lvl == $targer_player->lvl)
				{
					if($targer_player->armor != 0)
					{
						$armor_neg = round($targer_player->armor / $attack_player->lvl);
						if($armor_neg > 10)
							$armor_neg = 10;
							
						$log[1] -= $log[1] * ($armor_neg / 100);
					}
				}
			}
		}
		return $log;
	}
	
	public function load_view($player1, $player2)
	{
		$ret = 
			";" . $player1->nick ."/". $player1->lvl."/". $player1->race."/". $player1->gender."/". $player1->class."/". $player1->face[1]."/". $player1->face[2]."/". $player1->face[3]."/". $player1->face[4]."/". $player1->face[5]."/". $player1->face[6]."/". $player1->face[7]."/". $player1->face[8]."/". $player1->face[9]."/". "1"."/".
			$player2->nick."/". $player2->lvl."/". $player2->race."/". $player2->gender."/". $player2->class."/". $player2->face[1]."/". $player2->face[2]."/". $player2->face[3]."/". $player2->face[4]."/". $player2->face[5]."/". $player2->face[6]."/". $player2->face[7]."/". $player2->face[8]."/". $player2->face[9]. "/" . "1".
			";" . $player1->weapon['has_weapon']."/". $player1->weapon['item_id']."/". $player1->weapon['dmg_min']."/". $player1->weapon['dmg_max']."/". $player1->weapon['attr_type_1']."/". $player1->weapon['attr_type_2']."/". $player1->weapon['attr_type_3']."/". $player1->weapon['attr_val_1']."/". $player1->weapon['attr_val_2']."/". $player1->weapon['attr_val_3']."/". $player1->weapon['gold']."/". $player1->weapon['mush']."/".
			$player2->weapon['has_weapon']."/". $player2->weapon['item_id']."/". $player2->weapon['dmg_min']."/". $player2->weapon['dmg_max']."/". $player2->weapon['attr_type_1']."/". $player2->weapon['attr_type_2']."/". $player2->weapon['attr_type_3']."/". $player2->weapon['attr_val_1']."/". $player2->weapon['attr_val_2']."/". $player2->weapon['attr_val_3']."/". $player2->weapon['gold']."/". $player2->weapon['mush'].
			";" . $player1->shield['has_shield']."/". $player1->shield['item_id']."/". $player1->shield['dmg_min']."/". $player1->shield['dmg_max']."/". $player1->shield['attr_type_1']."/". $player1->shield['attr_type_2']."/". $player1->shield['attr_type_3']."/". $player1->shield['attr_val_1']."/". $player1->shield['attr_val_2']."/". $player1->shield['attr_val_3']."/". $player1->shield['gold']."/". $player1->shield['mush']."/".
			$player2->shield['has_shield']."/". $player2->shield['item_id']."/". $player2->shield['dmg_min']."/". $player2->shield['dmg_max']."/". $player2->shield['attr_type_1']."/". $player2->shield['attr_type_2']."/". $player2->shield['attr_type_3']."/". $player2->shield['attr_val_1']."/". $player2->shield['attr_val_2']."/". $player2->shield['attr_val_3']."/". $player2->shield['gold']."/". $player2->shield['mush'];
		
		return $ret;
	}
}
?>