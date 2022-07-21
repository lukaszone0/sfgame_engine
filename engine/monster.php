<?php
class Monster
{
	public $nick = "monster";
	public $lvl;
	public $race = 0;
	public $gender = 0;
	public $face = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	public $dmg_min = 1;
	public $dmg_max = 2;
	public $exp = 0;
	public $gold = 0;
	public $weapon = array(
			"has_weapon" => "-1",
			"item_type" => "1",
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
	//6 skala / 5 udko / 4 patyk / 3 blob / 2 wiatr / 1 szarpniecie
		
	public $shield = array(
			"has_shield" => "0",
			"item_type" => "2",
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
	public $hp = 0;
	public $attr_str;
	public $attr_agi;
	public $attr_int;
	public $attr_wit;
	public $attr_luck;
	public $armor = 0;
	public $class;
	public $bonus_reaction = 0;
	public $bonus_crit = 0;
	
	public function load_monster_quest($player)
	{
		$this->class = rand(1, 3);
		
		switch($player->data['quest_location_' . $player->data['status_extra']])
		{
			case 1:
				$face = array(1,2,3,9,10,11,12,17,21,29,34,35,36,56,57,58,61,63,64,65);
			break;
			case 2:
				$face = array(1,2,3,4,5,6,21,22,23,26,28,45,46,47,69,86,88,103,104,118,119,136,137);
			break;
			case 3:
				$face = array(18,48,49,50,53,54,55,60,62,67,71,72,73,92,93,94,95,96,97,130);
			break;
			case 4:
				$face = array(18,24,25,60,62,66,80,120,121,125);
			break;
			case 5:
				$face = array(8,19,20,24,25,38,39,41,43,59,76);
			break;
			case 6:
				$face = array(18,24,25,52,66,82);
			break;
			case 7:
				$face = array(1,2,3,5,6,9,10,11,12,17,21,28,34,35,36,56,57,58,63,64,65,105,110,111,126,127,150,153);
			break;
			case 8:
				$face = array(45,46,47,93,94,95,138,140,141,142);
			break;
			case 9:
				$face = array(7,18,52,66,82);
			break;
			case 10:
				$face = array(1,2,3,5,6,9,10,11,12,29,30,70,89,100,128);
			break;
			case 11:
				$face = array(1,2,3,5,6,9,10,11,12,105,112);
			break;
			case 12:
				$face = array(51,52,79,99,103,104,154,155,156,157);
			break;
			case 13:
				$face = array(40,42,44,48,49,50,53,54,55,68,69,71,72,73,90,91,132);
			break;
			case 14:
				$face = array(7,52,66,82,102,148,149);
			break;
			case 15:
				$face = array(125, 112);
			break;
			case 16:
				$face = array(13,14,15,16,24,25,26,27,28,39,43);
			break;
			case 17:
				$face = array(8,13,20,24,25,27,38,39,43,59,76,146,147);
			break;
			case 18:
				$face = array(1,2,3,5,6,14,15,70,74,84,101,142,144,145);
			break;
			case 19:
				$face = array(14,15,26,28,39,40,42,122,123,126,127);
			break;
			case 20:
				$face = array(24,25,26,27,28,37,39,43,61,71,72,73);
			break;
			case 21:
				$face = array(1,2,3,5,6,9,10,11,12,28,31,32,33,56,57,58,74,83,84,85,101,112,113,114,115,116,117,129,143,144,145);
			break;
			case 22:
				$face = array(7,102,148,149);
			break;
			case 23:
				$face = array(18);
			break;
		}
		
		$this->face[1] = $face[rand(0, count($face) - 1)];
		
		if($this->face[1] == 114 OR $this->face[1] == 113 OR $this->face[1] == 74 OR $this->face[1] == 70 OR $this->face[1] > 154 AND $this->face[1] < 158)
		{
			$this->class = 2;
		}
		else
		{
			$this->class = [1,3][rand(0,1)];
		}
		
		switch($this->class)
		{
			case 1:
				$this->attr_str = rand($player->attr_str / 2, $player->attr_str / 5);
				$this->attr_agi = rand($player->attr_agi / 5, $player->attr_agi / 10);
				$this->attr_int = rand($player->attr_int / 5, $player->attr_int / 10);
				$this->attr_wit = rand($player->attr_wit / 2, $player->attr_wit / 5);
				$this->attr_luck = rand($player->attr_luck / 5, $player->attr_luck / 10);
				$this->dmg_min += $player->weapon['dmg_min'] * (1 + $player->attr_str / 10);
				$this->dmg_max += $player->weapon['dmg_max'] * (1 + $player->attr_str / 10);
			break;
			case 2:
				$this->attr_str = rand($player->attr_str / 5, $player->attr_str / 10);
				$this->attr_agi = rand($player->attr_agi / 2, $player->attr_agi / 5);
				$this->attr_int = rand($player->attr_int / 5, $player->attr_int / 10);
				$this->attr_wit = rand($player->attr_wit / 2, $player->attr_wit / 10);
				$this->attr_luck = rand($player->attr_luck / 2, $player->attr_luck / 5);
				$this->dmg_min += $player->weapon['dmg_min'] * (1 + $player->attr_int / 10);
				$this->dmg_max += $player->weapon['dmg_max'] * (1 + $player->attr_int / 10);
			break;
			case 3:
				$this->attr_str = rand($player->attr_str / 5, $player->attr_str / 10);
				$this->attr_agi = rand($player->attr_agi / 5, $player->attr_agi / 10);
				$this->attr_int = rand($player->attr_int / 2, $player->attr_int / 5);
				$this->attr_wit = rand($player->attr_wit / 5, $player->attr_wit / 10);
				$this->attr_luck = rand($player->attr_luck / 2, $player->attr_luck / 10);
				$this->dmg_min += $player->weapon['dmg_min'] * (1 + $player->attr_agi / 10);
				$this->dmg_max += $player->weapon['dmg_max'] * (1 + $player->attr_agi / 10);
			break;
		}
		
		switch($this->face[1])
		{
			case 20:
			case 21:
			case 22:
			case 23:
				$this->weapon['has_weapon'] = "-4";
			break;
			case 37:
			case 38:
				$this->weapon['has_weapon'] = "-5";
			break;
			case 39:
			case 40:
			case 41:
			case 42:
			case 43:
			case 44:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "9";
			break;
			case 48:
			case 49:
			case 50:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "20";
			break;
			case 51:
			case 52:
				$this->weapon['has_weapon'] = "-6";
			break;
			case 53:
			case 54:
			case 55:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "10";
			break;
			case 68:
			case 69:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "9";
			break;
			case 71:
			case 72:
			case 73:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "8";
				$this->shield['has_shield'] = 2;
				$this->shield['item_id'] = 5;
				$this->shield['dmg_min'] = 25;
			break;
			case 79:
			case 80:
			case 81:
			case 82:
				$this->weapon['has_weapon'] = "-3";
			break;
			case 90:
			case 91:
			case 92:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "17";
			break;
			case 93:
			case 94:
			case 95:
				$this->weapon['has_shield'] = "-1";
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "9";
			break;
			case 101:
			case 115:
			case 116:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "6";
			break;
			case 102:
				$this->weapon['has_weapon'] = "-2";
			break;
			case 113:
			case 114:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "1004";
			break;
			case 117:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "26";
			break;
			case 124:
			case 125:
				$this->weapon['has_weapon'] = "-2";
			break;
			case 126:
				$this->weapon['has_weapon'] = "-3";
			break;
			case 127:
			case 128:
				$this->weapon['has_weapon'] = "-4";
			break;
			case 138:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "3";
				$this->weapon['item_id'] = "2004";
			break;
			case 140:
			case 141:
			case 142:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "12";
			break;
			case 150:
			case 151:
			case 152:
			case 153:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "15";
			break;
			case 159:
				$this->weapon['has_weapon'] = "1";
				$this->weapon['item_type'] = "1";
				$this->weapon['item_id'] = "13";
			break;
		}
		
		$this->lvl = $player->lvl;
		
		$k = [5,2,4][$this->class - 1];
		$this->hp = $this->attr_wit * $k * ($player->lvl + 1);
		$this->bonus_reaction = $player->bonus_reaction;
		
	}
	
	public function load_monster_dung_guild($dung, $monster)
	{	//TODO guild dung ;_;
		switch($dung)
		{
			case 1:
				switch($monster)
				{
					case 1:
						$this->face[1] = 74;
					case 2:
						$this->face[1] = 24;
					case 3:
						$this->face[1] = 74;
					case 4:
						$this->face[1] = 24;
					case 5:
						$this->face[1] = 74;
					case 6:
						$this->face[1] = 24;
					case 7:
						$this->face[1] = 74;
					case 8:
						$this->lvl = 10;
						$this->class = 2;
						$this->attr_int = 1665;
						$this->attr_wit = 3985;
						$this->dmg_min = 13;
						$this->dmg_max = 39;
						$this->hp = 3212;
						$this->face[1] = 24;
						$this->weapon["has_weapon"] = "-1";
					break;
					case 9:
						$this->lvl = 16;
						$this->class = 3;
						$this->attr_agi = 185;
						$this->attr_wit = 374;
						$this->dmg_min = 14;
						$this->dmg_max = 42;
						$this->hp = 25364;
						$this->face[1] = 74;
					break;
					case 10:
						$this->lvl = 26;
						$this->class = 1;
						$this->attr_str = 341;
						$this->attr_wit = 742;
						$this->dmg_min = 21;
						$this->dmg_max = 63;
						$this->hp = 100035;
						$this->face[1] = 74;
					break;
					case 11:
						$this->lvl = 36;
						$this->class = 3;
						$this->attr_agi = 571;
						$this->attr_wit = 1639;
						$this->dmg_min = 39;
						$this->dmg_max = 117;
						$this->hp = 241832;
						$this->face[1] = 74;
					break;
					case 12:
					break;
					case 13:
					break;
					case 14:
					break;
				}
			break;
			case 2:
				
			break;
		}
	}
	
	public function load_monster_tower($player)
	{
		switch($player->data['tower_level'])
		{
			case 1:
				$this->lvl = 200;
				$this->class = 1;
				$this->attr_str = 4194;
				$this->attr_agi = 1697;
				$this->attr_int = 1665;
				$this->attr_wit = 3985;
				$this->attr_luck = 2589;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 16019700;
				$this->armor = 15087;
				$this->face[1] = 400;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
				$this->shield["has_shield"] = 0;
			break;
			case 2:
				$this->lvl = 202;
				$this->class = 2;
				$this->attr_str = 1714;
				$this->attr_agi = 1682;
				$this->attr_int = 4240;
				$this->attr_wit = 4034;
				$this->attr_luck = 2621;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 13102432;
				$this->armor = 15087;
				$this->face[1] = 401;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 3:
				$this->lvl = 204;
				$this->class = 3;
				$this->attr_str = 1733;
				$this->attr_agi = 4288;
				$this->attr_int = 1696;
				$this->attr_wit = 4081;
				$this->attr_luck = 2655;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 40157040;
				$this->armor = 15087;
				$this->face[1] = 402;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 4:
				$this->lvl = 206;
				$this->class = 1;
				$this->attr_str = 4340;
				$this->attr_agi = 1746;
				$this->attr_int = 1715;
				$this->attr_wit = 4128;
				$this->attr_luck = 2690;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 68359680;
				$this->armor = 15087;
				$this->face[1] = 403;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 5:
				$this->lvl = 208;
				$this->class = 1;
				$this->attr_str = 4385;
				$this->attr_agi = 1763;
				$this->attr_int = 1733;
				$this->attr_wit = 4178;
				$this->attr_luck = 2726;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 87320200;
				$this->armor = 15087;
				$this->face[1] = 404;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 6:
				$this->lvl = 210;
				$this->class = 2;
				$this->attr_str = 1780;
				$this->attr_agi = 1750;
				$this->attr_int = 4434;
				$this->attr_wit = 4228;
				$this->attr_luck = 2756;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 42821184;
				$this->armor = 15087;
				$this->face[1] = 405;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 7:
				$this->lvl = 212;
				$this->class = 1;
				$this->attr_str = 4482;
				$this->attr_agi = 1794;
				$this->attr_int = 1766;
				$this->attr_wit = 4275;
				$this->attr_luck = 2790;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 127480500;
				$this->armor = 15087;
				$this->face[1] = 406;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 8:
				$this->lvl = 214;
				$this->class = 2;
				$this->attr_str = 1811;
				$this->attr_agi = 1784;
				$this->attr_int = 4532;
				$this->attr_wit = 4321;
				$this->attr_luck = 2826;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 59456960;
				$this->armor = 15087;
				$this->face[1] = 407;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 9:
				$this->lvl = 216;
				$this->class = 2;
				$this->attr_str = 1828;
				$this->attr_agi = 1804;
				$this->attr_int = 4577;
				$this->attr_wit = 4370;
				$this->attr_luck = 2857;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 68276880;
				$this->armor = 15087;
				$this->face[1] = 408;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 10:
				$this->lvl = 218;
				$this->class = 1;
				$this->attr_str = 4627;
				$this->attr_agi = 1847;
				$this->attr_int = 1818;
				$this->attr_wit = 4420;
				$this->attr_luck = 2891;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 193596000;
				$this->armor = 15087;
				$this->face[1] = 409;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 11:
				$this->lvl = 220;
				$this->class = 2;
				$this->attr_str = 1864;
				$this->attr_agi = 1835;
				$this->attr_int = 4678;
				$this->attr_wit = 4463;
				$this->attr_luck = 2925;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 86796424;
				$this->armor = 15087;
				$this->face[1] = 410;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 12:
				$this->lvl = 222;
				$this->class = 2;
				$this->attr_str = 1877;
				$this->attr_agi = 1853;
				$this->attr_int = 4721;
				$this->attr_wit = 4513;
				$this->attr_luck = 2960;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 96614304;
				$this->armor = 15087;
				$this->face[1] = 411;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 13:
				$this->lvl = 224;
				$this->class = 1;
				$this->attr_str = 4771;
				$this->attr_agi = 1898;
				$this->attr_int = 1869;
				$this->attr_wit = 4561;
				$this->attr_luck = 2991;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 266818500;
				$this->armor = 15087;
				$this->face[1] = 412;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 14:
				$this->lvl = 226;
				$this->class = 1;
				$this->attr_str = 4820;
				$this->attr_agi = 1909;
				$this->attr_int = 1887;
				$this->attr_wit = 4610;
				$this->attr_luck = 3027;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 293011600;
				$this->armor = 15087;
				$this->face[1] = 413;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 15:
				$this->lvl = 228;
				$this->class = 1;
				$this->attr_str = 4870;
				$this->attr_agi = 1928;
				$this->attr_int = 1907;
				$this->attr_wit = 4655;
				$this->attr_luck = 3060;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 319798500;
				$this->armor = 15087;
				$this->face[1] = 414;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 16:
				$this->lvl = 230;
				$this->class = 2;
				$this->attr_str = 1943;
				$this->attr_agi = 1925;
				$this->attr_int = 4914;
				$this->attr_wit = 4705;
				$this->attr_luck = 3092;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 139117440;
				$this->armor = 15087;
				$this->face[1] = 415;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 17:
				$this->lvl = 232;
				$this->class = 1;
				$this->attr_str = 4964;
				$this->attr_agi = 1962;
				$this->attr_int = 1940;
				$this->attr_wit = 4755;
				$this->attr_luck = 3126;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 376691100;
				$this->armor = 15087;
				$this->face[1] = 416;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 18:
				$this->lvl = 234;
				$this->class = 3;
				$this->attr_str = 1976;
				$this->attr_agi = 5013;
				$this->attr_int = 1958;
				$this->attr_wit = 4800;
				$this->attr_luck = 3163;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 324864000;
				$this->armor = 15087;
				$this->face[1] = 417;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 19:
				$this->lvl = 236;
				$this->class = 1;
				$this->attr_str = 5059;
				$this->attr_agi = 1993;
				$this->attr_int = 1975;
				$this->attr_wit = 4848;
				$this->attr_luck = 3198;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 436610880;
				$this->armor = 15087;
				$this->face[1] = 418;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 20:
				$this->lvl = 238;
				$this->class = 3;
				$this->attr_str = 2010;
				$this->attr_agi = 5107;
				$this->attr_int = 1992;
				$this->attr_wit = 4897;
				$this->attr_luck = 3228;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 374522560;
				$this->armor = 15087;
				$this->face[1] = 419;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 21:
				$this->lvl = 240;
				$this->class = 1;
				$this->attr_str = 5157;
				$this->attr_agi = 2024;
				$this->attr_int = 2009;
				$this->attr_wit = 4945;
				$this->attr_luck = 3262;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 238349000;
				$this->armor = 15087;
				$this->face[1] = 420;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 22:
				$this->lvl = 242;
				$this->class = 1;
				$this->attr_str = 5206;
				$this->attr_agi = 2043;
				$this->attr_int = 2028;
				$this->attr_wit = 4990;
				$this->attr_luck = 3295;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 242514000;
				$this->armor = 15087;
				$this->face[1] = 421;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 23:
				$this->lvl = 244;
				$this->class = 1;
				$this->attr_str = 5252;
				$this->attr_agi = 2058;
				$this->attr_int = 2042;
				$this->attr_wit = 5040;
				$this->attr_luck = 3330;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 246960000;
				$this->armor = 15087;
				$this->face[1] = 422;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 24:
				$this->lvl = 246;
				$this->class = 1;
				$this->attr_str = 5302;
				$this->attr_agi = 2077;
				$this->attr_int = 2061;
				$this->attr_wit = 5090;
				$this->attr_luck = 3362;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 251446000;
				$this->armor = 15087;
				$this->face[1] = 423;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 25:
				$this->lvl = 248;
				$this->class = 2;
				$this->attr_str = 2092;
				$this->attr_agi = 2078;
				$this->attr_int = 5352;
				$this->attr_wit = 5135;
				$this->attr_luck = 3397;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 102289200;
				$this->armor = 15087;
				$this->face[1] = 424;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 26:
				$this->lvl = 250;
				$this->class = 3;
				$this->attr_str = 2108;
				$this->attr_agi = 5400;
				$this->attr_int = 2094;
				$this->attr_wit = 5183;
				$this->attr_luck = 3429;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 208149280;
				$this->armor = 15087;
				$this->face[1] = 425;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 27:
				$this->lvl = 252;
				$this->class = 3;
				$this->attr_str = 2141;
				$this->attr_agi = 5446;
				$this->attr_int = 2129;
				$this->attr_wit = 5237;
				$this->attr_luck = 3475;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 211993760;
				$this->armor = 15087;
				$this->face[1] = 426;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 28:
				$this->lvl = 254;
				$this->class = 1;
				$this->attr_str = 5498;
				$this->attr_agi = 2173;
				$this->attr_int = 2162;
				$this->attr_wit = 5290;
				$this->attr_luck = 3522;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 269790000;
				$this->armor = 15087;
				$this->face[1] = 427;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 29:
				$this->lvl = 256;
				$this->class = 1;
				$this->attr_str = 5549;
				$this->attr_agi = 2207;
				$this->attr_int = 2198;
				$this->attr_wit = 5339;
				$this->attr_luck = 3567;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 274424600;
				$this->armor = 15087;
				$this->face[1] = 428;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 30:
				$this->lvl = 258;
				$this->class = 1;
				$this->attr_str = 5596;
				$this->attr_agi = 2241;
				$this->attr_int = 2228;
				$this->attr_wit = 5393;
				$this->attr_luck = 3613;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 279357400;
				$this->armor = 15087;
				$this->face[1] = 429;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 31:
				$this->lvl = 260;
				$this->class = 1;
				$this->attr_str = 5646;
				$this->attr_agi = 2275;
				$this->attr_int = 2263;
				$this->attr_wit = 5448;
				$this->attr_luck = 3657;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 284385600;
				$this->armor = 15087;
				$this->face[1] = 430;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 32:
				$this->lvl = 262;
				$this->class = 2;
				$this->attr_str = 2309;
				$this->attr_agi = 2296;
				$this->attr_int = 5698;
				$this->attr_wit = 5497;
				$this->attr_luck = 3703;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 115656880;
				$this->armor = 15087;
				$this->face[1] = 431;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 33:
				$this->lvl = 264;
				$this->class = 1;
				$this->attr_str = 5744;
				$this->attr_agi = 2340;
				$this->attr_int = 2331;
				$this->attr_wit = 5551;
				$this->attr_luck = 3751;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 294203000;
				$this->armor = 15087;
				$this->face[1] = 432;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 34:
				$this->lvl = 266;
				$this->class = 1;
				$this->attr_str = 5796;
				$this->attr_agi = 2377;
				$this->attr_int = 2362;
				$this->attr_wit = 5603;
				$this->attr_luck = 3794;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 299200200;
				$this->armor = 15087;
				$this->face[1] = 433;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 35:
				$this->lvl = 268;
				$this->class = 1;
				$this->attr_str = 5846;
				$this->attr_agi = 2405;
				$this->attr_int = 2396;
				$this->attr_wit = 5657;
				$this->attr_luck = 3840;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 304346600;
				$this->armor = 15087;
				$this->face[1] = 434;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 36:
				$this->lvl = 270;
				$this->class = 3;
				$this->attr_str = 2438;
				$this->attr_agi = 5895;
				$this->attr_int = 2429;
				$this->attr_wit = 5708;
				$this->attr_luck = 3886;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 247498880;
				$this->armor = 15087;
				$this->face[1] = 435;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 37:
				$this->lvl = 272;
				$this->class = 3;
				$this->attr_str = 2473;
				$this->attr_agi = 5947;
				$this->attr_int = 2464;
				$this->attr_wit = 5763;
				$this->attr_luck = 3930;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 251727840;
				$this->armor = 15087;
				$this->face[1] = 436;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 38:
				$this->lvl = 274;
				$this->class = 1;
				$this->attr_str = 5995;
				$this->attr_agi = 2507;
				$this->attr_int = 2498;
				$this->attr_wit = 5816;
				$this->attr_luck = 3975;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 319880000;
				$this->armor = 15087;
				$this->face[1] = 437;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 39:
				$this->lvl = 276;
				$this->class = 1;
				$this->attr_str = 6046;
				$this->attr_agi = 2538;
				$this->attr_int = 2531;
				$this->attr_wit = 5863;
				$this->attr_luck = 4022;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 324810200;
				$this->armor = 15087;
				$this->face[1] = 438;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 40:
				$this->lvl = 278;
				$this->class = 1;
				$this->attr_str = 6092;
				$this->attr_agi = 2572;
				$this->attr_int = 2566;
				$this->attr_wit = 5917;
				$this->attr_luck = 4069;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 330168600;
				$this->armor = 15087;
				$this->face[1] = 439;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 41:
				$this->lvl = 280;
				$this->class = 2;
				$this->attr_str = 2606;
				$this->attr_agi = 2600;
				$this->attr_int = 6144;
				$this->attr_wit = 5972;
				$this->attr_luck = 4112;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 134250560;
				$this->armor = 15087;
				$this->face[1] = 440;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 42:
				$this->lvl = 282;
				$this->class = 2;
				$this->attr_str = 2641;
				$this->attr_agi = 2631;
				$this->attr_int = 6194;
				$this->attr_wit = 6021;
				$this->attr_luck = 4158;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 136315440;
				$this->armor = 15087;
				$this->face[1] = 441;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 43:
				$this->lvl = 284;
				$this->class = 1;
				$this->attr_str = 6245;
				$this->attr_agi = 2671;
				$this->attr_int = 2668;
				$this->attr_wit = 6073;
				$this->attr_luck = 4203;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 346161000;
				$this->armor = 15087;
				$this->face[1] = 442;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 44:
				$this->lvl = 286;
				$this->class = 3;
				$this->attr_str = 2705;
				$this->attr_agi = 6295;
				$this->attr_int = 2699;
				$this->attr_wit = 6130;
				$this->attr_luck = 4248;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 281489600;
				$this->armor = 15087;
				$this->face[1] = 443;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 45:
				$this->lvl = 288;
				$this->class = 3;
				$this->attr_str = 2741;
				$this->attr_agi = 6342;
				$this->attr_int = 2733;
				$this->attr_wit = 6178;
				$this->attr_luck = 4295;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 285670720;
				$this->armor = 15087;
				$this->face[1] = 444;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 46:
				$this->lvl = 290;
				$this->class = 1;
				$this->attr_str = 6396;
				$this->attr_agi = 2771;
				$this->attr_int = 2765;
				$this->attr_wit = 6231;
				$this->attr_luck = 4340;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 362644200;
				$this->armor = 15087;
				$this->face[1] = 445;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 47:
				$this->lvl = 292;
				$this->class = 3;
				$this->attr_str = 2804;
				$this->attr_agi = 6444;
				$this->attr_int = 2798;
				$this->attr_wit = 6284;
				$this->attr_luck = 4385;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 294593920;
				$this->armor = 15087;
				$this->face[1] = 446;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 48:
				$this->lvl = 294;
				$this->class = 1;
				$this->attr_str = 2838;
				$this->attr_agi = 2834;
				$this->attr_int = 6492;
				$this->attr_wit = 6340;
				$this->attr_luck = 4430;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 149624000;
				$this->armor = 15087;
				$this->face[1] = 447;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 49:
				$this->lvl = 296;
				$this->class = 2;
				$this->attr_str = 2874;
				$this->attr_agi = 2866;
				$this->attr_int = 6542;
				$this->attr_wit = 6388;
				$this->attr_luck = 4477;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 151778880;
				$this->armor = 15087;
				$this->face[1] = 448;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 50:
				$this->lvl = 298;
				$this->class = 1;
				$this->attr_str = 6593;
				$this->attr_agi = 2905;
				$this->attr_int = 2902;
				$this->attr_wit = 6441;
				$this->attr_luck = 4523;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 385171800;
				$this->armor = 15087;
				$this->face[1] = 449;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 51:
				$this->lvl = 300;
				$this->class = 1;
				$this->attr_str = 6640;
				$this->attr_agi = 2937;
				$this->attr_int = 2932;
				$this->attr_wit = 6494;
				$this->attr_luck = 4569;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 390938800;
				$this->armor = 15087;
				$this->face[1] = 450;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 52:
				$this->lvl = 302;
				$this->class = 1;
				$this->attr_str = 2977;
				$this->attr_agi = 6707;
				$this->attr_int = 2973;
				$this->attr_wit = 6555;
				$this->attr_luck = 4612;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 317786040;
				$this->armor = 15087;
				$this->face[1] = 451;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 53:
				$this->lvl = 304;
				$this->class = 3;
				$this->attr_str = 6776;
				$this->attr_agi = 3010;
				$this->attr_int = 3013;
				$this->attr_wit = 6616;
				$this->attr_luck = 4654;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 403576000;
				$this->armor = 15087;
				$this->face[1] = 452;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 54:
				$this->lvl = 306;
				$this->class = 2;
				$this->attr_str = 3047;
				$this->attr_agi = 3052;
				$this->attr_int = 6839;
				$this->attr_wit = 6679;
				$this->attr_luck = 4699;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 164036024;
				$this->armor = 15087;
				$this->face[1] = 453;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 55:
				$this->lvl = 308;
				$this->class = 1;
				$this->attr_str = 6906;
				$this->attr_agi = 3089;
				$this->attr_int = 3091;
				$this->attr_wit = 6741;
				$this->attr_luck = 4741;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 416593800;
				$this->armor = 15087;
				$this->face[1] = 454;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 56:
				$this->lvl = 310;
				$this->class = 1;
				$this->attr_str = 6973;
				$this->attr_agi = 3121;
				$this->attr_int = 3132;
				$this->attr_wit = 6805;
				$this->attr_luck = 4784;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 423271000;
				$this->armor = 15087;
				$this->face[1] = 455;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 57:
				$this->lvl = 312;
				$this->class = 1;
				$this->attr_str = 7040;
				$this->attr_agi = 3160;
				$this->attr_int = 3173;
				$this->attr_wit = 6864;
				$this->attr_luck = 4828;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 429686040;
				$this->armor = 15087;
				$this->face[1] = 456;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 58:
				$this->lvl = 314;
				$this->class = 1;
				$this->attr_str = 3197;
				$this->attr_agi = 7106;
				$this->attr_int = 3211;
				$this->attr_wit = 6930;
				$this->attr_luck = 4871;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 349272000;
				$this->armor = 15087;
				$this->face[1] = 457;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 59:
				$this->lvl = 316;
				$this->class = 3;
				$this->attr_str = 3235;
				$this->attr_agi = 3249;
				$this->attr_int = 7171;
				$this->attr_wit = 6990;
				$this->attr_luck = 4915;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 177266040;
				$this->armor = 15087;
				$this->face[1] = 458;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 60:
				$this->lvl = 318;
				$this->class = 1;
				$this->attr_str = 7240;
				$this->attr_agi = 3270;
				$this->attr_int = 3291;
				$this->attr_wit = 7049;
				$this->attr_luck = 4958;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 449726200;
				$this->armor = 15087;
				$this->face[1] = 459;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 61:
				$this->lvl = 320;
				$this->class = 1;
				$this->attr_str = 7303;
				$this->attr_agi = 3309;
				$this->attr_int = 3331;
				$this->attr_wit = 7112;
				$this->attr_luck = 5005;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 456590040;
				$this->armor = 15087;
				$this->face[1] = 460;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 62:
				$this->lvl = 322;
				$this->class = 3;
				$this->attr_str = 7370;
				$this->attr_agi = 3348;
				$this->attr_int = 3368;
				$this->attr_wit = 7173;
				$this->attr_luck = 5043;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 463370580;
				$this->armor = 15087;
				$this->face[1] = 461;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 63:
				$this->lvl = 324;
				$this->class = 2;
				$this->attr_str = 7436;
				$this->attr_agi = 3382;
				$this->attr_int = 3409;
				$this->attr_wit = 7236;
				$this->attr_luck = 5088;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 470340000;
				$this->armor = 15087;
				$this->face[1] = 462;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 64:
				$this->lvl = 326;
				$this->class = 1;
				$this->attr_str = 3148;
				$this->attr_agi = 7502;
				$this->attr_int = 3447;
				$this->attr_wit = 7298;
				$this->attr_luck = 5134;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 381831036;
				$this->armor = 15087;
				$this->face[1] = 463;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 65:
				$this->lvl = 328;
				$this->class = 1;
				$this->attr_str = 7567;
				$this->attr_agi = 3458;
				$this->attr_int = 3487;
				$this->attr_wit = 7359;
				$this->attr_luck = 5177;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 484222020;
				$this->armor = 15087;
				$this->face[1] = 464;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 66:
				$this->lvl = 330;
				$this->class = 1;
				$this->attr_str = 7634;
				$this->attr_agi = 3495;
				$this->attr_int = 3528;
				$this->attr_wit = 7424;
				$this->attr_luck = 5217;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 491468080;
				$this->armor = 15087;
				$this->face[1] = 465;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 67:
				$this->lvl = 332;
				$this->class = 1;
				$this->attr_str = 3531;
				$this->attr_agi = 7701;
				$this->attr_int = 3568;
				$this->attr_wit = 7483;
				$this->attr_luck = 5265;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 398694024;
				$this->armor = 15087;
				$this->face[1] = 466;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 68:
				$this->lvl = 334;
				$this->class = 2;
				$this->attr_str = 3567;
				$this->attr_agi = 3607;
				$this->attr_int = 7767;
				$this->attr_wit = 7544;
				$this->attr_luck = 5307;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 202179020;
				$this->armor = 15087;
				$this->face[1] = 467;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 69:
				$this->lvl = 336;
				$this->class = 1;
				$this->attr_str = 7833;
				$this->attr_agi = 3609;
				$this->attr_int = 3645;
				$this->attr_wit = 7606;
				$this->attr_luck = 5347;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 512644040;
				$this->armor = 15087;
				$this->face[1] = 468;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 70:
				$this->lvl = 338;
				$this->class = 1;
				$this->attr_str = 7900;
				$this->attr_agi = 3641;
				$this->attr_int = 3687;
				$this->attr_wit = 7669;
				$this->attr_luck = 5392;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 519958200;
				$this->armor = 15087;
				$this->face[1] = 469;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 71:
				$this->lvl = 340;
				$this->class = 1;
				$this->attr_str = 7967;
				$this->attr_agi = 3680;
				$this->attr_int = 3729;
				$this->attr_wit = 7728;
				$this->attr_luck = 5436;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 527049600;
				$this->armor = 15087;
				$this->face[1] = 470;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 72:
				$this->lvl = 342;
				$this->class = 1;
				$this->attr_str = 8031;
				$this->attr_agi = 3717;
				$this->attr_int = 3764;
				$this->attr_wit = 7792;
				$this->attr_luck = 5480;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 534531200;
				$this->armor = 15087;
				$this->face[1] = 471;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 73:
				$this->lvl = 344;
				$this->class = 1;
				$this->attr_str = 3755;
				$this->attr_agi = 3804;
				$this->attr_int = 8098;
				$this->attr_wit = 7855;
				$this->attr_luck = 5523;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 216798000;
				$this->armor = 15087;
				$this->face[1] = 472;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 74:
				$this->lvl = 346;
				$this->class = 3;
				$this->attr_str = 8167;
				$this->attr_agi = 3790;
				$this->attr_int = 3845;
				$this->attr_wit = 7914;
				$this->attr_luck = 5566;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 549231600;
				$this->armor = 15087;
				$this->face[1] = 473;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 75:
				$this->lvl = 348;
				$this->class = 1;
				$this->attr_str = 8229;
				$this->attr_agi = 3829;
				$this->attr_int = 3886;
				$this->attr_wit = 7977;
				$this->attr_luck = 5611;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 556794060;
				$this->armor = 15087;
				$this->face[1] = 474;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 76:
				$this->lvl = 350;
				$this->class = 1;
				$this->attr_str = 8297;
				$this->attr_agi = 3868;
				$this->attr_int = 3923;
				$this->attr_wit = 8038;
				$this->attr_luck = 5651;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 564267600;
				$this->armor = 15087;
				$this->face[1] = 475;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 77:
				$this->lvl = 352;
				$this->class = 1;
				$this->attr_str = 8541;
				$this->attr_agi = 3976;
				$this->attr_int = 4007;
				$this->attr_wit = 8268;
				$this->attr_luck = 5767;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 583720080;
				$this->armor = 15087;
				$this->face[1] = 476;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 78:
				$this->lvl = 354;
				$this->class = 1;
				$this->attr_str = 8787;
				$this->attr_agi = 4088;
				$this->attr_int = 4093;
				$this->attr_wit = 8494;
				$this->attr_luck = 5881;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 603070400;
				$this->armor = 15087;
				$this->face[1] = 477;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 79:
				$this->lvl = 356;
				$this->class = 1;
				$this->attr_str = 4200;
				$this->attr_agi = 9030;
				$this->attr_int = 4174;
				$this->attr_wit = 8726;
				$this->attr_luck = 5993;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 498429012;
				$this->armor = 15087;
				$this->face[1] = 478;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 80:
				$this->lvl = 358;
				$this->class = 1;
				$this->attr_str = 9274;
				$this->attr_agi = 4313;
				$this->attr_int = 4256;
				$this->attr_wit = 8956;
				$this->attr_luck = 6107;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 643040080;
				$this->armor = 15087;
				$this->face[1] = 479;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 81:
				$this->lvl = 360;
				$this->class = 1;
				$this->attr_str = 9996;
				$this->attr_agi = 4642;
				$this->attr_int = 4556;
				$this->attr_wit = 9639;
				$this->attr_luck = 6534;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 695935084;
				$this->armor = 15087;
				$this->face[1] = 480;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 82:
				$this->lvl = 362;
				$this->class = 1;
				$this->attr_str = 10761;
				$this->attr_agi = 4999;
				$this->attr_int = 4877;
				$this->attr_wit = 10373;
				$this->attr_luck = 6989;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 753116008;
				$this->armor = 15087;
				$this->face[1] = 481;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 83:
				$this->lvl = 364;
				$this->class = 1;
				$this->attr_str = 11583;
				$this->attr_agi = 5380;
				$this->attr_int = 5215;
				$this->attr_wit = 11157;
				$this->attr_luck = 7466;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 814479208;
				$this->armor = 15087;
				$this->face[1] = 482;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 84:
				$this->lvl = 366;
				$this->class = 1;
				$this->attr_str = 12460;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 11994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 880414604;
				$this->armor = 15087;
				$this->face[1] = 483;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 85:
				$this->lvl = 368;
				$this->class = 3;
				$this->attr_str = 6214;
				$this->attr_agi = 5957;
				$this->attr_int = 12883;
				$this->attr_wit = 8525;
				$this->attr_luck = 11994;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 950765440;
				$this->armor = 15087;
				$this->face[1] = 484;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 86:
				$this->lvl = 370;
				$this->class = 2;
				$this->attr_str = 3567;
				$this->attr_agi = 3607;
				$this->attr_int = 7767;
				$this->attr_wit = 7544;
				$this->attr_luck = 5307;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 394981404;
				$this->armor = 15087;
				$this->face[1] = 485;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 87:
				$this->lvl = 372;
				$this->class = 1;
				$this->attr_str = 12410;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 11994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 451234301;
				$this->armor = 15087;
				$this->face[1] = 486;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 88:
				$this->lvl = 374;
				$this->class = 1;
				$this->attr_str = 12820;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 11994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 574321203;
				$this->armor = 15087;
				$this->face[1] = 487;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 89:
				$this->lvl = 376;
				$this->class = 1;
				$this->attr_str = 13230;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 12394;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 751237085;
				$this->armor = 15087;
				$this->face[1] = 488;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 90:
				$this->lvl = 378;
				$this->class = 1;
				$this->attr_str = 14360;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 12994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 951264102;
				$this->armor = 15087;
				$this->face[1] = 489;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 91:
				$this->lvl = 380;
				$this->class = 1;
				$this->attr_str = 14440;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 13594;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 632341102;
				$this->armor = 15087;
				$this->face[1] = 490;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 92:
				$this->lvl = 382;
				$this->class = 1;
				$this->attr_str = 14830;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 13994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 844123054;
				$this->armor = 15087;
				$this->face[1] = 491;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 93:
				$this->lvl = 384;
				$this->class = 1;
				$this->attr_str = 15020;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 14994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 965432103;
				$this->armor = 15087;
				$this->face[1] = 492;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 94:
				$this->lvl = 386;
				$this->class = 1;
				$this->attr_str = 15430;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 14394;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 805410305;
				$this->armor = 15087;
				$this->face[1] = 493;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 95:
				$this->lvl = 388;
				$this->class = 1;
				$this->attr_str = 15680;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 15994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 933123405;
				$this->armor = 15087;
				$this->face[1] = 494;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 96:
				$this->lvl = 390;
				$this->class = 1;
				$this->attr_str = 16070;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 15994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 533546201;
				$this->armor = 15087;
				$this->face[1] = 495;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 97:
				$this->lvl = 392;
				$this->class = 1;
				$this->attr_str = 16210;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 16494;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 654321630;
				$this->armor = 15087;
				$this->face[1] = 496;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 98:
				$this->lvl = 394;
				$this->class = 1;
				$this->attr_str = 16420;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 16994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 855315670;
				$this->armor = 15087;
				$this->face[1] = 497;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 99:
				$this->lvl = 396;
				$this->class = 1;
				$this->attr_str = 17450;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 17594;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 985431230;
				$this->armor = 15087;
				$this->face[1] = 498;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
			case 100:
				$this->lvl = 398;
				$this->class = 1;
				$this->attr_str = 18000;
				$this->attr_agi = 5781;
				$this->attr_int = 5578;
				$this->attr_wit = 18994;
				$this->attr_luck = 7980;
				$this->dmg_min = 365356;
				$this->dmg_max = 548792;
				$this->hp = 1000000000;
				$this->armor = 15087;
				$this->face[1] = 499;
				$this->exp = 178017293;
				$this->weapon["has_weapon"] = "-1";
			break;
		}
	}
	
	public function load_monster_dung($dung_id, $player)
	{
		$data = array_fill(0, 14, "0");
		//($lvl, $class, $str, $agi, $int, $wit, $luck, $dmg_min, $dmg_max, $hp, $armor, $id, $exp, $weapon_id)
		switch ($dung_id)
		{
			
			case 1:
				switch ($player->data["dungeon_1"] - 1)
				{
					case 1:
						 $this->lvl = 10;
						 $this->class = 2;
						 $this->face[1] = 129;
						 $this->attr_str = 48;
						 $this->attr_agi = 52;
						 $this->attr_int = 294;
						 $this->attr_wit = 145;
						 $this->attr_luck = 470;
						 $this->dmg_min = 342;
						 $this->dmg_max = 513;
						 $this->hp = 4694;
						 $this->exp = 1287;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 2:
						 $this->lvl = 12;
						 $this->class = 1;
						 $this->face[1] = 112;
						 $this->attr_str = 320;
						 $this->attr_agi = 6;
						 $this->attr_int = 59;
						 $this->attr_wit = 101;
						 $this->attr_luck = 51;
						 $this->dmg_min = 208;
						 $this->dmg_max = 312;
						 $this->hp = 8565;
						 $this->exp = 1785;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 3:
						 $this->lvl = 14;
						 $this->class = 1;
						 $this->face[1] = 6;
						 $this->attr_str = 249;
						 $this->attr_agi = 78;
						 $this->attr_int = 69;
						 $this->attr_wit = 194;
						 $this->attr_luck = 65;
						 $this->dmg_min = 302;
						 $this->dmg_max = 445;
						 $this->hp = 9300;
						 $this->exp = 2395;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 4:
						 $this->lvl = 16;
						 $this->class = 3;
						 $this->face[1] = 84;
						 $this->attr_str = 84;
						 $this->attr_agi = 195;
						 $this->attr_int = 83;
						 $this->attr_wit = 131;
						 $this->attr_luck = 94;
						 $this->dmg_min = 554;
						 $this->dmg_max = 820;
						 $this->hp = 8908;
						 $this->exp = 3146;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 5:
						 $this->lvl = 18;
						 $this->class = 2;
						 $this->face[1] = 31;
						 $this->attr_str = 97;
						 $this->attr_agi = 99;
						 $this->attr_int = 303;
						 $this->attr_wit = 198;
						 $this->attr_luck = 137;
						 $this->dmg_min = 2097;
						 $this->dmg_max = 3130;
						 $this->hp = 9108;
						 $this->exp = 4050;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 6:
						 $this->lvl = 22;
						 $this->class = 2;
						 $this->face[1] = 74;
						 $this->attr_str = 89;
						 $this->attr_agi = 58;
						 $this->attr_int = 353;
						 $this->attr_wit = 198;
						 $this->attr_luck = 137;
						 $this->dmg_min = 2597;
						 $this->dmg_max = 3230;
						 $this->hp = 5654;
						 $this->exp = 6412;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 7:
						 $this->lvl = 26;
						 $this->class = 1;
						 $this->face[1] = 116;
						 $this->attr_str = 543;
						 $this->attr_agi = 135;
						 $this->attr_int = 222;
						 $this->attr_wit = 260;
						 $this->attr_luck = 142;
						 $this->dmg_min = 1292;
						 $this->dmg_max = 1956;
						 $this->hp = 37100;
						 $this->exp = 9631;
						 $this->weapon["item_id"] = "24";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 8:
						 $this->lvl = 30;
						 $this->class = 2;
						 $this->face[1] = 114;
						 $this->attr_str = 126;
						 $this->attr_agi = 130;
						 $this->attr_int = 690;
						 $this->attr_wit = 452;
						 $this->attr_luck = 193;
						 $this->dmg_min = 4277;
						 $this->dmg_max = 6439;
						 $this->hp = 23298;
						 $this->exp = 13952;
						 $this->weapon["item_id"] = "1004";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 9:
						 $this->lvl = 40;
						 $this->class = 1;
						 $this->face[1] = 4;
						 $this->attr_str = 614;
						 $this->attr_agi = 207;
						 $this->attr_int = 191;
						 $this->attr_wit = 445;
						 $this->attr_luck = 238;
						 $this->dmg_min = 3370;
						 $this->dmg_max = 5054;
						 $this->hp = 91225;
						 $this->exp = 30909;
						 $this->weapon["has_weapon"] = "-5";
					break;
					case 10:
						 $this->lvl = 50;
						 $this->class = 3;
						 $this->face[1] = 166;
						 $this->attr_str = 221;
						 $this->attr_agi = 847;
						 $this->attr_int = 213;
						 $this->attr_wit = 561;
						 $this->attr_luck = 292;
						 $this->dmg_min = 7284;
						 $this->dmg_max = 10884;
						 $this->hp = 114444;
						 $this->exp = 60343;
						 $this->weapon["has_weapon"] = "-1";
					break;
				}
			break;
			case 2:
				switch ($player->data["dungeon_2"] - 1)
				{
					case 1:
						 $this->lvl = 20;
						 $this->class = 3;
						 $this->face[1] = 131;
						 $this->attr_str = 101;
						 $this->attr_agi = 264;
						 $this->attr_int = 101;
						 $this->attr_wit = 174;
						 $this->attr_luck = 119;
						 $this->dmg_min = 932;
						 $this->dmg_max = 1397;
						 $this->hp = 14616;
						 $this->exp = 2124;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 2:
						 $this->lvl = 24;
						 $this->class = 1;
						 $this->face[1] = 38;
						 $this->attr_str = 317;
						 $this->attr_agi = 126;
						 $this->attr_int = 117;
						 $this->attr_wit = 238;
						 $this->attr_luck = 130;
						 $this->dmg_min = 1046;
						 $this->dmg_max = 1570;
						 $this->hp = 29750;
						 $this->exp = 7909;
						 $this->weapon["has_weapon"] = "-5";
					break;
					case 3:
						 $this->lvl = 28;
						 $this->class = 1;
						 $this->face[1] = 112;
						 $this->attr_str = 480;
						 $this->attr_agi = 350;
						 $this->attr_int = 325;
						 $this->attr_wit = 284;
						 $this->attr_luck = 152;
						 $this->dmg_min = 1531;
						 $this->dmg_max = 2297;
						 $this->hp = 45180;
						 $this->exp = 11652;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 4:
						 $this->lvl = 34;
						 $this->class = 3;
						 $this->face[1] = 86;
						 $this->attr_str = 214;
						 $this->attr_agi = 596;
						 $this->attr_int = 194;
						 $this->attr_wit = 303;
						 $this->attr_luck = 216;
						 $this->dmg_min = 3215;
						 $this->dmg_max = 4850;
						 $this->hp = 39420;
						 $this->exp = 19539;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 5:
						 $this->lvl = 38;
						 $this->class = 1;
						 $this->face[1] = 51;
						 $this->attr_str = 592;
						 $this->attr_agi = 178;
						 $this->attr_int = 162;
						 $this->attr_wit = 398;
						 $this->attr_luck = 195;
						 $this->dmg_min = 3070;
						 $this->dmg_max = 4635;
						 $this->hp = 77610;
						 $this->exp = 26652;
						 $this->weapon["has_weapon"] = "-6";
					break;
					case 6:
						 $this->lvl = 44;
						 $this->class = 2;
						 $this->face[1] = 102;
						 $this->attr_str = 191;
						 $this->attr_agi = 190;
						 $this->attr_int = 780;
						 $this->attr_wit = 411;
						 $this->attr_luck = 259;
						 $this->dmg_min = 10586;
						 $this->dmg_max = 15879;
						 $this->hp = 36990;
						 $this->exp = 40886;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 7:
						 $this->lvl = 48;
						 $this->class = 1;
						 $this->face[1] = 23;
						 $this->attr_str = 744;
						 $this->attr_agi = 243;
						 $this->attr_int = 230;
						 $this->attr_wit = 563;
						 $this->attr_luck = 246;
						 $this->dmg_min = 4901;
						 $this->dmg_max = 7314;
						 $this->hp = 137935;
						 $this->exp = 53228;
						 $this->weapon["has_weapon"] = "-4";
					break;
					case 8:
						 $this->lvl = 56;
						 $this->class = 3;
						 $this->face[1] = 67;
						 $this->attr_str = 250;
						 $this->attr_agi = 960;
						 $this->attr_int = 240;
						 $this->attr_wit = 680;
						 $this->attr_luck = 345;
						 $this->dmg_min = 9215;
						 $this->dmg_max = 13774;
						 $this->hp = 155040;
						 $this->exp = 86309;
						 $this->weapon["item_id"] = "9";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 9:
						 $this->lvl = 66;
						 $this->class = 3;
						 $this->face[1] = 92;
						 $this->attr_str = 300;
						 $this->attr_agi = 1160;
						 $this->attr_int = 290;
						 $this->attr_wit = 880;
						 $this->attr_luck = 420;
						 $this->dmg_min = 13104;
						 $this->dmg_max = 19656;
						 $this->hp = 233840;
						 $this->exp = 148282;
						 $this->weapon["item_id"] = "8";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 10:
						 $this->lvl = 70;
						 $this->class = 2;
						 $this->face[1] = 169;
						 $this->attr_str = 369;
						 $this->attr_agi = 385;
						 $this->attr_int = 1240;
						 $this->attr_wit = 960;
						 $this->attr_luck = 340;
						 $this->dmg_min = 11875;
						 $this->dmg_max = 17750;
						 $this->hp = 340800;
						 $this->exp = 181085;
						 $this->weapon["has_weapon"] = "1";
						 $this->weapon["item_id"] = "1054";
					break;
				}
			break;
			case 3:
				switch ($player->data["dungeon_3"] - 1)
				{
					case 1:
						 $this->lvl = 32;
						 $this->class = 3;
						 $this->face[1] = 28;
						 $this->attr_str = 155;
						 $this->attr_agi = 486;
						 $this->attr_int = 161;
						 $this->attr_wit = 276;
						 $this->attr_luck = 205;
						 $this->dmg_min = 2678;
						 $this->dmg_max = 4018;
						 $this->hp = 36432;
						 $this->exp = 16557;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 2:
						 $this->lvl = 36;
						 $this->class = 3;
						 $this->face[1] = 3;
						 $this->attr_str = 141;
						 $this->attr_agi = 602;
						 $this->attr_int = 149;
						 $this->attr_wit = 344;
						 $this->attr_luck = 230;
						 $this->dmg_min = 3733;
						 $this->dmg_max = 5569;
						 $this->hp = 50912;
						 $this->exp = 22893;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 3:
						 $this->lvl = 42;
						 $this->class = 3;
						 $this->face[1] = 57;
						 $this->attr_str = 205;
						 $this->attr_agi = 726;
						 $this->attr_int = 224;
						 $this->attr_wit = 403;
						 $this->attr_luck = 247;
						 $this->dmg_min = 5226;
						 $this->dmg_max = 7875;
						 $this->hp = 69316;
						 $this->exp = 35642;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 4:
						 $this->lvl = 54;
						 $this->class = 1;
						 $this->face[1] = 94;
						 $this->attr_str = 920;
						 $this->attr_agi = 265;
						 $this->attr_int = 240;
						 $this->attr_wit = 640;
						 $this->attr_luck = 260;
						 $this->dmg_min = 6789;
						 $this->dmg_max = 10230;
						 $this->hp = 176000;
						 $this->exp = 46757;
						 $this->weapon["item_id"] = "9";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 5:
						 $this->lvl = 46;
						 $this->class = 1;
						 $this->face[1] = 140;
						 $this->attr_str = 768;
						 $this->attr_agi = 215;
						 $this->attr_int = 183;
						 $this->attr_wit = 539;
						 $this->attr_luck = 249;
						 $this->dmg_min = 4824;
						 $this->dmg_max = 7235;
						 $this->hp = 126665;
						 $this->exp = 76872;
						 $this->weapon["item_id"] = "12";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 6:
						 $this->lvl = 60;
						 $this->class = 3;
						 $this->face[1] = 78;
						 $this->attr_str = 270;
						 $this->attr_agi = 1040;
						 $this->attr_int = 260;
						 $this->attr_wit = 760;
						 $this->attr_luck = 375;
						 $this->dmg_min = 10710;
						 $this->dmg_max = 16065;
						 $this->hp = 185440;
						 $this->exp = 108013;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 7:
						 $this->lvl = 64;
						 $this->class = 1;
						 $this->face[1] = 93;
						 $this->attr_str = 1120;
						 $this->attr_agi = 340;
						 $this->attr_int = 315;
						 $this->attr_wit = 840;
						 $this->attr_luck = 310;
						 $this->dmg_min = 9831;
						 $this->dmg_max = 14690;
						 $this->hp = 273000;
						 $this->exp = 133734;
						 $this->weapon["item_id"] = "9";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 8:
						 $this->lvl = 76;
						 $this->class = 3;
						 $this->face[1] = 162;
						 $this->attr_str = 350;
						 $this->attr_agi = 1360;
						 $this->attr_int = 340;
						 $this->attr_wit = 1080;
						 $this->attr_luck = 495;
						 $this->dmg_min = 17673;
						 $this->dmg_max = 26441;
						 $this->hp = 332640;
						 $this->exp = 240784;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 9:
						 $this->lvl = 86;
						 $this->class = 1;
						 $this->face[1] = 142;
						 $this->attr_str = 1560;
						 $this->attr_agi = 505;
						 $this->attr_int = 480;
						 $this->attr_wit = 1280;
						 $this->attr_luck = 420;
						 $this->dmg_min = 18212;
						 $this->dmg_max = 27475;
						 $this->hp = 556800;
						 $this->exp = 374041;
						 $this->weapon["item_id"] = "12";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 10:
						 $this->lvl = 90;
						 $this->class = 1;
						 $this->face[1] = 170;
						 $this->attr_str = 1640;
						 $this->attr_agi = 535;
						 $this->attr_int = 510;
						 $this->attr_wit = 1360;
						 $this->attr_luck = 440;
						 $this->dmg_min = 20130;
						 $this->dmg_max = 30195;
						 $this->hp = 618800;
						 $this->exp = 441608;
						 $this->weapon["item_id"] = "50";
						 $this->weapon["has_weapon"] = 1;
					break;
				}
			break;
			case 4:
				switch ($player->data["dungeon_4"] - 1)
				{
					case 1:
						 $this->lvl = 52;
						 $this->class = 3;
						 $this->face[1] = 124;
						 $this->attr_str = 230;
						 $this->attr_agi = 880;
						 $this->attr_int = 220;
						 $this->attr_wit = 601;
						 $this->attr_luck = 315;
						 $this->dmg_min = 7832;
						 $this->dmg_max = 11748;
						 $this->hp = 127412;
						 $this->exp = 68234;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 2:
						 $this->lvl = 58;
						 $this->class = 3;
						 $this->face[1] = 45;
						 $this->attr_str = 260;
						 $this->attr_agi = 1000;
						 $this->attr_int = 250;
						 $this->attr_wit = 720;
						 $this->attr_luck = 360;
						 $this->dmg_min = 9898;
						 $this->dmg_max = 14847;
						 $this->hp = 169920;
						 $this->exp = 96706;
						 $this->weapon["item_id"] = "12";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 3:
						 $this->lvl = 62;
						 $this->class = 1;
						 $this->face[1] = 94;
						 $this->attr_str = 1080;
						 $this->attr_agi = 325;
						 $this->attr_int = 300;
						 $this->attr_wit = 800;
						 $this->attr_luck = 300;
						 $this->dmg_min = 9156;
						 $this->dmg_max = 13734;
						 $this->hp = 252000;
						 $this->exp = 120287;
						 $this->weapon["item_id"] = "9";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 4:
						 $this->lvl = 68;
						 $this->class = 1;
						 $this->face[1] = 107;
						 $this->attr_str = 1200;
						 $this->attr_agi = 370;
						 $this->attr_int = 345;
						 $this->attr_wit = 920;
						 $this->attr_luck = 330;
						 $this->dmg_min = 11132;
						 $this->dmg_max = 16698;
						 $this->hp = 317400;
						 $this->exp = 163994;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 5:
						 $this->lvl = 74;
						 $this->class = 1;
						 $this->face[1] = 46;
						 $this->attr_str = 1320;
						 $this->attr_agi = 415;
						 $this->attr_int = 390;
						 $this->attr_wit = 1040;
						 $this->attr_luck = 360;
						 $this->dmg_min = 13300;
						 $this->dmg_max = 19950;
						 $this->hp = 390000;
						 $this->exp = 163994;
						 $this->weapon["item_id"] = "12";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 6:
						 $this->lvl = 82;
						 $this->class = 3;
						 $this->face[1] = 39;
						 $this->attr_str = 380;
						 $this->attr_agi = 1480;
						 $this->attr_int = 370;
						 $this->attr_wit = 1200;
						 $this->attr_luck = 540;
						 $this->dmg_min = 20711;
						 $this->dmg_max = 31141;
						 $this->hp = 398400;
						 $this->exp = 315135;
						 $this->weapon["item_id"] = "9";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 7:
						 $this->lvl = 84;
						 $this->class = 1;
						 $this->face[1] = 141;
						 $this->attr_str = 1520;
						 $this->attr_agi = 490;
						 $this->attr_int = 465;
						 $this->attr_wit = 1240;
						 $this->attr_luck = 410;
						 $this->dmg_min = 17442;
						 $this->dmg_max = 26163;
						 $this->hp = 527000;
						 $this->exp = 343618;
						 $this->weapon["item_id"] = "12";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 8:
						 $this->lvl = 102;
						 $this->class = 3;
						 $this->face[1] = 47;
						 $this->attr_str = 480;
						 $this->attr_agi = 1880;
						 $this->attr_int = 470;
						 $this->attr_wit = 1600;
						 $this->attr_luck = 690;
						 $this->dmg_min = 32697;
						 $this->dmg_max = 49140;
						 $this->hp = 659200;
						 $this->exp = 560797;
						 $this->weapon["item_id"] = "12";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 9:
						 $this->lvl = 96;
						 $this->class = 1;
						 $this->face[1] = 137;
						 $this->attr_str = 1760;
						 $this->attr_agi = 580;
						 $this->attr_int = 555;
						 $this->attr_wit = 1480;
						 $this->attr_luck = 470;
						 $this->dmg_min = 23010;
						 $this->dmg_max = 34515;
						 $this->hp = 717800;
						 $this->exp = 704509;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 10:
						 $this->lvl = 110;
						 $this->class = 3;
						 $this->face[1] = 172;
						 $this->attr_str = 520;
						 $this->attr_agi = 2040;
						 $this->attr_int = 510;
						 $this->attr_wit = 1760;
						 $this->attr_luck = 750;
						 $this->dmg_min = 38335;
						 $this->dmg_max = 57400;
						 $this->hp = 781440;
						 $this->exp = 940791;
						 $this->weapon["item_id"] = "55";
						 $this->weapon["has_weapon"] = 1;
					break;
				}
			break;
			case 5:
				switch ($player->data["dungeon_5"] - 1)
				{
					case 1:
						 $this->lvl = 72;
						 $this->class = 3;
						 $this->face[1] = 9;
						 $this->attr_str = 330;
						 $this->attr_agi = 1280;
						 $this->attr_int = 320;
						 $this->attr_wit = 1000;
						 $this->attr_luck = 465;
						 $this->dmg_min = 15738;
						 $this->dmg_max = 23607;
						 $this->hp = 292000;
						 $this->exp = 199497;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 2:
						 $this->lvl = 78;
						 $this->class = 3;
						 $this->face[1] = 150;
						 $this->attr_str = 360;
						 $this->attr_agi = 1400;
						 $this->attr_int = 350;
						 $this->attr_wit = 1120;
						 $this->attr_luck = 510;
						 $this->dmg_min = 18612;
						 $this->dmg_max = 27918;
						 $this->hp = 353920;
						 $this->exp = 263817;
						 $this->weapon["item_id"] = "21";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 3:
						 $this->lvl = 80;
						 $this->class = 3;
						 $this->face[1] = 36;
						 $this->attr_str = 370;
						 $this->attr_agi = 1440;
						 $this->attr_int = 360;
						 $this->attr_wit = 1160;
						 $this->attr_luck = 525;
						 $this->dmg_min = 19720;
						 $this->dmg_max = 29580;
						 $this->hp = 375840;
						 $this->exp = 288496;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 4:
						 $this->lvl = 88;
						 $this->class = 3;
						 $this->face[1] = 153;
						 $this->attr_str = 410;
						 $this->attr_agi = 1600;
						 $this->attr_int = 400;
						 $this->attr_wit = 1320;
						 $this->attr_luck = 585;
						 $this->dmg_min = 23989;
						 $this->dmg_max = 36064;
						 $this->hp = 469920;
						 $this->exp = 406744;
						 $this->weapon["item_id"] = "21";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 5:
						 $this->lvl = 94;
						 $this->class = 1;
						 $this->face[1] = 17;
						 $this->attr_str = 1720;
						 $this->attr_agi = 565;
						 $this->attr_int = 540;
						 $this->attr_wit = 1440;
						 $this->attr_luck = 460;
						 $this->dmg_min = 21971;
						 $this->dmg_max = 33043;
						 $this->hp = 684000;
						 $this->exp = 518518;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 6:
						 $this->lvl = 100;
						 $this->class = 3;
						 $this->face[1] = 151;
						 $this->attr_str = 470;
						 $this->attr_agi = 1840;
						 $this->attr_int = 460;
						 $this->attr_wit = 1560;
						 $this->attr_luck = 675;
						 $this->dmg_min = 31450;
						 $this->dmg_max = 47175;
						 $this->hp = 630240;
						 $this->exp = 653687;
						 $this->weapon["item_id"] = "21";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 7:
						 $this->lvl = 108;
						 $this->class = 1;
						 $this->face[1] = 161;
						 $this->attr_str = 2000;
						 $this->attr_agi = 670;
						 $this->attr_int = 645;
						 $this->attr_wit = 1720;
						 $this->attr_luck = 530;
						 $this->dmg_min = 29346;
						 $this->dmg_max = 44220;
						 $this->hp = 937400;
						 $this->exp = 876584;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 8:
						 $this->lvl = 114;
						 $this->class = 2;
						 $this->face[1] = 118;
						 $this->attr_str = 520;
						 $this->attr_agi = 540;
						 $this->attr_int = 2200;
						 $this->attr_wit = 1760;
						 $this->attr_luck = 775;
						 $this->dmg_min = 76908;
						 $this->dmg_max = 115583;
						 $this->hp = 404800;
						 $this->exp = 1081088;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 9:
						 $this->lvl = 122;
						 $this->class = 1;
						 $this->face[1] = 160;
						 $this->attr_str = 2280;
						 $this->attr_agi = 775;
						 $this->attr_int = 750;
						 $this->attr_wit = 2000;
						 $this->attr_luck = 600;
						 $this->dmg_min = 37785;
						 $this->dmg_max = 56792;
						 $this->hp = 1230000;
						 $this->exp = 1412064;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 10:
						 $this->lvl = 130;
						 $this->class = 3;
						 $this->face[1] = 171;
						 $this->attr_str = 620;
						 $this->attr_agi = 2440;
						 $this->attr_int = 610;
						 $this->attr_wit = 2160;
						 $this->attr_luck = 900;
						 $this->dmg_min = 54145;
						 $this->dmg_max = 81095;
						 $this->hp = 1131840;
						 $this->exp = 1821461;
						 $this->weapon["has_weapon"] = "1";
						 $this->weapon["item_id"] = "20";
					break;
				}
			break;
			case 6:
				switch ($player->data["dungeon_6"] - 1)
				{
					case 1:
						 $this->lvl = 92;
						 $this->class = 1;
						 $this->face[1] = 128;
						 $this->attr_str = 1680;
						 $this->attr_agi = 550;
						 $this->attr_int = 525;
						 $this->attr_wit = 1400;
						 $this->attr_luck = 450;
						 $this->dmg_min = 21125;
						 $this->dmg_max = 31603;
						 $this->hp = 651000;
						 $this->exp = 478738;
						 $this->weapon["has_weapon"] = "-4";
					break;
					case 2:
						 $this->lvl = 98;
						 $this->class = 3;
						 $this->face[1] = 86;
						 $this->attr_str = 460;
						 $this->attr_agi = 1800;
						 $this->attr_int = 450;
						 $this->attr_wit = 1520;
						 $this->attr_luck = 660;
						 $this->dmg_min = 30046;
						 $this->dmg_max = 45069;
						 $this->hp = 601920;
						 $this->exp = 605700;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 3:
						 $this->lvl = 104;
						 $this->class = 2;
						 $this->face[1] = 77;
						 $this->attr_str = 470;
						 $this->attr_agi = 490;
						 $this->attr_int = 2000;
						 $this->attr_wit = 1560;
						 $this->attr_luck = 700;
						 $this->dmg_min = 63918;
						 $this->dmg_max = 95877;
						 $this->hp = 327600;
						 $this->exp = 758451;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 4:
						 $this->lvl = 106;
						 $this->class = 3;
						 $this->face[1] = 81;
						 $this->attr_str = 500;
						 $this->attr_agi = 1960;
						 $this->attr_int = 490;
						 $this->attr_wit = 1680;
						 $this->attr_luck = 720;
						 $this->dmg_min = 35460;
						 $this->dmg_max = 53190;
						 $this->hp = 719040;
						 $this->exp = 815853;
						 $this->weapon["has_weapon"] = "-3";
					break;
					case 5:
						 $this->lvl = 118;
						 $this->class = 3;
						 $this->face[1] = 89;
						 $this->attr_str = 560;
						 $this->attr_agi = 2200;
						 $this->attr_int = 520;
						 $this->attr_wit = 1920;
						 $this->attr_luck = 810;
						 $this->dmg_min = 44200;
						 $this->dmg_max = 66300;
						 $this->hp = 913920;
						 $this->exp = 1237696;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 6:
						 $this->lvl = 124;
						 $this->class = 1;
						 $this->face[1] = 16;
						 $this->attr_str = 2320;
						 $this->attr_agi = 790;
						 $this->attr_int = 765;
						 $this->attr_wit = 2040;
						 $this->attr_luck = 610;
						 $this->dmg_min = 39144;
						 $this->dmg_max = 58716;
						 $this->hp = 1275000;
						 $this->exp = 1506706;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 7:
						 $this->lvl = 128;
						 $this->class = 3;
						 $this->face[1] = 88;
						 $this->attr_str = 610;
						 $this->attr_agi = 2400;
						 $this->attr_int = 600;
						 $this->attr_wit = 2120;
						 $this->attr_luck = 885;
						 $this->dmg_min = 52297;
						 $this->dmg_max = 78566;
						 $this->hp = 1093920;
						 $this->exp = 1710914;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 8:
						 $this->lvl = 136;
						 $this->class = 2;
						 $this->face[1] = 30;
						 $this->attr_str = 630;
						 $this->attr_agi = 650;
						 $this->attr_int = 2640;
						 $this->attr_wit = 2200;
						 $this->attr_luck = 940;
						 $this->dmg_min = 110240;
						 $this->dmg_max = 165360;
						 $this->hp = 602800;
						 $this->exp = 2187846;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 9:
						 $this->lvl = 144;
						 $this->class = 3;
						 $this->face[1] = 87;
						 $this->attr_str = 690;
						 $this->attr_agi = 2720;
						 $this->attr_int = 680;
						 $this->attr_wit = 2440;
						 $this->attr_luck = 1005;
						 $this->dmg_min = 66612;
						 $this->dmg_max = 100191;
						 $this->hp = 1415200;
						 $this->exp = 2767832;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 10:
						 $this->lvl = 150;
						 $this->class = 3;
						 $this->face[1] = 167;
						 $this->attr_str = 720;
						 $this->attr_agi = 2840;
						 $this->attr_int = 710;
						 $this->attr_wit = 2560;
						 $this->attr_luck = 1050;
						 $this->dmg_min = 72675;
						 $this->dmg_max = 108870;
						 $this->hp = 1546240;
						 $this->exp = 3280697;
						 $this->weapon["has_weapon"] = "-1";
					break;
				}
			break;
			case 7:
				switch ($player->data["dungeon_7"] - 1)
				{
					case 1:
						 $this->lvl = 112;
						 $this->class = 3;
						 $this->face[1] = 66;
						 $this->attr_str = 530;
						 $this->attr_agi = 2080;
						 $this->attr_int = 520;
						 $this->attr_wit = 1800;
						 $this->attr_luck = 765;
						 $this->dmg_min = 39710;
						 $this->dmg_max = 59565;
						 $this->hp = 813600;
						 $this->exp = 1009041;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 2:
						 $this->lvl = 116;
						 $this->class = 3;
						 $this->face[1] = 97;
						 $this->attr_str = 550;
						 $this->attr_agi = 2160;
						 $this->attr_int = 540;
						 $this->attr_wit = 1880;
						 $this->attr_luck = 795;
						 $this->dmg_min = 42749;
						 $this->dmg_max = 64015;
						 $this->hp = 879840;
						 $this->exp = 1157092;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 3:
						 $this->lvl = 120;
						 $this->class = 2;
						 $this->face[1] = 82;
						 $this->attr_str = 550;
						 $this->attr_agi = 570;
						 $this->attr_int = 2320;
						 $this->attr_wit = 1880;
						 $this->attr_luck = 820;
						 $this->dmg_min = 85511;
						 $this->dmg_max = 128150;
						 $this->hp = 454960;
						 $this->exp = 1322625;
						 $this->weapon["has_weapon"] = "-3";
					break;
					case 4:
						 $this->lvl = 126;
						 $this->class = 1;
						 $this->face[1] = 52;
						 $this->attr_str = 2360;
						 $this->attr_agi = 805;
						 $this->attr_int = 780;
						 $this->attr_wit = 2080;
						 $this->attr_luck = 620;
						 $this->dmg_min = 40527;
						 $this->dmg_max = 60909;
						 $this->hp = 1320800;
						 $this->exp = 1606255;
						 $this->weapon["has_weapon"] = "-6";
					break;
					case 5:
						 $this->lvl = 138;
						 $this->class = 1;
						 $this->face[1] = 158;
						 $this->attr_str = 2600;
						 $this->attr_agi = 895;
						 $this->attr_int = 870;
						 $this->attr_wit = 2320;
						 $this->attr_luck = 680;
						 $this->dmg_min = 48807;
						 $this->dmg_max = 73341;
						 $this->hp = 1612400;
						 $this->exp = 2059369;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 6:
						 $this->lvl = 134;
						 $this->class = 1;
						 $this->face[1] = 135;
						 $this->attr_str = 2520;
						 $this->attr_agi = 865;
						 $this->attr_int = 840;
						 $this->attr_wit = 2240;
						 $this->attr_luck = 660;
						 $this->dmg_min = 46046;
						 $this->dmg_max = 69069;
						 $this->hp = 1512000;
						 $this->exp = 2322552;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 7:
						 $this->lvl = 142;
						 $this->class = 2;
						 $this->face[1] = 102;
						 $this->attr_str = 660;
						 $this->attr_agi = 680;
						 $this->attr_int = 2760;
						 $this->attr_wit = 2320;
						 $this->attr_luck = 985;
						 $this->dmg_min = 120218;
						 $this->dmg_max = 180327;
						 $this->hp = 663520;
						 $this->exp = 2612278;
						 $this->weapon["has_weapon"] = "1";
						 $this->weapon["item_id"] = "1054";
					break;
					case 8:
						 $this->lvl = 146;
						 $this->class = 1;
						 $this->face[1] = 52;
						 $this->attr_str = 2760;
						 $this->attr_agi = 955;
						 $this->attr_int = 930;
						 $this->attr_wit = 2480;
						 $this->attr_luck = 720;
						 $this->dmg_min = 54846;
						 $this->dmg_max = 82269;
						 $this->hp = 1822800;
						 $this->exp = 2930646;
						 $this->weapon["has_weapon"] = "-6";
					break;
					case 9:
						 $this->lvl = 148;
						 $this->class = 1;
						 $this->face[1] = 149;
						 $this->attr_str = 2800;
						 $this->attr_agi = 970;
						 $this->attr_int = 945;
						 $this->attr_wit = 2520;
						 $this->attr_luck = 730;
						 $this->dmg_min = 56481;
						 $this->dmg_max = 84581;
						 $this->hp = 1877400;
						 $this->exp = 3101774;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 10:
						 $this->lvl = 170;
						 $this->class = 1;
						 $this->face[1] = 168;
						 $this->attr_str = 3240;
						 $this->attr_agi = 1135;
						 $this->attr_int = 1110;
						 $this->attr_wit = 2960;
						 $this->attr_luck = 840;
						 $this->dmg_min = 75075;
						 $this->dmg_max = 112450;
						 $this->hp = 2530800;
						 $this->exp = 5583708;
						 $this->weapon["has_weapon"] = "-6";
					break;
				}
			break;
			case 8:
				switch ($player->data["dungeon_8"] - 1)
				{
					case 1:
						 $this->lvl = 132;
						 $this->class = 1;
						 $this->face[1] = 38;
						 $this->attr_str = 2480;
						 $this->attr_agi = 850;
						 $this->attr_int = 825;
						 $this->attr_wit = 2200;
						 $this->attr_luck = 650;
						 $this->dmg_min = 44571;
						 $this->dmg_max = 66981;
						 $this->hp = 1463000;
						 $this->exp = 1937541;
						 $this->weapon["has_weapon"] = "-5";
					break;
					case 2:
						 $this->lvl = 140;
						 $this->class = 3;
						 $this->face[1] = 143;
						 $this->attr_str = 670;
						 $this->attr_agi = 2640;
						 $this->attr_int = 660;
						 $this->attr_wit = 2360;
						 $this->attr_luck = 975;
						 $this->dmg_min = 63070;
						 $this->dmg_max = 94605;
						 $this->hp = 1331040;
						 $this->exp = 2463717;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 3:
						 $this->lvl = 154;
						 $this->class = 1;
						 $this->face[1] = 147;
						 $this->attr_str = 2920;
						 $this->attr_agi = 1015;
						 $this->attr_int = 990;
						 $this->attr_wit = 2640;
						 $this->attr_luck = 760;
						 $this->dmg_min = 61237;
						 $this->dmg_max = 92002;
						 $this->hp = 2046000;
						 $this->exp = 3663979;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 4:
						 $this->lvl = 158;
						 $this->class = 3;
						 $this->face[1] = 144;
						 $this->attr_str = 760;
						 $this->attr_agi = 3000;
						 $this->attr_int = 750;
						 $this->attr_wit = 2720;
						 $this->attr_luck = 1110;
						 $this->dmg_min = 80668;
						 $this->dmg_max = 121002;
						 $this->hp = 1729920;
						 $this->exp = 4082943;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 5:
						 $this->lvl = 164;
						 $this->class = 3;
						 $this->face[1] = 99;
						 $this->attr_str = 790;
						 $this->attr_agi = 3120;
						 $this->attr_int = 780;
						 $this->attr_wit = 2840;
						 $this->attr_luck = 1155;
						 $this->dmg_min = 87014;
						 $this->dmg_max = 130834;
						 $this->hp = 1874400;
						 $this->exp = 4785109;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 6:
						 $this->lvl = 168;
						 $this->class = 2;
						 $this->face[1] = 154;
						 $this->attr_str = 790;
						 $this->attr_agi = 810;
						 $this->attr_int = 3280;
						 $this->attr_wit = 2840;
						 $this->attr_luck = 1180;
						 $this->dmg_min = 169106;
						 $this->dmg_max = 253659;
						 $this->hp = 959920;
						 $this->exp = 5306545;
						 $this->weapon["has_weapon"] = "-3";
					break;
					case 7:
						 $this->lvl = 172;
						 $this->class = 1;
						 $this->face[1] = 146;
						 $this->attr_str = 3280;
						 $this->attr_agi = 1150;
						 $this->attr_int = 1125;
						 $this->attr_wit = 3000;
						 $this->attr_luck = 850;
						 $this->dmg_min = 76657;
						 $this->dmg_max = 115150;
						 $this->hp = 2595000;
						 $this->exp = 5873522;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 8:
						 $this->lvl = 180;
						 $this->class = 3;
						 $this->face[1] = 98;
						 $this->attr_str = 870;
						 $this->attr_agi = 3340;
						 $this->attr_int = 860;
						 $this->attr_wit = 3160;
						 $this->attr_luck = 1275;
						 $this->dmg_min = 102510;
						 $this->dmg_max = 153765;
						 $this->hp = 2287840;
						 $this->exp = 7157815;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 9:
						 $this->lvl = 185;
						 $this->class = 2;
						 $this->face[1] = 156;
						 $this->attr_str = 875;
						 $this->attr_agi = 895;
						 $this->attr_int = 3620;
						 $this->attr_wit = 3180;
						 $this->attr_luck = 1305;
						 $this->dmg_min = 205458;
						 $this->dmg_max = 308187;
						 $this->hp = 1182960;
						 $this->exp = 8070081;
						 $this->weapon["has_weapon"] = "-3";
					break;
					case 10:
						 $this->lvl = 200;
						 $this->class = 2;
						 $this->face[1] = 164;
						 $this->attr_str = 950;
						 $this->attr_agi = 970;
						 $this->attr_int = 3920;
						 $this->attr_wit = 3480;
						 $this->attr_luck = 1410;
						 $this->dmg_min = 240516;
						 $this->dmg_max = 360774;
						 $this->hp = 1398960;
						 $this->exp = 11412835;
						 $this->weapon["item_id"] = "2051";
						 $this->weapon["has_weapon"] = 1;
					break;
				}
			break;
			case 9:
				switch ($player->data["dungeon_9"] - 1)
				{
					case 1:
						 $this->lvl = 152;
						 $this->class = 1;
						 $this->face[1] = 136;
						 $this->attr_str = 2880;
						 $this->attr_agi = 1000;
						 $this->attr_int = 975;
						 $this->attr_wit = 2600;
						 $this->attr_luck = 750;
						 $this->dmg_min = 59534;
						 $this->dmg_max = 89590;
						 $this->hp = 1989000;
						 $this->exp = 3467701;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 2:
						 $this->lvl = 156;
						 $this->class = 2;
						 $this->face[1] = 125;
						 $this->attr_str = 730;
						 $this->attr_agi = 750;
						 $this->attr_int = 3040;
						 $this->attr_wit = 2600;
						 $this->attr_luck = 1090;
						 $this->dmg_min = 145485;
						 $this->dmg_max = 218380;
						 $this->hp = 816400;
						 $this->exp = 3868959;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 3:
						 $this->lvl = 160;
						 $this->class = 3;
						 $this->face[1] = 99;
						 $this->attr_str = 770;
						 $this->attr_agi = 3040;
						 $this->attr_int = 760;
						 $this->attr_wit = 2760;
						 $this->attr_luck = 1125;
						 $this->dmg_min = 82960;
						 $this->dmg_max = 124440;
						 $this->hp = 1777440;
						 $this->exp = 4307201;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 4:
						 $this->lvl = 162;
						 $this->class = 1;
						 $this->face[1] = 37;
						 $this->attr_str = 3080;
						 $this->attr_agi = 1075;
						 $this->attr_int = 1050;
						 $this->attr_wit = 2800;
						 $this->attr_luck = 800;
						 $this->dmg_min = 67980;
						 $this->dmg_max = 101970;
						 $this->hp = 2282000;
						 $this->exp = 4541147;
						 $this->weapon["has_weapon"] = "-5";
					break;
					case 5:
						 $this->lvl = 166;
						 $this->class = 2;
						 $this->face[1] = 129;
						 $this->attr_str = 780;
						 $this->attr_agi = 800;
						 $this->attr_int = 3240;
						 $this->attr_wit = 2800;
						 $this->attr_luck = 1165;
						 $this->dmg_min = 164775;
						 $this->dmg_max = 247325;
						 $this->hp = 935200;
						 $this->exp = 5040468;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 6:
						 $this->lvl = 174;
						 $this->class = 3;
						 $this->face[1] = 138;
						 $this->attr_str = 1165;
						 $this->attr_agi = 3320;
						 $this->attr_int = 1140;
						 $this->attr_wit = 3040;
						 $this->attr_luck = 860;
						 $this->dmg_min = 78588;
						 $this->dmg_max = 117882;
						 $this->hp = 1560740;
						 $this->exp = 6175189;
						 $this->weapon["item_id"] = "2004";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 7:
						 $this->lvl = 176;
						 $this->class = 3;
						 $this->face[1] = 90;
						 $this->attr_str = 850;
						 $this->attr_agi = 3360;
						 $this->attr_int = 840;
						 $this->attr_wit = 3080;
						 $this->attr_luck = 1245;
						 $this->dmg_min = 100763;
						 $this->dmg_max = 150976;
						 $this->hp = 2180640;
						 $this->exp = 6489101;
						 $this->weapon["item_id"] = "8";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 8:
						 $this->lvl = 178;
						 $this->class = 3;
						 $this->face[1] = 42;
						 $this->attr_str = 860;
						 $this->attr_agi = 3400;
						 $this->attr_int = 850;
						 $this->attr_wit = 3120;
						 $this->attr_luck = 1260;
						 $this->dmg_min = 102982;
						 $this->dmg_max = 154473;
						 $this->hp = 2233920;
						 $this->exp = 6816906;
						 $this->weapon["item_id"] = "9";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 9:
						 $this->lvl = 190;
						 $this->class = 2;
						 $this->face[1] = 74;
						 $this->attr_str = 900;
						 $this->attr_agi = 920;
						 $this->attr_int = 3720;
						 $this->attr_wit = 3280;
						 $this->attr_luck = 1340;
						 $this->dmg_min = 216713;
						 $this->dmg_max = 325256;
						 $this->hp = 1252960;
						 $this->exp = 9081081;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 10:
						 $this->lvl = $player->data['lvl'];
						 $this->class = $player->data['class'];
						 $this->face = $player->face;
						 $this->attr_str = $player->attr_str;
						 $this->attr_agi = $player->attr_agi;
						 $this->attr_int = $player->attr_int;
						 $this->attr_wit = $player->attr_wit;
						 $this->attr_luck = $player->attr_luck;
						 $this->dmg_min = $player->dmg_min;
						 $this->dmg_max = $player->dmg_max;
						 $this->hp = $player->hp;
						 $this->exp = Server::get_exp($player->data['lvl']);
						 $this->weapon = $player->weapon;
						 $this->shield = $player->shield;
						 $this->nick = $player->data['user_name'];
					break;
				}
			break;
			case 10:
				switch ($player->data["dungeon_10"] - 1)
				{
					case 1:
						 $this->lvl = 205;
						 $this->class = 3;
						 $this->face[1] = 101;
						 $this->attr_str = 995;
						 $this->attr_agi = 3940;
						 $this->attr_int = 985;
						 $this->attr_wit = 3660;
						 $this->attr_luck = 1450;
						 $this->dmg_min = 137460;
						 $this->dmg_max = 206190;
						 $this->hp = 3015840;
						 $this->exp = 12751538;
						 $this->weapon["item_id"] = "6";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 2:
						 $this->lvl = 210;
						 $this->class = 1;
						 $this->face[1] = 115;
						 $this->attr_str = 4040;
						 $this->attr_agi = 1420;
						 $this->attr_int = 1395;
						 $this->attr_wit = 3760;
						 $this->attr_luck = 1010;
						 $this->dmg_min = 115425;
						 $this->dmg_max = 173340;
						 $this->hp = 3966800;
						 $this->exp = 14222021;
						 $this->weapon["item_id"] = "21";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 3:
						 $this->lvl = 215;
						 $this->class = 1;
						 $this->face[1] = 159;
						 $this->attr_str = 4140;
						 $this->attr_agi = 1455;
						 $this->attr_int = 1430;
						 $this->attr_wit = 3860;
						 $this->attr_luck = 1030;
						 $this->dmg_min = 121180;
						 $this->dmg_max = 181770;
						 $this->hp = 4168800;
						 $this->exp = 15824529;
						 $this->weapon["item_id"] = "13";
						 $this->weapon["has_weapon"] = 1;
						 $this->shield = $this->load_shield(5, 15);
					break;
					case 4:
						 $this->lvl = 220;
						 $this->class = 1;
						 $this->face[1] = 21;
						 $this->attr_str = 4240;
						 $this->attr_agi = 1490;
						 $this->attr_int = 1465;
						 $this->attr_wit = 3960;
						 $this->attr_luck = 1050;
						 $this->dmg_min = 127075;
						 $this->dmg_max = 190400;
						 $this->hp = 4375800;
						 $this->exp = 17581974;
						 $this->weapon["has_weapon"] = "-4";
					break;
					case 5:
						 $this->lvl = 225;
						 $this->class = 3;
						 $this->face[1] = 61;
						 $this->attr_str = 1095;
						 $this->attr_agi = 4340;
						 $this->attr_int = 1085;
						 $this->attr_wit = 4060;
						 $this->attr_luck = 1590;
						 $this->dmg_min = 166170;
						 $this->dmg_max = 249255;
						 $this->hp = 3670240;
						 $this->exp = 19491852;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 6:
						 $this->lvl = 230;
						 $this->class = 3;
						 $this->face[1] = 163;
						 $this->attr_str = 1120;
						 $this->attr_agi = 4440;
						 $this->attr_int = 1110;
						 $this->attr_wit = 4160;
						 $this->attr_luck = 1615;
						 $this->dmg_min = 173995;
						 $this->dmg_max = 260770;
						 $this->hp = 3843840;
						 $this->exp = 21576743;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 7:
						 $this->lvl = 235;
						 $this->class = 2;
						 $this->face[1] = 161;
						 $this->attr_str = 1125;
						 $this->attr_agi = 1145;
						 $this->attr_int = 4620;
						 $this->attr_wit = 4180;
						 $this->attr_luck = 1655;
						 $this->dmg_min = 332897;
						 $this->dmg_max = 499114;
						 $this->hp = 1972960;
						 $this->exp = 23839326;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 8:
						 $this->lvl = 240;
						 $this->class = 1;
						 $this->face[1] = 159;
						 $this->attr_str = 4640;
						 $this->attr_agi = 1630;
						 $this->attr_int = 1605;
						 $this->attr_wit = 4360;
						 $this->attr_luck = 1130;
						 $this->dmg_min = 151590;
						 $this->dmg_max = 227385;
						 $this->hp = 5253800;
						 $this->exp = 26302843;
						 $this->weapon["item_id"] = "13";
						 $this->weapon["has_weapon"] = 1;
						 $this->shield = $this->load_shield(5, 15);
					break;
					case 9:
						 $this->lvl = 245;
						 $this->class = 1;
						 $this->face[1] = 158;
						 $this->attr_str = 4740;
						 $this->attr_agi = 1665;
						 $this->attr_int = 1640;
						 $this->attr_wit = 4460;
						 $this->attr_luck = 1150;
						 $this->dmg_min = 158175;
						 $this->dmg_max = 237025;
						 $this->hp = 5485800;
						 $this->exp = 28966329;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 10:
						 $this->lvl = 250;
						 $this->class = 1;
						 $this->face[1] = 165;
						 $this->attr_str = 4840;
						 $this->attr_agi = 1700;
						 $this->attr_int = 1675;
						 $this->attr_wit = 4560;
						 $this->attr_luck = 1170;
						 $this->dmg_min = 164900;
						 $this->dmg_max = 247350;
						 $this->hp = 5722800;
						 $this->exp = 31862139;
						 $this->weapon["item_id"] = "11";
						 $this->weapon["has_weapon"] = 1;
					break;
				}
			break;
			case 11:
				switch ($player->data["dungeon_11"] - 1)
				{
					case 1:
						 $this->lvl = 255;
						 $this->class = 1;
						 $this->face[1] = 173;
						 $this->attr_str = 4940;
						 $this->attr_agi = 1735;
						 $this->attr_int = 1710;
						 $this->attr_wit = 4660;
						 $this->attr_luck = 1190;
						 $this->dmg_min = 171270;
						 $this->dmg_max = 257400;
						 $this->hp = 5964800;
						 $this->exp = 34985806;
						 $this->weapon["item_id"] = "20";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 2:
						 $this->lvl = 260;
						 $this->class = 3;
						 $this->face[1] = 174;
						 $this->attr_str = 1270;
						 $this->attr_agi = 5040;
						 $this->attr_int = 1260;
						 $this->attr_wit = 4760;
						 $this->attr_luck = 1835;
						 $this->dmg_min = 223210;
						 $this->dmg_max = 334815;
						 $this->hp = 4969440;
						 $this->exp = 38369989;
						 $this->weapon["item_id"] = "9";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 3:
						 $this->lvl = 265;
						 $this->class = 1;
						 $this->face[1] = 175;
						 $this->attr_str = 5140;
						 $this->attr_agi = 1805;
						 $this->attr_int = 1780;
						 $this->attr_wit = 4860;
						 $this->attr_luck = 1230;
						 $this->dmg_min = 185400;
						 $this->dmg_max = 278100;
						 $this->hp = 6463800;
						 $this->exp = 42016588;
						 $this->weapon["item_id"] = "12";
						 $this->weapon["has_weapon"] = 1;
						 $this->shield = $this->load_shield(3, 15);
					break;
					case 4:
						 $this->lvl = 270;
						 $this->class = 1;
						 $this->face[1] = 176;
						 $this->attr_str = 5240;
						 $this->attr_agi = 1840;
						 $this->attr_int = 1815;
						 $this->attr_wit = 4960;
						 $this->attr_luck = 1250;
						 $this->dmg_min = 192675;
						 $this->dmg_max = 288750;
						 $this->hp = 6720800;
						 $this->exp = 45958126;
						 $this->weapon["item_id"] = "0";
						 $this->weapon["has_weapon"] = 0;
					break;
					case 5:
						 $this->lvl = 275;
						 $this->class = 2;
						 $this->face[1] = 177;
						 $this->attr_str = 1325;
						 $this->attr_agi = 1345;
						 $this->attr_int = 5420;
						 $this->attr_wit = 4980;
						 $this->attr_luck = 1935;
						 $this->dmg_min = 456663;
						 $this->dmg_max = 685266;
						 $this->hp = 2748960;
						 $this->exp = 50191950;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 6:
						 $this->lvl = 280;
						 $this->class = 3;
						 $this->face[1] = 178;
						 $this->attr_str = 1370;
						 $this->attr_agi = 5440;
						 $this->attr_int = 1360;
						 $this->attr_wit = 5160;
						 $this->attr_luck = 1975;
						 $this->dmg_min = 259420;
						 $this->dmg_max = 389130;
						 $this->hp = 5799840;
						 $this->exp = 54764133;
						 $this->weapon["has_weapon"] = "-4";
					break;
					case 7:
						 $this->lvl = 285;
						 $this->class = 1;
						 $this->face[1] = 179;
						 $this->attr_str = 5540;
						 $this->attr_agi = 1945;
						 $this->attr_int = 1920;
						 $this->attr_wit = 5260;
						 $this->attr_luck = 1310;
						 $this->dmg_min = 214785;
						 $this->dmg_max = 322455;
						 $this->hp = 7521800;
						 $this->exp = 59666036;
						 $this->weapon["item_id"] = "20";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 8:
						 $this->lvl = 290;
						 $this->class = 3;
						 $this->face[1] = 180;
						 $this->attr_str = 1420;
						 $this->attr_agi = 5640;
						 $this->attr_int = 1410;
						 $this->attr_wit = 5360;
						 $this->attr_luck = 2045;
						 $this->dmg_min = 278545;
						 $this->dmg_max = 417535;
						 $this->hp = 6239040;
						 $this->exp = 64942539;
						 $this->weapon["item_id"] = "9";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 9:
						 $this->lvl = 295;
						 $this->class = 2;
						 $this->face[1] = 181;
						 $this->attr_str = 1425;
						 $this->attr_agi = 1445;
						 $this->attr_int = 5820;
						 $this->attr_wit = 5380;
						 $this->attr_luck = 2075;
						 $this->dmg_min = 525866;
						 $this->dmg_max = 789382;
						 $this->hp = 3184960;
						 $this->exp = 70595045;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 10:
						 $this->lvl = 300;
						 $this->class = 1;
						 $this->face[1] = 182;
						 $this->attr_str = 5840;
						 $this->attr_agi = 2050;
						 $this->attr_int = 2025;
						 $this->attr_wit = 5560;
						 $this->attr_luck = 1370;
						 $this->dmg_min = 238680;
						 $this->dmg_max = 358020;
						 $this->hp = 8367800;
						 $this->exp = 76669139;
						 $this->weapon["has_weapon"] = "-1";
					break;
				}
			break;
			case 12:
				switch ($player->data["dungeon_12"] - 1)
				{
					case 1:
						 $this->lvl = 305;
						 $this->class = 2;
						 $this->face[1] = 183;
						 $this->attr_str = 1475;
						 $this->attr_agi = 1495;
						 $this->attr_int = 6020;
						 $this->attr_wit = 5580;
						 $this->attr_luck = 2145;
						 $this->dmg_min = 562599;
						 $this->dmg_max = 843597;
						 $this->hp = 3414960;
						 $this->exp = 83158305;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 2:
						 $this->lvl = 310;
						 $this->class = 2;
						 $this->face[1] = 184;
						 $this->attr_str = 1500;
						 $this->attr_agi = 1520;
						 $this->attr_int = 6120;
						 $this->attr_wit = 5680;
						 $this->attr_luck = 2180;
						 $this->dmg_min = 581124;
						 $this->dmg_max = 871686;
						 $this->hp = 3532960;
						 $this->exp = 90125436;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 3:
						 $this->lvl = 315;
						 $this->class = 1;
						 $this->face[1] = 185;
						 $this->attr_str = 6140;
						 $this->attr_agi = 2155;
						 $this->attr_int = 2130;
						 $this->attr_wit = 5860;
						 $this->attr_luck = 1430;
						 $this->dmg_min = 263220;
						 $this->dmg_max = 394830;
						 $this->hp = 9258800;
						 $this->exp = 97556858;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 4:
						 $this->lvl = 320;
						 $this->class = 3;
						 $this->face[1] = 186;
						 $this->attr_str = 1570;
						 $this->attr_agi = 6240;
						 $this->attr_int = 1560;
						 $this->attr_wit = 5960;
						 $this->attr_luck = 2255;
						 $this->dmg_min = 340000;
						 $this->dmg_max = 510000;
						 $this->hp = 7652640;
						 $this->exp = 105514978;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 5:
						 $this->lvl = 325;
						 $this->class = 2;
						 $this->face[1] = 187;
						 $this->attr_str = 1575;
						 $this->attr_agi = 1595;
						 $this->attr_int = 6420;
						 $this->attr_wit = 5980;
						 $this->attr_luck = 2285;
						 $this->dmg_min = 639142;
						 $this->dmg_max = 958713;
						 $this->hp = 3898960;
						 $this->exp = 113997992;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 6:
						 $this->lvl = 330;
						 $this->class = 1;
						 $this->face[1] = 188;
						 $this->attr_str = 6440;
						 $this->attr_agi = 2260;
						 $this->attr_int = 2235;
						 $this->attr_wit = 6160;
						 $this->attr_luck = 1490;
						 $this->dmg_min = 288960;
						 $this->dmg_max = 434085;
						 $this->hp = 10194800;
						 $this->exp = 123067419;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 7:
						 $this->lvl = 335;
						 $this->class = 3;
						 $this->face[1] = 189;
						 $this->attr_str = 1645;
						 $this->attr_agi = 6540;
						 $this->attr_int = 1635;
						 $this->attr_wit = 6260;
						 $this->attr_luck = 2360;
						 $this->dmg_min = 372695;
						 $this->dmg_max = 559370;
						 $this->hp = 8413440;
						 $this->exp = 132712488;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 8:
						 $this->lvl = 340;
						 $this->class = 1;
						 $this->face[1] = 190;
						 $this->attr_str = 6640;
						 $this->attr_agi = 2330;
						 $this->attr_int = 2305;
						 $this->attr_wit = 6360;
						 $this->attr_luck = 1530;
						 $this->dmg_min = 307230;
						 $this->dmg_max = 460845;
						 $this->hp = 10843800;
						 $this->exp = 143018630;
						 $this->weapon["has_weapon"] = "-3";
					break;
					case 9:
						 $this->lvl = 345;
						 $this->class = 1;
						 $this->face[1] = 191;
						 $this->attr_str = 6740;
						 $this->attr_agi = 2365;
						 $this->attr_int = 2340;
						 $this->attr_wit = 6460;
						 $this->attr_luck = 1150;
						 $this->dmg_min = 316575;
						 $this->dmg_max = 474525;
						 $this->hp = 11175800;
						 $this->exp = 153964246;
						 $this->weapon["item_id"] = "0";
						 $this->weapon["has_weapon"] = 0;
					break;
					case 10:
						 $this->lvl = 350;
						 $this->class = 2;
						 $this->face[1] = 192;
						 $this->attr_str = 6840;
						 $this->attr_agi = 2400;
						 $this->attr_int = 2375;
						 $this->attr_wit = 6560;
						 $this->attr_luck = 1570;
						 $this->dmg_min = 326060;
						 $this->dmg_max = 489090;
						 $this->hp = 11512800;
						 $this->exp = 165631756;
						 $this->weapon["has_weapon"] = "-1";
					break;
				}
			break;
			case 13:
				switch ($player->data["dungeon_13"] - 1)
				{
					case 1:
						 $this->lvl = 355;
						 $this->class = 1;
						 $this->face[1] = 243;
						 $this->attr_str = 7570;
						 $this->attr_agi = 2655;
						 $this->attr_int = 2630;
						 $this->attr_wit = 7290;
						 $this->attr_luck = 1716;
						 $this->dmg_min = 365356;
						 $this->dmg_max = 548792;
						 $this->hp = 12976200;
						 $this->exp = 178017293;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 2:
						 $this->lvl = 360;
						 $this->class = 2;
						 $this->face[1] = 244;
						 $this->attr_str = 1970;
						 $this->attr_agi = 1990;
						 $this->attr_int = 8000;
						 $this->attr_wit = 7560;
						 $this->attr_luck = 2838;
						 $this->dmg_min = 881901;
						 $this->dmg_max = 1323252;
						 $this->hp = 5458320;
						 $this->exp = 191202824;
						 $this->weapon["has_weapon"] = "-3";
					break;
					case 3:
						 $this->lvl = 365;
						 $this->class = 1;
						 $this->face[1] = 245;
						 $this->attr_str = 8290;
						 $this->attr_agi = 2908;
						 $this->attr_int = 2882;
						 $this->attr_wit = 8010;
						 $this->attr_luck = 1860;
						 $this->dmg_min = 411680;
						 $this->dmg_max = 617520;
						 $this->hp = 14658300;
						 $this->exp = 205171015;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 4:
						 $this->lvl = 370;
						 $this->class = 2;
						 $this->face[1] = 246;
						 $this->attr_str = 2160;
						 $this->attr_agi = 2180;
						 $this->attr_int = 8760;
						 $this->attr_wit = 8320;
						 $this->attr_luck = 3104;
						 $this->dmg_min = 992764;
						 $this->dmg_max = 1489146;
						 $this->hp = 6173440;
						 $this->exp = 220033230;
						 $this->weapon["has_weapon"] = "-2";
					break;
					case 5:
						 $this->lvl = 375;
						 $this->class = 1;
						 $this->face[1] = 247;
						 $this->attr_str = 9340;
						 $this->attr_agi = 3275;
						 $this->attr_int = 3250;
						 $this->attr_wit = 9060;
						 $this->attr_luck = 2070;
						 $this->dmg_min = 476850;
						 $this->dmg_max = 715275;
						 $this->hp = 17032800;
						 $this->exp = 235758967;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 6:
						 $this->lvl = 380;
						 $this->class = 3;
						 $this->face[1] = 248;
						 $this->attr_str = 2682;
						 $this->attr_agi = 10690;
						 $this->attr_int = 2672;
						 $this->attr_wit = 10410;
						 $this->attr_luck = 3812;
						 $this->dmg_min = 691220;
						 $this->dmg_max = 1036830;
						 $this->hp = 15864840;
						 $this->exp = 252458197;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 7:
						 $this->lvl = 385;
						 $this->class = 2;
						 $this->face[1] = 249;
						 $this->attr_str = 2888;
						 $this->attr_agi = 2908;
						 $this->attr_int = 11670;
						 $this->attr_wit = 11230;
						 $this->attr_luck = 4122;
						 $this->dmg_min = 1375904;
						 $this->dmg_max = 2063856;
						 $this->hp = 8669560;
						 $this->exp = 270120546;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 8:
						 $this->lvl = 390;
						 $this->class = 1;
						 $this->face[1] = 250;
						 $this->attr_str = 12540;
						 $this->attr_agi = 4395;
						 $this->attr_int = 4370;
						 $this->attr_wit = 12260;
						 $this->attr_luck = 2710;
						 $this->dmg_min = 665150;
						 $this->dmg_max = 997725;
						 $this->hp = 23968300;
						 $this->exp = 288853442;
						 $this->weapon["has_weapon"] = "-1";
					break;
					case 9:
						 $this->lvl = 395;
						 $this->class = 1;
						 $this->face[1] = 251;
						 $this->attr_str = 13540;
						 $this->attr_agi = 4724;
						 $this->attr_int = 4720;
						 $this->attr_wit = 13260;
						 $this->attr_luck = 2910;
						 $this->dmg_min = 727635;
						 $this->dmg_max = 1090775;
						 $this->hp = 26254800;
						 $this->exp = 308630400;
						 $this->weapon["item_id"] = "13";
						 $this->weapon["has_weapon"] = 1;
					break;
					case 10:
						 $this->lvl = 400;
						 $this->class = 1;
						 $this->face[1] = 252;
						 $this->attr_str = 16840;
						 $this->attr_agi = 5900;
						 $this->attr_int = 5875;
						 $this->attr_wit = 16540;
						 $this->attr_luck = 3570;
						 $this->dmg_min = 916640;
						 $this->dmg_max = 1374960;
						 $this->hp = 33162700;
						 $this->exp = 329599075;
						 $this->weapon["item_id"] = "23";
						 $this->weapon["has_weapon"] = 1;
					break;
				}
			break;
		}
	}
	
	public function load_shield($id, $proc)
	{
		$shield['has_shield'] = 2;
		$shield['item_id'] = $id;
		$shield['item_type'] = 2;
		$shield['dmg_min'] = $proc;
		$shield['dmg_max'] = 0;
		$shield['attr_type_1'] = 0;
		$shield['attr_type_2'] = 0;
		$shield['attr_type_3'] = 0;
		$shield['attr_val_1'] = 0;
		$shield['attr_val_2'] = 0;
		$shield['attr_val_3'] = 0;
		$shield['gold'] = 100;
		$shield['mush'] = 0;
		
		return $shield;
	}
}
?>