<?php

function connect(){
    $HOST = $_ENV['HOST'];
    $PORT = $_ENV['PORT'];
    $DB_NAME = $_ENV['DB_NAME'];
    $USERNAME = $_ENV['USERNAME'];
    $PASSWORD = $_ENV['PASSWORD'];

    // create connection
    $conn = new mysqli($HOST, $USERNAME, $PASSWORD, $DB_NAME, $PORT);

    // check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
      return $conn;
}

function register(string $email, string $password){
    $hpassword = md5($password);
    $conn = connect();

    // prepare, bind and execute
    $sql = "INSERT INTO users (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $hpassword);
    $stmt->execute();
    $stmt->close();

    // log in automatically
    $retval = login($email, $password);

    // close connection
    $conn->close();
    return $retval;
}

class User {
    public $email;
    public $fname;
    public $lname;

    function __construct(){
        $conn = connect();
        $sess_id = session_id();
        $sql = "SELECT * FROM users where sesskey = '$sess_id' LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $this->fname = $row['fname'];
            $this->lname = $row['lname'];
            $this->email = $row['email'];
        }

        // close connection
        $conn->close();
    }

    static function get_user_detail($column){
        $conn = connect();
        $sess = session_id();
        $sql = "SELECT * FROM users where sesskey = '$sess' LIMIT 1";
        $result = $conn->query($sql);
    
        if ($result->num_rows == 0){
            $retval = null;
        }else{
            $row = $result->fetch_assoc();
            $retval = $row[$column];
        }
        // close connection
        $conn->close();
    
        return $retval;
    }
    

}

function login(string $email, string $password){
    $password = md5($password);
    $conn = connect();
    $sql = "SELECT * FROM users where email = '$email' and password = '$password' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows == 0){
        // close connection
        $conn->close();
        return false;
    }else{
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        // set a sesssion id
        $sess_id = uniqid();
        session_id($sess_id);
        // Start the session
        session_start();
        // update user
        $sql = "UPDATE users SET sesskey=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $sess_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // close connection
    $conn->close();
    return true;
}

function upload($file):array{
    $target_dir = "media/";
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        // rename file
        $name = pathinfo($target_file,PATHINFO_FILENAME);
        $num = rand();
        $target_file = $target_dir . $name . $num . strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $uploadOk = 1;
    }

    // Check file size
    if ($file["size"] > 5000000) {
        $error =  "Sorry, your file is too large. 5MB max";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        return [false, $error];
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $error = "The file ". htmlspecialchars( basename($file["name"])). " has been uploaded.";
            return [true, $target_file];
        } else {
            $error = "Sorry, there was an error uploading your file.";
            return [false, $error];
        }
    }

}

function ops() {
    $conn = connect();
    // make upload
    $file = upload($_FILES['file']);
    if (!$file[0]){
        return $file[1];
    }
    // prepare, bind and execute
    $sql = "INSERT INTO ops (title, surname, onames, dob, status, sex, phone, address, lga, ward, state, occupation, adi, ami, 
    aai, disablity, edu_status, bg, genotype, hypertension, diabetes, epilepsy, asthma, tb, glaucoma, kd, hiv, sc, cs, 
    medical_condition, nok_fname, nok_phone, nok_address, facilty_code, facilty_name, health_plan, amount, condition, 
    hospital, hmo, about_us, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, )";
    $stmt = $conn->prepare($sql);
    $title = $_POST['title'];
    $surname = $_POST['surname'];
    $onames = $_POST['onames'];
    $status = $_POST['status'];
    $sex = $_POST['sex'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $lga = $_POST['lga'];
    $ward = $_POST['ward'];
    $state = $_POST['state'];
    $occupation = $_POST['occupation'];
    $adi = $_POST['adi'];
    $ami = $_POST['ami'];
    $aai = $_POST['aai'];
    $disablity = $_POST['disablity'];
    $edu_status = $_POST['edu_status'];
    $bg = $_POST['bg'];
    $genotype = $_POST['genotype'];
    $hypertension = $_POST['tihypertensiontle'];
    $diabetes = $_POST['diabetes'];
    $epilepsy = $_POST['epilepsy'];
    $asthma = $_POST['asthma'];
    $tb = $_POST['tb'];
    $glaucoma = $_POST['glaucoma'];
    $kd = $_POST['kd'];
    $hiv = $_POST['hiv'];
    $sc = $_POST['sc'];
    $cs = $_POST['cs'];
    $medical_condition = $_POST['medical_condition'];
    $nok_fname = $_POST['nok_fname'];
    $nok_phone = $_POST['nok_phone'];
    $nok_address = $_POST['nok_address'];
    $facilty_code = $_POST['facilty_code'];
    $facilty_name = $_POST['facilty_name'];
    $health_plan = $_POST['health_plan'];
    $amount = $_POST['amount'];
    $condition = $_POST['condition'];
    $hospital = $_POST['hospital'];
    $hmo = $_POST['hmo'];
    $about_us = $_POST['about_us'];
    $user_id = get_user_detail('id');
    $stmt->bind_param("sssssssssssssssssssssssssssssssssssssssssi", $title, $surname, $onames, $status, $sex, $phone, $address, $lga, $ward, $state, $occupation, $adi, 
    $ami, $aai, $disablity, $edu_status, $bg, $genotype, $hypertension, $diabetes, $epilepsy, $asthma, $tb, $glaucoma, $kd, $hiv, 
    $sc, $cs, $medical_condition, $nok_fname, $nok_phone, $nok_address, $facilty_code, $facilty_name, $health_plan, $amount, 
    $condition, $hospital, $hmo, $about_us, $user_id);
    $stmt->execute();
    $stmt->close();
}

