<?php
/**
 * Elgg file uploader/edit action
 *
 * @package ElggFile
 */

// Get variables
$title = get_input("title");
$desc = get_input("description");
$access_id = (int) get_input("access_id");
$container_guid = (int) get_input('container_guid', 0);
$guid = (int) get_input('file_guid');
$tags = get_input("tags");


	
if ($container_guid == 0) {
	$container_guid = elgg_get_logged_in_user_guid();
}

elgg_make_sticky_form('file');

// check if upload failed
if (!empty($_FILES['upload']['name']) && $_FILES['upload']['error'] != 0) {
	register_error(elgg_echo('file:cannotload'));
	forward(REFERER);
}

// check whether this is a new file or an edit
$new_file = true;
if ($guid > 0) {
	$new_file = false;
}

if ($new_file) {
	// must have a file if a new file upload
	$file = new ElggFile();
	$file->subtype = "file";

                                                                

} else {
	// load original file object
	// load original file object
	$file = get_entity($guid);
	if (!$file instanceof ElggFile) {
		register_error(elgg_echo('file:cannotload'));
		forward(REFERER);
	}
	/* @var ElggFile $file */

	// user must be able to edit file
	if (!$file->canEdit()) {
		register_error(elgg_echo('file:noaccess'));
		forward(REFERER);
	}
}

$file->title = $title;
$file->description = $desc;
$file->access_id = $access_id;
$file->container_guid = $container_guid;

$tags = explode(",", $tags);
$file->tags = $tags;


$filter_enabled = elgg_get_plugin_setting('filter_filter', 'filterpro'); 

$filter_word = elgg_get_plugin_setting('word_filter', 'filterpro');
$filter_excel = elgg_get_plugin_setting('excel_filter', 'filterpro'); 
$filter_jpg = elgg_get_plugin_setting('jpg_filter', 'filterpro'); 
$filter_mp3 = elgg_get_plugin_setting('mp3_filter', 'filterpro'); 
$filter_pdf = elgg_get_plugin_setting('pdf_filter', 'filterpro'); 


$word = 'application/msword';
$jpg = 'image/jpeg';
$excel = 'application/vnd.ms-excel';
$mp3_1 = 'video/mp4';
$pdf_docs = 'application/pdf';

$check_file_type = ElggFile::detectMimeType($_FILES['upload']['tmp_name'], $_FILES['upload']['type']);

