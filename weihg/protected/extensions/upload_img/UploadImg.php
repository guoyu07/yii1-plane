<?php 
/*
**@version 2013-12-26
**@author  yuzixiu
*/
class UploadImg extends CApplicationComponent{
	
//	public $model;
	/***
	******�ϴ�����ͼƬ
	******$model=>Ϊ������  $isthumb=>Ϊtrue����������ͼ��false����������ͼ
	***/
    public function UploadPhoto($model,$path,$isthumb=false){
		$root = $_SERVER['DOCUMENT_ROOT'].'/'.$path;
		$files = CUploadedFile::getInstance($model,'g_path');
		$folder = date('Ymd')."/";
		$pre = rand(999,9999).time();
		if($files && ($files->type == "image/jpeg" || $files->type == "image/pjpeg" || $files->type == "image/png" || $files->type == "image/x-png" || $files->type == "image/gif"))
		{
			$newName = $pre.'.'.$files->extensionName;
		}else{
			die($files->type);
		}
		if($files->size > 2000000){
			die("ͼƬ���ܳ���2M�����Ҽ���������ѡȡͼƬ��");
		}		
		if(!is_dir($root.$folder))
		{
			if(!mkdir($root.$folder, 0777, true)){
				die('�����ļ���ʧ��...');
			}else{
			//	chmod($root.$folder,0777);
			}
		}
		//echo $root.$folder.$newName;exit;
		if($files->saveAs($root.$folder.$newName))
		{
			if($isthumb){
				$this->thumbphoto($files,$path.$folder.$newName,$path.$folder.'thumb'.$newName);
				return $path.$folder.$newName.'#'.$path.$folder.'thumb'.$newName;
			}else{
				return $path.$folder.$newName;
			}
			
		}
    }
	// ----��������ͼ
	public function thumbphoto($model,$path,$newname){
		$im = null;
		$imagetype = strtolower($model->extensionName);
		if($imagetype == 'gif'){
			$im = imagecreatefromgif($path);
		}elseif($imagetype == 'jpg' || $imagetype == 'jpeg'){
			$im = imagecreatefromjpeg($path);
		}elseif($imagetype == 'png'){
			$im = imagecreatefrompng($path);
		}
		Yii::app()->CThumb->resizeImage($im,220, 220, $newname, $model->extensionName);	
	}	
	/***
	******�ϴ����ͼƬ
	******$model=>Ϊ������ $pathΪ�ϴ�·��
	***/
    public function UploadPhotos($model,$path)
    {
		$root = $_SERVER['DOCUMENT_ROOT'].'/'.$path;
		$files = CUploadedFile::getInstances($model,'p_path');
	//	print_r($files);exit;
		$return ='';
		$n = 1;
		foreach($files as $file){
			$pre = rand(999,9999).time().$n;
			if($file && ($file->type == "image/jpeg" || $file->type == "image/pjpeg" || $file->type == "image/png" || $file->type == "image/x-png" || $file->type == "image/gif"))
			{
				$newName = $pre.'.'.$file->extensionName;
			}else{
				die('wrong type');
			}
			if(!is_dir($root))
			{
				if(!mkdir($root, 0755, true))
				{
					die('�����ļ���ʧ��...');
				}
			}
			//echo $root.$folder.$newName;exit;
			if($file->saveAs($root.$newName))
			{		
				$return .= $path.$newName.'#';
			}
			$n++;
		}
		return $return;
    }		
}

?>