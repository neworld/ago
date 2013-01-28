<?php
class Main_ItemOverDiv {
	/**
	* @desc sugeneruojam itemo overdiva
	* @param Item daigtas
	* @return String tekstas
	*/
	public function itemOverDiv($item,$lvl=null,$race=null, $num = null, $k = 1) {
		$items_DB=include('app/data/item_DB.php');

		$type=($item->type=="GUN")? "Ginklas" : $items_DB[$item->type]['title'];

		if ($lvl!=null) {
			$lvl_style=($lvl<$item->lvl)? ' style="color:#FF0000"' : '';
		} else {
			$lvl_style='';
		}

		if ($race!=null) {
			$race_style=($race!=$item->race)? ' style="color:#FF0000"' : '';
		} else {
			$race_style='';
		}

		if (($lvl!=null) && ($item->maxlvl>0) && ($item->maxlvl<$lvl)) {
			$maxlvl_style=' style="color:#FF0000"';
		} else {
			$maxlvl_style='';
		}

		switch ($item->color) {
			case 'grey' : $color = 'grey'; break;
			case 'white' : $color = 'white'; break;
			case 'green' : $color = '#03DB01'; break;
			case 'blue' : $color = '#494FD4'; break;
			case 'purple' : $color = '#C305BF'; break;
			case 'yellow' : $color = 'yellow'; break;
			case 'orange' : $color = 'orange'; break;
		}

		$text='<div style="font-size:10px;">';
		$text.="<div style=\"font-weight:bold;font-size:14px;color:$color;\">{$item->title}</div>";
		$text.="<div>Tipas: $type</div>";
		if (in_array($item->race,array(RACE_MAN,RACE_RJAL)))
			$text.="<div$race_style>Rasė: ".racename($item->race)."</div>";
		$text.="<div$lvl_style>Lygis: {$item->lvl}</div>";
		if ($item->maxlvl) {
			$text.="<div$maxlvl_style>Maksimalus lygis: {$item->maxlvl}</div>";
		}
		$text.="<div>Kokybė: {$item->quality}</div>";
		$text.="<div>Bazinė kaina: {$item->cost}</div>";

		if ($item->type=="GUN") {
			$text.="<div>Ginklas nešiojamas: {$item->get_gun_place()}</div>";
			$text.="<div>Ginklo tipas: {$item->get_gun_type()}</div>";
			if ($item->ammo!=AMMO_NO) {
				$text.="<div>Amunicija: {$item->get_ammo()}</div>";
			}
			$text.="<div>Periodas: {$item->plus['SPEED_DURATON']}s.</div>";
			$text.="<div>Daroma žala: {$item->getDMG()}</div>";
			$text.="<div>Daroma žala per sekundę: {$item->getDMGpersecond()}</div>";
		}
        
        $skills_db=include('app/data/skills_DB.php');
        $skills_db["ARMOR"]=array("NAME" => "Šarvai");
        $skills_db["ADD_HP_RATE"]=array("NAME" => "Gyvybių per minutę");
        $skills_db["ADD_DMG"]=array("NAME" => "Žalos");
        $skills_db["BANK"]=array("NAME" => "Papildomos vietos inventoriuje");
        $skills_db["RECOVERY_HP"]=array("NAME" => "Atstato gyvybių");
        $skills_db["RECOVERY_EXP"]=array("NAME" => "Prideda patirties");
        $skills_db["RECOVERY_HUNT"]=array("NAME" => "Atstato darbo laiko");
        $skills_db["ADDON"]=array("NAME" => "Laisva vieta priedui");
        
		if (count($item->plus)>0) {
            $scolor = ($k == 1)? "#FFFFFF" : "#FF0000" ;
			$text.="<div style=\"font-weight:bold;text-align:center\">Savybės</div>";

			foreach ($item->plus as $key => $v) {
				if (isset($skills_db[$key])) {
                    $v2 = round($v * $k);
					$text.="<div style=\"color:$scolor\">{$skills_db[$key]['NAME']}: $v2</div>";
				}
			}
		}
        
        if ($item->set) {
            $itemssets = new ItemsSets();
            $data = $itemssets->get($item->set);
            if ($data->count() > 0) {
                $text.="<div style=\"font-weight:bold;text-align:center\">Setas</div>";
                foreach ($data as $v)
                    if (isset($skills_db[$v->type])) {
                        $color = ($v->num <= $num)? '#FFFF80' : '#ACACAC';
                        $text.="<div style=\"color:$color\">{$skills_db[$v->type]['NAME']}: {$v->value}% ({$v->num})</div>";
                    }
            }
        }
                
        
        
		$text.="<div style=\"font-weight:bold;text-align:center\">Aprašymas</div>";
		$text.="<div style=\"font-style:italic;width:200px;\">".str_replace("\n","br />",$item->apie)."</div>";

		$text.='</div>';

		return $text;
	}
}
?>