if($filter_enabled == 1)	
{	
	if($filter_word == 1)
	{
		if ($check_file_type == $word)
			{

					if (isset($_FILES['upload']['name']) && !empty($_FILES['upload']['name'])) {
					
						$prefix = "file/";
					
						// if previous file, delete it
						if ($new_file == false) {
							$filename = $file->getFilenameOnFilestore();
							if (file_exists($filename)) {
								unlink($filename);
							}
					
							// use same filename on the disk - ensures thumbnails are overwritten
							$filestorename = $file->getFilename();
							$filestorename = elgg_substr($filestorename, elgg_strlen($prefix));
						} else {
							$filestorename = elgg_strtolower(time().$_FILES['upload']['name']);
						}
					
						$file->setFilename($prefix . $filestorename);
						$mime_type = ElggFile::detectMimeType($_FILES['upload']['tmp_name'], $_FILES['upload']['type']);
					
						// hack for Microsoft zipped formats
						$info = pathinfo($_FILES['upload']['name']);
						$office_formats = array('docx', 'xlsx', 'pptx');
						if ($mime_type == "application/zip" && in_array($info['extension'], $office_formats)) {
							switch ($info['extension']) {
								case 'docx':
									$mime_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
									break;
								case 'xlsx':
									$mime_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
									break;
								case 'pptx':
									$mime_type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
									break;
							}
						}
					
						// check for bad ppt detection
						if ($mime_type == "application/vnd.ms-office" && $info['extension'] == "ppt") {
							$mime_type = "application/vnd.ms-powerpoint";
						}
					
						$file->setMimeType($mime_type);
						$file->originalfilename = $_FILES['upload']['name'];
						$file->simpletype = file_get_simple_type($mime_type);
					
						// Open the file to guarantee the directory exists
						$file->open("write");
						$file->close();
						move_uploaded_file($_FILES['upload']['tmp_name'], $file->getFilenameOnFilestore());
					
						$guid = $file->save();
					
						// if image, we need to create thumbnails (this should be moved into a function)
						if ($guid && $file->simpletype == "image") {
							$file->icontime = time();
							
							$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
							if ($thumbnail) {
								$thumb = new ElggFile();
								$thumb->setMimeType($_FILES['upload']['type']);
					
								$thumb->setFilename($prefix."thumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbnail);
								$thumb->close();
					
								$file->thumbnail = $prefix."thumb".$filestorename;
								unset($thumbnail);
							}
					
							$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
							if ($thumbsmall) {
								$thumb->setFilename($prefix."smallthumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbsmall);
								$thumb->close();
								$file->smallthumb = $prefix."smallthumb".$filestorename;
								unset($thumbsmall);
							}
					
							$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
							if ($thumblarge) {
								$thumb->setFilename($prefix."largethumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumblarge);
								$thumb->close();
								$file->largethumb = $prefix."largethumb".$filestorename;
								unset($thumblarge);
							}
						}
					}
					
					
					else {
						// not saving a file but still need to save the entity to push attributes to database
						if ($check_file_type == 1)
						{
						$file->save();
						}
					}
					
								
					
							
							
							
							

			}
			
	}



	if($filter_mp3 == 1)
	{
		if ($check_file_type == $mp3_1)
			{

					if (isset($_FILES['upload']['name']) && !empty($_FILES['upload']['name'])) {
					
						$prefix = "file/";
					
						// if previous file, delete it
						if ($new_file == false) {
							$filename = $file->getFilenameOnFilestore();
							if (file_exists($filename)) {
								unlink($filename);
							}
					
							// use same filename on the disk - ensures thumbnails are overwritten
							$filestorename = $file->getFilename();
							$filestorename = elgg_substr($filestorename, elgg_strlen($prefix));
						} else {
							$filestorename = elgg_strtolower(time().$_FILES['upload']['name']);
						}
					
						$file->setFilename($prefix . $filestorename);
						$mime_type = ElggFile::detectMimeType($_FILES['upload']['tmp_name'], $_FILES['upload']['type']);
					
						// hack for Microsoft zipped formats
						$info = pathinfo($_FILES['upload']['name']);
						$office_formats = array('docx', 'xlsx', 'pptx');
						if ($mime_type == "application/zip" && in_array($info['extension'], $office_formats)) {
							switch ($info['extension']) {
								case 'docx':
									$mime_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
									break;
								case 'xlsx':
									$mime_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
									break;
								case 'pptx':
									$mime_type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
									break;
							}
						}
					
						// check for bad ppt detection
						if ($mime_type == "application/vnd.ms-office" && $info['extension'] == "ppt") {
							$mime_type = "application/vnd.ms-powerpoint";
						}
					
						$file->setMimeType($mime_type);
						$file->originalfilename = $_FILES['upload']['name'];
						$file->simpletype = file_get_simple_type($mime_type);
					
						// Open the file to guarantee the directory exists
						$file->open("write");
						$file->close();
						move_uploaded_file($_FILES['upload']['tmp_name'], $file->getFilenameOnFilestore());
					
						$guid = $file->save();
					
						// if image, we need to create thumbnails (this should be moved into a function)
						if ($guid && $file->simpletype == "image") {
							$file->icontime = time();
							
							$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
							if ($thumbnail) {
								$thumb = new ElggFile();
								$thumb->setMimeType($_FILES['upload']['type']);
					
								$thumb->setFilename($prefix."thumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbnail);
								$thumb->close();
					
								$file->thumbnail = $prefix."thumb".$filestorename;
								unset($thumbnail);
							}
					
							$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
							if ($thumbsmall) {
								$thumb->setFilename($prefix."smallthumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbsmall);
								$thumb->close();
								$file->smallthumb = $prefix."smallthumb".$filestorename;
								unset($thumbsmall);
							}
					
							$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
							if ($thumblarge) {
								$thumb->setFilename($prefix."largethumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumblarge);
								$thumb->close();
								$file->largethumb = $prefix."largethumb".$filestorename;
								unset($thumblarge);
							}
						}
					}
					
					
					else {
						// not saving a file but still need to save the entity to push attributes to database
						if ($check_file_type == 1)
						{
						$file->save();
						}
					}
					
								
					
							
							
							
							

			}
			
	}

if($filter_pdf == 1)
	{
		if ($check_file_type == $pdf_docs)
			{

					if (isset($_FILES['upload']['name']) && !empty($_FILES['upload']['name'])) {
					
						$prefix = "file/";
					
						// if previous file, delete it
						if ($new_file == false) {
							$filename = $file->getFilenameOnFilestore();
							if (file_exists($filename)) {
								unlink($filename);
							}
					
							// use same filename on the disk - ensures thumbnails are overwritten
							$filestorename = $file->getFilename();
							$filestorename = elgg_substr($filestorename, elgg_strlen($prefix));
						} else {
							$filestorename = elgg_strtolower(time().$_FILES['upload']['name']);
						}
					
						$file->setFilename($prefix . $filestorename);
						$mime_type = ElggFile::detectMimeType($_FILES['upload']['tmp_name'], $_FILES['upload']['type']);
					
						// hack for Microsoft zipped formats
						$info = pathinfo($_FILES['upload']['name']);
						$office_formats = array('docx', 'xlsx', 'pptx');
						if ($mime_type == "application/zip" && in_array($info['extension'], $office_formats)) {
							switch ($info['extension']) {
								case 'docx':
									$mime_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
									break;
								case 'xlsx':
									$mime_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
									break;
								case 'pptx':
									$mime_type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
									break;
							}
						}
					
						// check for bad ppt detection
						if ($mime_type == "application/vnd.ms-office" && $info['extension'] == "ppt") {
							$mime_type = "application/vnd.ms-powerpoint";
						}
					
						$file->setMimeType($mime_type);
						$file->originalfilename = $_FILES['upload']['name'];
						$file->simpletype = file_get_simple_type($mime_type);
					
						// Open the file to guarantee the directory exists
						$file->open("write");
						$file->close();
						move_uploaded_file($_FILES['upload']['tmp_name'], $file->getFilenameOnFilestore());
					
						$guid = $file->save();
					
						// if image, we need to create thumbnails (this should be moved into a function)
						if ($guid && $file->simpletype == "image") {
							$file->icontime = time();
							
							$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
							if ($thumbnail) {
								$thumb = new ElggFile();
								$thumb->setMimeType($_FILES['upload']['type']);
					
								$thumb->setFilename($prefix."thumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbnail);
								$thumb->close();
					
								$file->thumbnail = $prefix."thumb".$filestorename;
								unset($thumbnail);
							}
					
							$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
							if ($thumbsmall) {
								$thumb->setFilename($prefix."smallthumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbsmall);
								$thumb->close();
								$file->smallthumb = $prefix."smallthumb".$filestorename;
								unset($thumbsmall);
							}
					
							$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
							if ($thumblarge) {
								$thumb->setFilename($prefix."largethumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumblarge);
								$thumb->close();
								$file->largethumb = $prefix."largethumb".$filestorename;
								unset($thumblarge);
							}
						}
					}
					
					
					else {
						// not saving a file but still need to save the entity to push attributes to database
						if ($check_file_type == 1)
						{
						$file->save();
						}
					}
					
								
					
							
							
							
							

			}
			
	}




	
	if($filter_jpg == 1)
	{
		if ($check_file_type == $jpg)
			{
				
				
					if (isset($_FILES['upload']['name']) && !empty($_FILES['upload']['name'])) {
					
						$prefix = "file/";
					
						// if previous file, delete it
						if ($new_file == false) {
							$filename = $file->getFilenameOnFilestore();
							if (file_exists($filename)) {
								unlink($filename);
							}
					
							// use same filename on the disk - ensures thumbnails are overwritten
							$filestorename = $file->getFilename();
							$filestorename = elgg_substr($filestorename, elgg_strlen($prefix));
						} else {
							$filestorename = elgg_strtolower(time().$_FILES['upload']['name']);
						}
					
						$file->setFilename($prefix . $filestorename);
						$mime_type = ElggFile::detectMimeType($_FILES['upload']['tmp_name'], $_FILES['upload']['type']);
					
						// hack for Microsoft zipped formats
						$info = pathinfo($_FILES['upload']['name']);
						$office_formats = array('docx', 'xlsx', 'pptx');
						if ($mime_type == "application/zip" && in_array($info['extension'], $office_formats)) {
							switch ($info['extension']) {
								case 'docx':
									$mime_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
									break;
								case 'xlsx':
									$mime_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
									break;
								case 'pptx':
									$mime_type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
									break;
							}
						}
					
						// check for bad ppt detection
						if ($mime_type == "application/vnd.ms-office" && $info['extension'] == "ppt") {
							$mime_type = "application/vnd.ms-powerpoint";
						}
					
						$file->setMimeType($mime_type);
						$file->originalfilename = $_FILES['upload']['name'];
						$file->simpletype = file_get_simple_type($mime_type);
					
						// Open the file to guarantee the directory exists
						$file->open("write");
						$file->close();
						move_uploaded_file($_FILES['upload']['tmp_name'], $file->getFilenameOnFilestore());
					
						$guid = $file->save();
					
						// if image, we need to create thumbnails (this should be moved into a function)
						if ($guid && $file->simpletype == "image") {
							$file->icontime = time();
							
							$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
							if ($thumbnail) {
								$thumb = new ElggFile();
								$thumb->setMimeType($_FILES['upload']['type']);
					
								$thumb->setFilename($prefix."thumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbnail);
								$thumb->close();
					
								$file->thumbnail = $prefix."thumb".$filestorename;
								unset($thumbnail);
							}
					
							$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
							if ($thumbsmall) {
								$thumb->setFilename($prefix."smallthumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbsmall);
								$thumb->close();
								$file->smallthumb = $prefix."smallthumb".$filestorename;
								unset($thumbsmall);
							}
					
							$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
							if ($thumblarge) {
								$thumb->setFilename($prefix."largethumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumblarge);
								$thumb->close();
								$file->largethumb = $prefix."largethumb".$filestorename;
								unset($thumblarge);
							}
						}
					}
					
					
					else {
						// not saving a file but still need to save the entity to push attributes to database
						if ($check_file_type == 1)
						{
						$file->save();
						}
					}
									
	


			}
			
	}








if($filter_excel == 1)
	{
		if ($check_file_type == $excel)
			{
					if (isset($_FILES['upload']['name']) && !empty($_FILES['upload']['name'])) {
					
						$prefix = "file/";
					
						// if previous file, delete it
						if ($new_file == false) {
							$filename = $file->getFilenameOnFilestore();
							if (file_exists($filename)) {
								unlink($filename);
							}
					
							// use same filename on the disk - ensures thumbnails are overwritten
							$filestorename = $file->getFilename();
							$filestorename = elgg_substr($filestorename, elgg_strlen($prefix));
						} else {
							$filestorename = elgg_strtolower(time().$_FILES['upload']['name']);
						}
					
						$file->setFilename($prefix . $filestorename);
						$mime_type = ElggFile::detectMimeType($_FILES['upload']['tmp_name'], $_FILES['upload']['type']);
					
						// hack for Microsoft zipped formats
						$info = pathinfo($_FILES['upload']['name']);
						$office_formats = array('docx', 'xlsx', 'pptx');
						if ($mime_type == "application/zip" && in_array($info['extension'], $office_formats)) {
							switch ($info['extension']) {
								case 'docx':
									$mime_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
									break;
								case 'xlsx':
									$mime_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
									break;
								case 'pptx':
									$mime_type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
									break;
							}
						}
					
						// check for bad ppt detection
						if ($mime_type == "application/vnd.ms-office" && $info['extension'] == "ppt") {
							$mime_type = "application/vnd.ms-powerpoint";
						}
					
						$file->setMimeType($mime_type);
						$file->originalfilename = $_FILES['upload']['name'];
						$file->simpletype = file_get_simple_type($mime_type);
					
						// Open the file to guarantee the directory exists
						$file->open("write");
						$file->close();
						move_uploaded_file($_FILES['upload']['tmp_name'], $file->getFilenameOnFilestore());
					
						$guid = $file->save();
					
						// if image, we need to create thumbnails (this should be moved into a function)
						if ($guid && $file->simpletype == "image") {
							$file->icontime = time();
							
							$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
							if ($thumbnail) {
								$thumb = new ElggFile();
								$thumb->setMimeType($_FILES['upload']['type']);
					
								$thumb->setFilename($prefix."thumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbnail);
								$thumb->close();
					
								$file->thumbnail = $prefix."thumb".$filestorename;
								unset($thumbnail);
							}
					
							$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
							if ($thumbsmall) {
								$thumb->setFilename($prefix."smallthumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumbsmall);
								$thumb->close();
								$file->smallthumb = $prefix."smallthumb".$filestorename;
								unset($thumbsmall);
							}
					
							$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
							if ($thumblarge) {
								$thumb->setFilename($prefix."largethumb".$filestorename);
								$thumb->open("write");
								$thumb->write($thumblarge);
								$thumb->close();
								$file->largethumb = $prefix."largethumb".$filestorename;
								unset($thumblarge);
							}
						}
					}
					
					
					else {
						// not saving a file but still need to save the entity to push attributes to database
						if ($check_file_type == 1)
						{
						$file->save();
						}
					}

			
			}
			
	}
	
	
	
	
}	





if ($filter_enabled == 2 || $filter_enabled == 0)
{
// we have a file upload, so process it
if (isset($_FILES['upload']['name']) && !empty($_FILES['upload']['name'])) {

	$prefix = "file/";

	// if previous file, delete it
	if ($new_file == false) {
		$filename = $file->getFilenameOnFilestore();
		if (file_exists($filename)) {
			unlink($filename);
		}

		// use same filename on the disk - ensures thumbnails are overwritten
		$filestorename = $file->getFilename();
		$filestorename = elgg_substr($filestorename, elgg_strlen($prefix));
	} else {
		$filestorename = elgg_strtolower(time().$_FILES['upload']['name']);
	}

	$file->setFilename($prefix . $filestorename);
	$mime_type = ElggFile::detectMimeType($_FILES['upload']['tmp_name'], $_FILES['upload']['type']);

	// hack for Microsoft zipped formats
	$info = pathinfo($_FILES['upload']['name']);
	$office_formats = array('docx', 'xlsx', 'pptx');
	if ($mime_type == "application/zip" && in_array($info['extension'], $office_formats)) {
		switch ($info['extension']) {
			case 'docx':
				$mime_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
				break;
			case 'xlsx':
				$mime_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
				break;
			case 'pptx':
				$mime_type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
				break;
		}
	}

	// check for bad ppt detection
	if ($mime_type == "application/vnd.ms-office" && $info['extension'] == "ppt") {
		$mime_type = "application/vnd.ms-powerpoint";
	}

	$file->setMimeType($mime_type);
	$file->originalfilename = $_FILES['upload']['name'];
	$file->simpletype = file_get_simple_type($mime_type);

	// Open the file to guarantee the directory exists
	$file->open("write");
	$file->close();
	move_uploaded_file($_FILES['upload']['tmp_name'], $file->getFilenameOnFilestore());

	$guid = $file->save();

	// if image, we need to create thumbnails (this should be moved into a function)
	if ($guid && $file->simpletype == "image") {
		$file->icontime = time();
		
		$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
		if ($thumbnail) {
			$thumb = new ElggFile();
			$thumb->setMimeType($_FILES['upload']['type']);

			$thumb->setFilename($prefix."thumb".$filestorename);
			$thumb->open("write");
			$thumb->write($thumbnail);
			$thumb->close();

			$file->thumbnail = $prefix."thumb".$filestorename;
			unset($thumbnail);
		}

		$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
		if ($thumbsmall) {
			$thumb->setFilename($prefix."smallthumb".$filestorename);
			$thumb->open("write");
			$thumb->write($thumbsmall);
			$thumb->close();
			$file->smallthumb = $prefix."smallthumb".$filestorename;
			unset($thumbsmall);
		}

		$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
		if ($thumblarge) {
			$thumb->setFilename($prefix."largethumb".$filestorename);
			$thumb->open("write");
			$thumb->write($thumblarge);
			$thumb->close();
			$file->largethumb = $prefix."largethumb".$filestorename;
			unset($thumblarge);
		}
	}
}


else {
	// not saving a file but still need to save the entity to push attributes to database
	
	$file->save();
	
}


}
// file saved so clear sticky form
elgg_clear_sticky_form('file');





// handle results differently for new files and file updates
if ($new_file) {
	if ($guid) {
		$message = elgg_echo("file:saved");
		system_message($message);
		add_to_river('river/object/file/create', 'create', elgg_get_logged_in_user_guid(), $file->guid);
	} else {
		// failed to save file object - nothing we can do about this
		$error = elgg_echo("El tipo archivo que ha intentado subir no esta permitido. Por favor intente de nuevo.");
		register_error($error);
	}

	$container = get_entity($container_guid);
	if (elgg_instanceof($container, 'group')) {
		forward("file/group/$container->guid/all");
	} else {
		forward("file/owner/$container->username");
	}

} else {
	if ($guid) {
		system_message(elgg_echo("file:saved"));
	} else {
		register_error(elgg_echo("file:uploadfailed"));
	}

	forward($file->getURL());
}	
