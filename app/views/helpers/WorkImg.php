<?php
Class Main_WorkImg {
	public function workImg($work,$time=null, $text = null) {
		switch ($work) {
			case JOB_HUNT : $img= "<img src=\"/img/Bhunt.png\" />"; break;
			case JOB_JOB : $img= "<img src=\"/img/Bwork.png\" />"; break;
			case JOB_HEAL : $img= "<img src=\"/img/ligonine.png\" />"; break;
			case JOB_EXERCISE : $img= "<img src=\"/img/exercise.jpg\" />"; break;
			case JOB_ILSISI : $img= "<img src=\"/img/namas.png\" />"; break;
            case JOB_AGONIJA : $img= "<img src=\"/img/agonija2.png\" />"; break;
			case JOB_MISSION : $img= "<img src=\"/img/mission2.png\" />"; break;
			default : $text=$work; break;
		}
        
        if ($work >= 200 && $work <= 299) {
            $doctor = $work - 200;
            $data = include("app/data/doctors.php");
            $text = "Jus gydo {$data[$doctor]['name']}";
            $img = "<img src=\"/img/doctor/$doctor.png\" />";
        }

		if (!$text) {
			switch ($work) {
				case JOB_HUNT : $text= "Medžiojate"; break;
				case JOB_JOB : $text= "Dirbate"; break;
				case JOB_HEAL : $text= "Gydotės"; break;
				case JOB_ILSISI : $text= "Ilsitės"; break;
                case JOB_AGONIJA : $text= "Agonija"; break;
                case JOB_EXERCISE : $text= "Treniruotė"; break;
			}
		}

		$return='<div id="workImg" style="font-weight:bold;text-align:center;border:solid 1px #1F354C;margin:10px;padding:10px;display:block;width:220px;margin-left:auto;margin-right:auto;">';
		if (isset($img)) {
			$return.=$img;
		}
		$return.='<div style="font-size:14px;">'.$text.'</div>';

		if (isset($time) && $time > 0) {
			$key=genkey(4);
			$return.="<div id=\"liko-$key\"></div>";
			require('app/views/helpers/Run.php');
			$run=new Main_Run();

			$run->run("new Timer($time,'liko-$key',{onstop: function() { Crefresh(); }, ontimer:function (title) { changeTitle(title) } })");
		}
		$return.='</div>';
		return $return;
	}
}

?>
