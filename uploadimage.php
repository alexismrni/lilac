<?php
ini_set('display_errors', '0');

// CHMOD /srv/eyesofnetwork/nagios/share/images/logos AT 770 BEFORE UPLOAD
if(isset($_POST["submit"])) {
$target_dir = "../nagios/share/images/logos/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {

        $uploadOk = 1;
    } else {

        $uploadOk = 0;
        echo "<font color='red'>You don't choose a file!</font>  ";
        echo '<a href="uploadimage.php">Rééssayer</a>';
        exit;
    }
}
// Check if file already exists
if (file_exists($target_file)) {


    $name = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_FILENAME);
    $extension = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_EXTENSION);
    $increment = rand(1, 999);
    $basename = $name . $increment . '.' . $extension;
    $target_file = $target_dir . basename($basename);
}

// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "<font color='red'>Sorry, your file is too large.</font>";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "<font color='red'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</font>";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "<font color='red'>Sorry, your file was not uploaded.</font>";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "<font color='green'><b>The file ".$basename. " has been uploaded.</b></font>";
    } else {
        echo '<font color="red">Sorry, there was an error uploading your file. Try fix with "chmod -R 770 /srv/eyesofnetwork/nagios/share/images/logos" as root.</font>';
    }
}
}
?>

<!DOCTYPE html>
<html>
<head>
  <?php
if (isset($uploadOk)) {
  if ($uploadOk==1)
{ ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
function redirect34() {
    window.parent.location.href = this.window.parent.location;
}

$(document).ready(function(){
       setTimeout(redirect34, 5000);
});
</script>

<?php exit; } }

if (isset($uploadOk)) {
  if ($uploadOk==0)
{  echo '<a href="uploadimage.php">Rééssayer</a>'; exit; } }

?>
</head>
<body>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload Image" id="submit" name="submit">
</form>

</body>
</html>