function bhp() {
    $conn = connect();
    // make upload
    $file = upload($_FILES['file']);
    if (!$file[0]){
        return $file[1];
    }
    // prepare, bind and execute
    $sql = "INSERT INTO ops (title, surname, onames, dob, status, sex, phone, address, lga, ward, state, occupation, adi, ami, 
    aai, disablity, edu_status, bg, genotype, hypertension, diabetes, epilepsy, asthma, tb, glaucoma, kd, hiv, sc, cs, 
    medical_condition, nok_fname, nok_phone, nok_address, facilty_code, facilty_name, nin, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
    ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $title = $_POST['title'];
    $surname = $_POST['surname'];
    $onames = $_POST['onames'];
    $status = $_POST['status'];
    $sex = $_POST['sex'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $lga = $_POST['lga'];
    $ward = $_POST['ward'];
    $state = $_POST['state'];
    $occupation = $_POST['occupation'];
    $adi = $_POST['adi'];
    $ami = $_POST['ami'];
    $aai = $_POST['aai'];
    $disablity = $_POST['disablity'];
    $edu_status = $_POST['edu_status'];
    $bg = $_POST['bg'];
    $genotype = $_POST['genotype'];
    $hypertension = $_POST['tihypertensiontle'];
    $diabetes = $_POST['diabetes'];
    $epilepsy = $_POST['epilepsy'];
    $asthma = $_POST['asthma'];
    $tb = $_POST['tb'];
    $glaucoma = $_POST['glaucoma'];
    $kd = $_POST['kd'];
    $hiv = $_POST['hiv'];
    $sc = $_POST['sc'];
    $cs = $_POST['cs'];
    $medical_condition = $_POST['medical_condition'];
    $nok_fname = $_POST['nok_fname'];
    $nok_phone = $_POST['nok_phone'];
    $nok_address = $_POST['nok_address'];
    $facilty_code = $_POST['facilty_code'];
    $facilty_name = $_POST['facilty_name'];
    $nin = $_POST['nin'];
    $user_id = get_user_detail('id');
    $stmt->bind_param("ssssssssssssssssssssssssssssssssssssi", $title, $surname, $onames, $status, $sex, $phone, $address, $lga, $ward, $state, $occupation, $adi, 
    $ami, $aai, $disablity, $edu_status, $bg, $genotype, $hypertension, $diabetes, $epilepsy, $asthma, $tb, $glaucoma, $kd, $hiv, 
    $sc, $cs, $medical_condition, $nok_fname, $nok_phone, $nok_address, $facilty_code, $facilty_name, $nin, $user_id);
    $stmt->execute();
    $stmt->close();
}

function get_user_detail($column){
    $conn = connect();
    $sess = session_id();
    $sql = "SELECT * FROM users where sesskey = '$sess' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows == 0){
        $retval = null;
        // redirect to login page
        header("Location: /login.php");
        exit;
    }else{
        $row = $result->fetch_assoc();
        $retval = $row[$column];
    }
    // close connection
    $conn->close();

    return $retval;
}

function logout(){
    // remove all session variables
    session_unset();

    // destroy the session
    session_destroy();

    // redirect
    header("Location: /login.php");
    exit;
}
