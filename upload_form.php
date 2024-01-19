<?php
// Define constants for upload directory, maximum file size, allowed file extensions, and allowed MIME types.
define('UPLOAD_DIRECTORY', '/var/php_uploaded_files/');
define('MAXSIZE', 5242880); // 5MB in bytes.
// Before PHP 5.6, we can't define arrays as constants.
$ALLOWED_EXTENSIONS = array('pdf', 'doc', 'docx', 'odt');
$ALLOWED_MIMES = array('application/pdf', // For .pdf files.
'application/msword', // For .doc files.
'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // For .docx ←-
'application/vnd.oasis.opendocument.text', // For .odt files.
);

// ----------------------------------------------------------------------------------- 
// * Checks if given file's extension and MIME are defined as allowed, which are defined in
// * array $ALLOWED_EXTENSIONS and $ALLOWED_MIMES, respectively.
// *
// * @param $uploadedTempFile The file that is has been uploaded already, from where the MIME
// * will be read.
// * @param $destFilePath The path that the dest file will have, from where the extension ←-
// will
// * be read.
// * @return True if file's extension and MIME are allowed; false if at least one of them is ←-
// not.
// ------------------------------------------------------------------------------------------

// Function to check if file's extension and MIME type are allowed.
function validFileType($uploadedTempFile, $destFilePath) {
global $ALLOWED_EXTENSIONS, $ALLOWED_MIMES;
$fileExtension = pathinfo($destFilePath, PATHINFO_EXTENSION);
$fileMime = mime_content_type($uploadedTempFile);
$validFileExtension = in_array($fileExtension, $ALLOWED_EXTENSIONS);
$validFileMime = in_array($fileMime, $ALLOWED_MIMES);
$validFileType = $validFileExtension && $validFileMime;
return $validFileType;
}

// -------------------------------------------------------------------------------------------
// * Handles the file upload, first, checking if the file we are going to deal with is ←-
// actually an
// * uploaded file; second, if file's size is smaller than specified; and third, if the file ←-
// is
// * a valid file (extension and MIME).
// *
// * @return Response with string of the result; if it has been successful or not.
// -------------------------------------------------------------------------------------------

// Function to handle file upload.
function handleUpload() {
$uploadedTempFile = $_FILES['file']['tmp_name'];
$filename = basename($_FILES['file']['name']);
$destFile = UPLOAD_DIRECTORY . $filename;
$isUploadedFile = is_uploaded_file($uploadedTempFile);
$validSize = $_FILES['file']['size'] <= MAXSIZE && $_FILES['file']['size'] >= 0;
if ($isUploadedFile && $validSize && validFileType($uploadedTempFile, $destFile)) {
$success = move_uploaded_file($uploadedTempFile, $destFile);
if ($success) {
$response = 'The file was uploaded successfully!';
} else {
$response = 'An unexpected error occurred; the file could not be uploaded.';
}
} else {
$response = 'Error: the file you tried to upload is not a valid file. Check file ←-
type and size.';
}
return $response;
}
// Flow starts here. Begin processing the form submission.
$validFormSubmission = !empty($_FILES);
if ($validFormSubmission) {
$error = $_FILES['file']['error'];
switch($error) {
case UPLOAD_ERR_OK:
$response = handleUpload();
break;
case UPLOAD_ERR_INI_SIZE:
$response = 'Error: file size is bigger than allowed.';
break;
case UPLOAD_ERR_PARTIAL:
$response = 'Error: the file was only partially uploaded.';
break;
case UPLOAD_ERR_NO_FILE:
$response = 'Error: no file could have been uploaded.';
break;
case UPLOAD_ERR_NO_TMP_DIR:
$response = 'Error: no temp directory! Contact the administrator.';
break;
case UPLOAD_ERR_CANT_WRITE:
$response = 'Error: it was not possible to write in the disk. Contact the ←-
administrator.';
break;
case UPLOAD_ERR_EXTENSION:
$response = 'Error: a PHP extension stopped the upload. Contact the ←-
administrator.';
break;
default:
$response = 'An unexpected error occurred; the file could not be uploaded.';
break;
}
} else {
$response = 'Error: the form was not submitted correctly - did you try to access the ←-
action url directly?';
}
echo $response;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <!--multipart/form-data is used to ensure encoding of characters by the form is prevented-->
    <form action="<?php htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" enctype="multipart/form-data">
        <label for="file">  Select a file to upload:</label>
        <input type="file" name="file" id="file" accept=".pdf, .odt, .doc, .docx">
        <input type="submit" value="Upload file!">
    </form>
</body>
</html>