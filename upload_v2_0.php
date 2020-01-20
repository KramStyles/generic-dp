<?php
require_once "processors/controllers.php";
require_once "fileserver.php";
require_once "HTML_functions.php";
extract($_POST, EXTR_OVERWRITE);

$siteClass = new Generic();
$uri = extract((array) $siteClass->getURIdata(), EXTR_OVERWRITE);

$datatypes = [
	'picture'=>['gif','jpg','bmp','jpeg','png','svg'],
	'audio'=>['mp3','wma','m4a','ogg'],
	'document'=>['doc','docx','txt','pdf','xls','xlsx'],
	'video'=>['mp4','avi','3pg','mkv','wmv'],
	'pdf'=>['pdf'],
	'archive'=>['zip','7z','rar','exe']
];
if(isset($switch) ){
	//echo 'here0';
	switch($switch){
		case 1:
			$return = array();
			//echo 'here1';
			$q = "select company_name, company_logo from featured_company where TRIM(company_logo) !='' order by company_name desc";
			$con = $db->query($q) or die($db->error);
			if($con->num_rows > 0){
				//echo 'here3';
				while($row =  $con->fetch_assoc()){
					$logo = $row['company_logo'];
					if(stripos($logo,'../') === false ){
						$logo = '../'.$logo;
					}
					$r['src'] = $logo;
					$r['icon'] = $logo;
					$r['name'] = $row['company_name'];
					$r['extension'] = getFileFormat($logo);
					$return[]=utf8ize($r);
				}
				//echo 'here4';
			}
		echo json_encode($return);
		break;
	}
	//echo 'here7';
die();
}
//echo 'here8';


function returnUploadFolder($ms)

{

	if($ms=="jpg"||$ms ="png"|| $ms ="gif") return "content/pictures/";

	else return "content/userdata/";

}

function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        echo 'error';
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            //self::deleteDir($file);
			deleteDir($file);
        } else {
            unlink($file);
        }
    }
    if(rmdir($dirPath)) return 1; else return 0;
}

extract($_COOKIE,EXTR_OVERWRITE);

if(isset($_POST['cameraPhoto'])){
	$data = $_POST['cameraPhoto'];
  list($type, $data) = explode(';', $data);
  list(, $data)      = explode(',', $data);
  $data = base64_decode($data);
	if(!is_dir($folder)){
		if(!mkdir($folder,0755,true)){echo json_encode(['error'=>'cannot create folder']);die();}
	}
	$filename = $filename.".png";
	$filename = renameIfExists($filename, $folder);
  if(file_put_contents($folder.$filename, $data)){
		$_rsp['src'] = $site.str_replace('../','',$folder . $filename);
		$_rsp['name'] = basename($filename);
		echo json_encode($_rsp);
	}else{echo "an error occured";}
}
elseif(isset($_FILES["file_upload"])) {

 	$filename =str_replace(' ','_',$_FILES["file_upload"]["name"]);
	$ext = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
	if($_FILES["file_upload"]["size"] < 100045766666666){
		if(!is_dir($folder)){
			if(!mkdir($folder,0755,true)){echo json_encode(['error'=>'cannot create folder']);die();}
		}
		if(isset($getype)){
			if(!isset($datatypes[$getype])){echo json_encode(['error'=>'Unknown filetype']);die();}
			if(array_search($ext, $datatypes[$getype]) === false){echo json_encode(['error'=>"unrecognized $getype format"]);die();}
		}else{
			$getype = getdataType($datatypes, $ext);
			if($getype === false){echo json_encode(['error'=>'Unknown filetype']);die();}
		}
		$filename=renameIfExists($filename,$folder);

		if(move_uploaded_file($_FILES["file_upload"]["tmp_name"],"$folder".$filename)){
			if($getype == 'picture'){
				list($orig_width, $orig_height) = getimagesize($folder.$filename);
				$icon = returnIcon($filename, $folder);
				if($orig_width > 200 or $orig_height > 200){
					if(checkPictureFormat($filename)){
						if(!is_dir($folder."tbn")){
							mkdir($folder."tbn",0755);
						}
						$icon=$folder.'tbn/'.$filename;
						if(!is_file($icon)){
							resizePicture($folder.$filename, $icon, 128, 128);
						}
						if(!empty($width) && !empty($height)){
							resizePicture($folder.$filename, $folder.$filename, $width, $height);
						}
					}
				}
				$_rsp['size'] = [$orig_width, $orig_height];
				$_rsp['icon'] = $icon;
				if(isset($old_img) && file_exists($old_img)){
					unlink($old_img);
					unlink(dirname($old_img)."tbn/".basename($old_img));
				}
			}
			$_rsp['src'] = http_filepath($folder.$filename);
			$_rsp['name'] = basename($filename);
			$_rsp['extension'] = $ext;
			echo json_encode($_rsp);
		} else {echo json_encode(['error'=>"Couldn't upload ".$getype]);die();}
	}else {echo json_encode(['error'=>"File size is too much"]);die();}
} else if(!empty($url_file)){
		$dir=$folder;
		$ext=getFileFormat($url_file);
		$icon=returnIcon($url_file,$dir);
		if($icon !=false){

			$bs=basename($url_file);

			echo $bs;

			echo $dir;

			$filenmae="lop.kop";//$filename=renameIfExists($bs,$dir);

			$f=getRemoteFiles($url_file);

			if($f !=false)

			{

				$savefile = fopen($dir . $filename, 'w');

				fwrite($savefile, $f);

				fclose($savefile);

				if(!is_dir($dir."tbn"))

				{

					mkdir($dir."tbn",0755);

				}

				$thumbnail=$dir."tbn/".$filename;

				if(!is_file($thumbnail))

				{

					resizePicture($dir . $filename,$thumbnail, 128, 128);

				}

			$name[0] =$dir.$filename;

			$name[1] =$thumbnail;

			$name[2] =basename($filename);

			$name[3] =$ext;

			echo json_encode($name);

		} else echo 0;

	} else echo -1;



}

