<?php
  /* --------------------------- UASE LICENSE --------------------------

  The MIT License (MIT)

  Copyright (c) 2015 Seth Vandebrooke

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
  */

  #-----------CONFIG----------
  
  $GLOBALS['username_field'] = "username"; //The name of the field where you will store usernames
  $GLOBALS['email_field'] = "email"; //The name of the field where you will store the user's email
  $GLOBALS['password_field'] = "password"; //The name of the field where you will store the user's password
  $GLOBALS['user_table'] = "users"; //Database Table for user data e.g. username, email, password, firstname, lastname...
  $servername = "SERVERNAME"; //Servername !MUST CHANGE!
  $username = "USERNAME"; //Database username !MUST CHANGE!
  $password = "PASSWORD"; //Database password !MUSTCHANGE!
  $dbname = "DATABASE"; //DataBase name !MUST CHANGE!
  session_save_path("./tmp"); //Set the path to store the session files

  #-----------^CONFIG^--------
  
 //-----------------------------------------------------------------------------------------
  #   EXAMPLE CODE
  //-----------------------------------------------------------------------------------------
  /*
  # Signing up
  if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $validate = SignUpValidate("username,email,name",$username.",".$email.",".$name,$password,$username,$email);
    if ($validate=="Go") {
       SignUp("username,email,name","'".$username."','".$email."','".$name."'",$password);
       RememberUsername($username);
       header("Location: main.php");
    } else {
      echo $validate;
    }
  }
  # Loging in
  if (isset($_POST['login'])) {
    if (CheckConnection()=="Connection Established!") { // Check database connection
      if (CheckLogin($_POST['username'], $_POST['password'])==TRUE) { // Authenticate user login
        Header("Location: main.php"); // Redirect to page with successful login
      } else {
        echo '<div class="alert alert-danger">Sorry! That username and password do not match :(</div>'; // If the login is not successful notify the user
      }
    }
  }
  # Displaying user profile picture
  < img href="<?php echo GravatarPicture(GrabUsername(),"200"); ?>" alt="Profile picture" />
  # Displaying username
  echo GrabUsername();
  # Changing the user's password
  if (isset($_POST['submit_pass'])) {
    ChangePassword($user,$_POST['old-password'],$_POST['new-password'],$_POST['confirm-new-password']);
  }
  */

  #MUST RUN --- Do not change this section!
  session_start();
  $conn = new mysqli($servername, $username, $password, $dbname);
  mysql_connect($servername, $username, $password, $dbname);
  mysql_select_db($dbname) or die("Couldn't select DB"); 
  #MUST RUN ^

  # SYNTAX CheckConnection(); !NO_PARAMETERS!
  # The CheckConnection function checks the database connection
  function CheckConnection() {
      if ($conn->connect_error) {
            return $conn->connect_error;
        } 
        else {
            return "Connection Established!";
        }
  }
  # SYNTAX HashIt("STRING");
  # The HashIt function hashes the specified string
  function HashIt($string) {
    return hash("sha256", $string);
  }
  # SYNTAX SQLInjectionProtection("STRING");
  # The SQLInjectionProtection function intakes the specified string, removes any wild characters to prevent SQL injection, and returns the clean string
  function SQLInjectionProtection($string) {
    $str = preg_replace('#[^A-Za-z0-9]#i','',$str);
    return $str;
  }
  # SYNTAX RememberUsername("USERNAME");
  # The RememberUsername function stores the speficied username as a session variable for the later use of the GrabUsername function
  function RememberUsername($user_login) {
    $_SESSION["user_login"] = $user_login;
  }
  # SYNTAX GrabUsername();
  # The GrabeUsername function returns the name of the logged in user
  function GrabUsername() {
    return $_SESSION["user_login"];
  }
  # SYNTAX CheckLogin("USERNAME","PASSWORD");
  # The CheckLogin function intakes the parameters and returns TRUE if the login credentials are accepted and FALSE if not
  function CheckLogin($username,$upassword) {
    $username = strip_tags($username);
    $upassword = strip_tags($upassword);
    $table = $GLOBALS['user_table'];
    $userfield = $GLOBALS['username_field'];
    $passfield = $GLOBALS['password_field'];
    $upassword = HashIt($upassword);
    $sql = mysql_query("SELECT id FROM $table WHERE $userfield='$username' AND $passfield='$upassword' LIMIT 1") or die(mysql_error());
    $userCount = mysql_num_rows($sql);
    if ($userCount == 1) { // If a user was found
      session_start();
      RememberUsername($username);
      return TRUE;
      exit();
    } else {
      return FALSE;
      exit();
    }
  }
  # SYNTAX GravatarPicture("USERNAME","PICTURE_SIZE");
  # The GravatarPicture function grabs the profile picture for the specified user in the specified size from their gravatar account if they have one. If they do not then it will result in the default Gravatar picture.
  function GravatarPicture($username,$size) {
        $Table_User = $GLOBALS['user_table'];
        $username = strip_tags($username);
        $sql = mysql_query("SELECT email FROM $Table_User WHERE username='$username'") or die(mysql_error());
        while($row = mysql_fetch_array($sql)){$user_email = $row["email"];}
        return "http://www.gravatar.com/avatar/" . md5(strtolower(trim("$user_email"))) . "?s=" . $size;
    }
  # SYNTAX SingUpValidate("FIELD,FIELD,FIELD","VALUE,VALUE,VALUE","PASSWORD","USERNAME","EMAIL");
  # The SingUpValidate function checks to see if any form fields were left empy and weather or not the email/username are already in use
  function SignUpValidate($fields,$values,$pass,$username,$email) {
    $fields = strip_tags($fields);
    $values = strip_tags($values);
    $pass = strip_tags($pass);
    $username = strip_tags($username);
    $email = strip_tags($email);
    $Table_User = $GLOBALS['user_table'];
    $userfield = $GLOBALS['username_field'];
    $passfield = $GLOBALS['password_field'];
    $emailfield = $GLOBALS['email_field'];
    if (empty($pass)) {
      return "password is empty";
      exit();
    }
    if (empty($passfield)) {
      return "password field empty";
      exit();
    }
    $fields_array = explode(",", $fields);
    $values_array = explode(",", $values);
    if (count($fields_array)!=count($values_array)) {
      return "Please leave no field empty!";
      exit();
    }
    $check_u = mysql_query("SELECT $userfield FROM $Table_User WHERE $userfield='$username'") or die(mysql_error());
      $check_u2 = mysql_num_rows($check_u);
      $check_e = mysql_query("SELECT $emailfield FROM $Table_User WHERE $emailfield='$email'") or die(mysql_error());
      $check_e2 = mysql_num_rows($check_e);
      if (!$check_u2==0) {
        return "That username already exists... Sorry :(";
      } elseif (!$check_e2==0) {
        return "Sorry... That email has already been used :(";
      } else {
        return "Go";
      }
  }
  # SYNTAX SignUp("COLUMN,COLUMN,COLUMN","'".$username."','".$password."','".$email."'","PASSWORD");
  # The SignUp function intakes the parameters and inserts the values to the specified table
  # The password value is entered separately from the other values so that it may be properly encrypted before stored
  function SignUp($fields,$values,$pass) {
    $fields = strip_tags($fields);
    $values = strip_tags($values);
    $pass = strip_tags($pass);
    if (CheckConnection()=="Connection Established!") {
      $table = $GLOBALS['user_table'];
      $userfield = $GLOBALS['username_field'];
      $passfield = $GLOBALS['password_field'];
      $columns = $fields.",".$passfield;
      $columns = SQLInjectionProtection($columns);
      $totalvalues = $values.",'".HashIt($pass)."'";
      $sql = mysql_query("INSERT INTO $table ($fields,$passfield) VALUES ($totalvalues)") or die(mysql_error());
    } else {echo "Failed database connection";}
  }
  # SYNTAX Logout("./index.php");
  # The Logout function destroys the session data variables and redirects the user to the desired page
  function Logout($page) {
    RememberUsername("");
    session_destroy();
    header("location: ".$page);
  }
  # SYNTAX GrabUsersEmail("USERNAME");
  # The GrabUsersEmail function acquires the corresponding email of the specified user
  function GrabUsersEmail($user) {
    $user = strip_tags($user);
    $Table_User = $GLOBALS['user_table'];
    $userfield = $GLOBALS['username_field'];
    $sql = mysql_query("SELECT email FROM $Table_User WHERE $userfield='$user'") or die(mysql_error());
    while($row = mysql_fetch_array($sql)){$user_email = $row["email"];}
    return $user_email;
  }
  # SYNTAX GrabData("USERNAME","FIELD_NAME");
  # The GrabData function acquires the value of a field corresponding to the specified email
  # EXAMPLE: GrabData("Joe","firstname"); would grab the value from the "firstname" field in the database that corresponds to the username Joe
  function GrabData($user,$data) {
    $user = strip_tags($user);
    $data = strip_tags($data);
    $Table_User = $GLOBALS['user_table'];
    $userfield = $GLOBALS['username_field'];
    $sql = mysql_query("SELECT $data FROM $Table_User WHERE $userfield='$user'") or die(mysql_error());
    while($row = mysql_fetch_array($sql)){$output = $row[$data];}
    return $output;
  }
  # SYNTAX ChangePassword("USERNAME","OLD_PASSWORD","NEW_PASSWORD","CONFIRM_NEW_PASSWORD");
  #   The ChangePassword function changes the password for the specified user
  function ChangePassword($user,$oldpass,$newpass,$passcon) {
    $fields = strip_tags($fields);
    $fields = strip_tags($fields);
    $fields = strip_tags($fields);
    $fields = strip_tags($fields);
    $Table = $GLOBALS['user_table'];
    $userfield = $GLOBALS['username_field'];
    $passfield = $GLOBALS['password_field'];
    $password_query = mysql_query("SELECT * FROM $Table WHERE $userfield='$user'") or die(mysql_error());
    while ($row = mysql_fetch_assoc($password_query)) {
      $db_password = $row[$passfield];
      if (HashIt($oldpass)==$db_password) { 
        if ($newpass==$passcon) { 
          $passinsert=HashIt($newpass);
          $PUQ = mysql_query("UPDATE $Table SET $passfield='$passinsert' WHERE $userfield='$user'") or die(mysql_error());
          echo "Success! Your password has been changed!";
        } else { echo "Your confirming password does not match your new password!";}
      } else { echo "The old password that you entered is incorrect!";}
    }
  }
  # SYNTAX UpdateField("USERNAME","VALUE","FIELD");
  # The UpdateField function updates the specified field with the given value in accordance to the specified user
  function UpdateField($user,$value,$field) {
    $user = strip_tags($user);
    $value = strip_tags($value);
    $field = strip_tags($field);
    $Table = $GLOBALS['user_table'];
    $userfield = $GLOBALS['username_field'];
    $sql = mysql_query("UPDATE $Table SET $field='$value' WHERE $userfield='$user'") or die(mysql_error());
  }
  #
  class user {
    public $name, $data;
    private $arra, $i;
    public function __construct($name,$data) {
      $this -> name = $name;
      $this -> data['name'] = $name;
      $data = explode(",",$data);
      for ($i=0; $i < count($data); $i++) { 
        $this -> $data[$i] = GrabData($name,$data[$i]);
      }
    }
  }
?>
