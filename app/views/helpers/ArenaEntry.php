<?php
class Main_ArenaEntry {
	private function lygis($lvl) {
		return ($lvl==0)? "betkoks" : $lvl;
	}

	public function arenaEntry($data,$uid, $race) {
		$text='<table class="arena_entry_box">';

		$text.='<tr><td colspan="5" style="text-align:center;font-size:14px">
			<b>'.$data->info->title.'</b>
			<div style="font-size:10px">'.$data->info->start.' - '.$data->info->end.'</div>
			<div style="font-size:10px">Lygis nuo '.$this->lygis($data->info->min_lvl).' iki '. $this->lygis($data->info->max_lvl).'</div>
			<div style="font-size:10px"><u id="map_info_'.($data->info->id).'">Žemėlapis</u></div>
		</td></tr>';

		$maps=include("app/data/maps.php");

		require_once('app/views/helpers/Run.php');
		$run=new Main_Run();

		$run->run("new Overlib('{$maps[$data->info->map]['apie']}', P('map_info_{$data->info->id}'));");

		$left=array();
		$right=array();
		$user_aid=0;
		foreach ($data->fighters as $v) {
			if ($v->side=='L') {
				$left[]=$v;
			} else {
				$right[]=$v;
			}
			if ($v->uid==$uid) {
				$user_aid=$v->id;
			}
		}

		$max=$data->info->size/2;
		for ($x=0; $x<=4; $x++) {
			$text.='<tr>';

			$color=($x+1>$max)? 'background-color:#1F354C;' : "";

			$text.='<td style="width:16px; height:16px;'.$color.'">';

			if (isset($left[$x])) {
				if ($left[$x]->uid==$data->info->creator) {
					$text.='<img src="/img/crown.png" border="0" width="16" height="16">';
				} elseif ($left[$x]->uid==$uid) {
					$text.='<img class="pointer" onclick="content(\'/page/arena\', {\'leave\' : '.($user_aid).'}, no);" src="/img/logout.png" border="0" width="16" height="16">';
				}
			}

			$text.='</td>';

			$text.='<td style="width:80px; height:16px; overflow:hidden;'.$color.'"><div style="width:80px; height:16px; overflow:hidden;">';

			if (isset($left[$x])) {
				$user=$left[$x]->getuser(true);
				$text.=$user. '('.$user->getlvl().')';
			}
			$text.='</td>';

			if ($x==0) {
				$text.='<td rowspan="5" style="width:52px;text-align:center; font-size:30px" valign="middle">VS</td>';
			}

			$text.='<td style="width:80px; height:16px; overflow:hidden;'.$color.'"><div style="width:80px; height:16px; overflow:hidden;">';

			if (isset($right[$x])) {
				$user=$right[$x]->getuser(true);
				$text.=$user. '('.$user->getlvl().')';
			}
			$text.='</td>';

			$text.='<td style="width:16px; height:16px;'.$color.'">';

			if (isset($right[$x])) {
				if ($right[$x]->uid==$data->info->creator) {
					$text.='<img src="/img/crown.png" border="0" width="16" height="16">';
				} elseif ($right[$x]->uid==$uid) {
					$text.='<img  class="pointer" onclick="content(\'/page/arena\', {\'leave\' : '.($user_aid).'}, no);" src="/img/logout.png" border="0" width="16" height="16">';
				}
			}

			$text.='</td></tr>';

		}

		$text.='<tr><td colspan="5" style="text-align:center">';

		if ($uid==$data->info->creator) {
			if (count($right)==0 && count($left)==1) {
				$text.="<a onclick=\"content('/page/arena', {'leave' : {$user_aid}}, no);\">Naikinti</a>";
			} else {
				$text.='<i>Naikinti negalima</i>';
			}
		} else {
            $users = new Users();
            $user = $users->getuser($uid, true, true);

			$race=$left[0]->getuser(true)->getrace();

			$fighters=new Fighters();

			if ((($race==$user->getrace() && count($left)<$max) ||
				($race!=$user->getrace() && count($right)<$max)) &&
				($fighters->countFightShedules($uid)<4))
			{
				if ($data->info->password) {
					$text.="<input type='password' style='font-size:6px' id='password-{$data->info->id}' />";
					$text.="<input type='button' style='font-size:6px' value='jungtis' onclick=\"content('/page/arena', {'join' : {$data->info->id}, 'password' : \$F('password-{$data->info->id}')}, no);\" />";
				} else {
					$text.="<a onclick=\"content('/page/arena', {'join' : {$data->info->id}}, no);\">Prisijungti</a>";
				}

			} else {
				$text.='<i>Prisijungti negalima</i>';
			}
		}


		$text.='</td></tr>';

		$text.='</table>';

		return $text;
	}
}
?>
