<?php
include "config.php";

if (getSettings('credentials') == false || getSettings('credentials') == '') {
  die('Credentials is not set');
} elseif (getSettings('DriveToken') == false || getSettings('DriveToken') == '') {
  die('DriveToken is not set, visit /token.php to start.');
}

$client = new Google_Client();
$client->setApplicationName('nDrive');
$client->setScopes(Google_Service_Drive::DRIVE);
$client->setAuthConfig(json_decode(getSettings("credentials"), true));
$client->setAccessType('offline');

$accessToken = json_decode(getSettings("DriveToken"), true);
$client->setAccessToken($accessToken);

if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    $new_token = $client->getAccessToken();
    $accessToken = json_encode(array_merge($accessToken, $new_token));
    setSettings("DriveToken", $accessToken);
}

$service = new Google_Service_Drive($client);

$noHTML = false;

if (isset($_POST['fid']) && isset($_POST['fname']) && isset($_POST['ftype']) && isset($_POST['fsize'])) {
  error_reporting(0);
  header('Content-Type: application/json');
  $success = false;
  $return = [];

  $noHTML = true;
  $file_id = $_POST['fid'];
  $file_name = $_POST['fname'];
  $mime_type = $_POST['ftype'];
  $file_size = $_POST['fsize'];

  $ext = pathinfo($file_name, PATHINFO_EXTENSION);

  $copiedFile = new Google_Service_Drive_DriveFile();
  $copiedFile->setName($file_name);
  $copiedFile->setMimeType($mime_type);
  try {
      $return_file = $service->files->copy($file_id, $copiedFile);
      $driveID = $return_file->id;
      $return_file->setMimeType($mime_type);
      $newPermission = new Google_Service_Drive_Permission();
      $newPermission->setType('anyone');
      $newPermission->setRole('reader');
      $service->permissions->create($driveID, $newPermission);
      $service->files->delete($file_id);
  } catch (Exception $e) {
      $return['msg'] = "An error occurred: " . $e->getMessage();
  }
  $return['url'] = 'https://drive.google.com/open?id='.$driveID;
  $success = true;
  
  $return['success'] = $success;
  echo json_encode($return);
}
if (!$noHTML): 
?><!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
	<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css" />
	<link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <div class="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>nDrive</h3>
            </div>
            <ul class="list-unstyled components">
                <li class="active">
                    <a href="/" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="glyphicon glyphicon-home"></i> Home</a>
                </li>
                <li>
                    <a href="https://nstudio.pw/donate/"><i class="glyphicon glyphicon-pencil"></i> Donate me</a>
                </li>
                <li>
                    <a href="https://nstudio.pw"><i class="glyphicon glyphicon-user"></i> Author</a>
                </li>
            </ul>
        </nav>
    
        <!-- Page Content -->
        <div id="content">
            <div class="container" id="container">
        		<h1><i class="glyphicon glyphicon-cloud"></i> nDrive</h1>
        		<h2 class="lead">Upload without limits</h2>
        		<blockquote>
        			<p>
        				Drag files to anywhere on this page to upload.
        			</p>
        		</blockquote>
        		<div id="actions" class="row">
        			<div class="col-lg-7">
        				<span class="btn btn-success fileinput-button">
        					<i class="glyphicon glyphicon-plus"></i>
        					<span>Add files...</span>
        				</span>
        				<button type="submit" class="btn btn-primary start">
        					<i class="glyphicon glyphicon-upload"></i>
        					<span>Start upload</span>
        				</button>
        				<button type="reset" class="btn btn-warning cancel">
        					<i class="glyphicon glyphicon-ban-circle"></i>
        					<span>Cancel upload</span>
        				</button>
        			</div>
        
        			<div class="col-lg-5">
        				<!-- The global file processing state -->
        				<span class="fileupload-process">
        					<div id="total-progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        						<div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
        					</div>
        				</span>
        			</div>
        
        		</div>
        		<div class="table table-striped files" id="previews">
        
        			<div id="template" class="file-row">
        				<!-- This is used as the file preview template -->
        				<div>
        					<span class="preview"><img data-dz-thumbnail /></span>
        				</div>
        				<div>
        					<p class="name" data-dz-name></p>
        					<strong class="error text-danger" data-dz-errormessage></strong>
        				</div>
        				<div>
        					<p class="size" data-dz-size></p>
        					<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        						<div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
        					</div>
        				</div>
        				<div>
        					<button class="btn btn-primary start">
        						<i class="glyphicon glyphicon-upload"></i>
        						<span>Start</span>
        					</button>
        					<a class="btn btn-success link">
        						<i class="glyphicon glyphicon-link"></i>
        						<span>Link</span>
        					</a>
        					<button data-dz-remove class="btn btn-warning cancel">
        						<i class="glyphicon glyphicon-ban-circle"></i>
        						<span>Cancel</span>
        					</button>
        					<button data-dz-remove class="btn btn-danger delete">
        						<i class="glyphicon glyphicon-trash"></i>
        						<span>Delete</span>
        					</button>
        				</div>
        			</div>
        
        		</div>
        	</div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>
    
    <script>
        Dropzone.autoDiscover = false;
    	// Get the template HTML and remove it from the doument
    	var previewNode = document.querySelector("#template");
    	previewNode.id = "";
    	var previewTemplate = previewNode.parentNode.innerHTML;
    	previewNode.parentNode.removeChild(previewNode);
    
    	var myDropzone = new Dropzone(document.body, {
            timeout: 36000000,
            url: 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id',
    		thumbnailWidth: 80,
    		thumbnailHeight: 80,
    		parallelUploads: 20,
    		previewTemplate: previewTemplate,
    		autoQueue: false, // Make sure the files aren't queued until manually added
    		previewsContainer: "#previews", // Define the container to display the previews
    		clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
    	});
    
    	myDropzone.on("addedfile", function(file) {
    		this.options.headers = {
    			'Authorization': 'Bearer <?=$accessToken['access_token']?>',
    			"Content-Type": "application/json",
    			"X-Upload-Content-Length": file.size,
    			"X-Upload-Content-Type": file.type
    		};
        $(file.previewElement.querySelector(".link")).hide();
    		file.previewElement.querySelector(".start").onclick = function() {
    			myDropzone.enqueueFile(file);
    		};
    	});
    	
    	myDropzone.on("totaluploadprogress", function(progress) {
    		document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
    	});
    
    	myDropzone.on("sending", function (file, xhr, formData) {
    	    var _send = xhr.send;
            xhr.send = function() {
                _send.call(xhr, file);
            }
    		document.querySelector("#total-progress").style.opacity = "1";
    		file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
    	});
    	
    	myDropzone.on("complete", function(file) {
        console.log(file);
    	    $(file.previewElement.querySelector(".delete")).hide();
    		if (file.status === 'success') {
    		    var xhr_data = JSON.parse(file.xhr.response);
        		var file_id = xhr_data['id'];
    		    $.post('index.php', {
    		        fid  : file_id,
    		        fname: file.name,
    		        ftype: file.type,
    		        fsize: file.size
    		    }).done(function (ret) {
                    console.log(ret);
                    if (ret.success) {
                        $(file.previewElement.querySelector(".link")).show();
                        $(file.previewElement.querySelector(".link")).attr('href', ret.url);
                    } else {
                        $(file.previewElement.querySelector(".delete")).show();
                        $(file.previewElement.querySelector(".error")).text(ret.msg);
                    }
    		    });
    		}
    	});
    
    	myDropzone.on("queuecomplete", function(progress) {
    		document.querySelector("#total-progress").style.opacity = "0";
    	});
    	
    	document.querySelector("#actions .start").onclick = function() {
    		myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
    	};
    	
    	document.querySelector("#actions .cancel").onclick = function() {
    		myDropzone.removeAllFiles(true);
    	};
    </script>

</body>
</html>
<?php endif; ?>