else if(!empty($url_anchor))

{



		$f=getRemoteFiles($url_anchor);

		if($f !=false)

		{

			$title=get_tag($f,'title');

			$r["i"] =$url_anchor;

			$r["c"][]=array("v"=>$title);



			echo json_encode($r);

		} else echo 0;





}
else if(!empty($moveFolder)){
	$new_name= $dropFolder."/".basename($moveFolder);
	if(rename($moveFolder,$new_name)){
		$arr = [
			'newfile' => $new_name,
			'oldfile' => $moveFolder,
		];
		echo json_encode($arr);
	}else{
		echo 0;
	}
	die();
}

//Delete files or folders in upload dialogue
else if(!empty($delPath))
{
	switch ($type)
	{
		case 1:
		if(is_http_url($delPath))$delPath = absolute_filepath($delPath);
		if(file_exists($delPath)){
 			echo unlink($delPath);
		}else{
			echo 0;
		}
		break;
		case 0:
			echo deleteDir($delPath);
		break;
	}
}
//Create new folder in upload dialogue
else if(!empty($folderName)){
	$count=0;
	$folderName = strtolower(str_replace(' ','_',$folderName));
	mkdir($ucPath.$folderName,0755,true);
	$in[$count]['src']= $ucPath.$folderName;
	$in[$count]['name']= basename($folderName);
	$in[$count]['isFolder']= -1;
	$in[$count]['extension']= -1;
	echo json_encode($in);
}
//Rename files in upload dialogue
else if(!empty($newPath)){
	if(is_http_url($newPath))$newPath = absolute_filepath($newPath);
	if(is_http_url($oldPath))$oldPath = absolute_filepath($oldPath);
	echo rename($oldPath, $newPath);
}
//Delete redundant cropped and uncropped photos uploaded while trying to set generic dp
else if(isset($generic_delete)){
	if(gettype($unlink) == 'array'){
		foreach($unlink as $file){
			if(is_http_url($file))$file = absolute_filepath($file);
			if(file_exists($file))unlink($file);
			$tbn = dirname($file)."/tbn/".basename($file);
			if(file_exists($tbn))unlink($tbn);
		}
		echo json_encode(['done'=>'Images cleared']);die();
	}else{echo json_encode(['done'=>'No Files to delete']);die();}
}
//Crop generic dp images;
else if(isset($generic_crop)){
	if(file_exists($image_source)){
		$file =  realpath($image_source);
		list($orig_width, $orig_height) = getimagesize($file);
		$new_width = ($resizable_width * $orig_width)/$img_width;
		$new_height = ($resizable_height * $orig_height)/$img_height;
		$x_cord = ($orig_width * $resizable_left)/$img_width;
		$y_cord = ($orig_height * $resizable_top)/$img_height;
		$param = [
			'x' => $x_cord,
			'y' => $y_cord,
			'width' => $new_width,
			'height' => $new_height
		];
		$filename = pathinfo($file,PATHINFO_FILENAME);
		$new_dp = $img_dir.$filename.rand().".png";
		$im = imagecreatefrompicture($file);
		//echo ; die();
		if(gettype($im) !== 'resource'){die(50);}
		$im2 = image_crop($im, $param);
		if ($im2 !== FALSE) {
			imagepng($im2, $new_dp);
			if(file_exists($new_dp)){
				//unlink($file);
				list($new_w, $new_h)  = getimagesize($new_dp);
				$response = array('src'=>$new_dp, 'size'=>[$new_w, $new_h], 'name'=>basename($new_dp));
				echo json_encode($response);
			}
		}else{echo json_encode(['error'=>"This image file can't be cropped"]);die();}
	}else{echo json_encode(['error'=>'File no longer exists']);die();}
}else{
	//die();
	if(!is_dir($folder)){
		mkdir($folder, 0755, true);
	}
	$ar=getLocalFiles($folder);

	if(isset($getype) and !empty($getype)){
		$search = $ar;
		$ar = [];
		$arrtype = $datatypes[$getype];
		foreach($search as $c => $item){
			$extx = strtolower(trim($item['extension']));
			if(array_search($extx, $arrtype) === false && $extx != '-1'){
				continue;
			}
			$ar[] = $item;
		}
		//$ar[] = ['../'.$folder,'Back','..','-1'];
	}
    echo json_encode($ar);
}
function image_crop($im,$param){
	// Get new sizes
	$width=imagesx ($im ) ;
	$height=imagesy ($im ) ;
	$newwidth = $param['width'];
	$newheight = $param['height'];
	  //echo " $height -- $width";
	  //see($param);
	// Load
	$thumb = imagecreatetruecolor($newwidth, $newheight);
	// Resize
	imagecopy($thumb, $im, 0, 0, $param['x'], $param['y'], $width, $height);
	// Output
	return $thumb;
  }
?>
