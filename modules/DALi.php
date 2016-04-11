<?php
if ( !class_exists( 'DALi' ) ) {
  class DALi {

    function __construct($config) {
      $this->conf = $config;
    }

    //------------- General --------------->
    private function dbconnect() {
      return new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DB);
    }

    public function query($query) {
      $db = $this->dbconnect();
      if($this->checkDbConnect($db)) {
        echo false;
      }
      $result = $db->query($query);
      if(!$result) {
        echo "False";
      }
      while ($row = $result->fetch_array() ) {
        $results[] = $row;
      }
      $result->free();
      $db->close();
      return $results;
    }

    private function queryChange($query) {
      $db = $this->dbconnect();
      if($this->checkDbConnect($db)) {
        return false;
      }
      $db->query($query);
      $db->close();
    }

    private function checkDbConnect($conn) {
      if($conn->connect_errno > 0) {
        die ('Unable to connect to database [' . $conn->connect_error . ']');
      }
    }
    public function DoesThisExist($sql) {
      $db = $this->dbconnect();
      $result = $db->query($sql);
      $count = 0;
      foreach ($result as $key => $value) { //because I can't for the life of me get num_rows working
        $count += 1;
      }
      if ($count == 0) {
        return true; //which means no
      } else {
        return false;
      }
    }

    //---------QR Code Generation---------->
    public function getQRCode() {
      $link = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
      return "<img src='https://chart.googleapis.com/chart?cht=qr&chl=$link&chs=150x150' width='120' alt='qr-mobile' />";
    }

    //--------General Functions------------>
    public function checkUsernameExists($who) {
      $sql = "SELECT id FROM users WHERE username = '$who' LIMIT 1";
      return $this->DoesThisExist($sql);
    }

    //--------End-User Functions----------->
    // Getter functions
    private function getTitleInfo($title) {
        $sql = "SELECT * FROM sub_category WHERE id = '$title'";
        return $this->query($sql);
    }

    private function getStatus($id) {
        $sql = "SELECT status FROM sr_status WHERE id = '$id'";
        return $this->query($sql);
    }

    public function getBuildingsRow($request) {
   	  if($request == 'all') {
	      $sql = "SELECT * FROM building";
	      return $this->query($sql);
  	  }
    }

    private function getCategories() {
      $sql = "SELECT * FROM category ORDER BY `cat` ASC";
      return $this->query($sql);
    }

    private function getCategoryById($id) {
       $sql = "SELECT * FROM category WHERE id='$id'";
       return $this->query($sql);
    }

    private function getTitleNumber($title) {
      $sql = "SELECT id FROM sub_category WHERE sub_cat = '$title' LIMIT 1";
      $result = $this->query($sql);
      foreach ($result as $res) {
        $tn = $res[0];
      }
      return $tn;
    }

    private function getRecordType($title) {
      $sql = "SELECT type FROM sub_category WHERE sub_cat = '$title' LIMIT 1";
      $result = $this->query($sql);
      foreach ($result as $res) {
        $rt = $res[0];
      }
      return $rt;
    }

    // Builder Functions
    public function buildCategorySections() {
      $cats = $this->getCategories();
      $num = 1;

      foreach ($cats as $category) {
        if ($num == 1) {
          $build = "<li class='active'><a href='#$num' data-toggle='tab'>$num. $category[1]</a></li>\r\n";
        } else {
          $build .= "\t\t<li><a href='#$num' data-toggle='tab'>$num. $category[1]</a></li>\r\n";
        }
        $num++;
      }

      return $build;
    }

    public function buildSubCategorySections() {
      $sql = "SELECT category.cat, sub_category.sub_cat, sub_category.description, sub_category.fa_icon, cat_color.color FROM sub_category INNER JOIN category ON category.id = sub_category.cat INNER JOIN cat_color ON sub_category.color = cat_color.id ORDER BY cat ASC, sub_cat ASC";
      $subcats = $this->query($sql);
      $cats = array();
      $subsec = " ";
      $count = 1;
      foreach ($subcats as $row) {
          if (!in_array($row['cat'], $cats)) {
            $cat      = $row['cat'];
            array_push($cats, $cat);
            if ($count == 1) {
              $subsec .= "<!-- $cat -->\r\n\t\t<div class='tab-pane active' id='$count'>\r\n";
              $count += 1;
            } else {
              $subsec .= "\t\t<!-- $cat -->\r\n\t\t<div class='tab-pane' id='$count'>\r\n";
              $count += 1;
            }
            foreach ($subcats as $sub) {
              if ($cat == $sub['cat']) {
                $sub_cat  = $sub[1];
                $desc     = $sub[2];
                $fonticon = $sub[3];
                $color    = $sub[4];
                $subsec .= "\t\t<h4><i class='fa fa-$fonticon fa-2x pull-left align m-$color'></i> <a class='tab_value' data-toggle='modal' data-target='#incidentModal' data-title='$sub_cat' name='submit-value' href='#'>$sub_cat</a><br />".
                  "<small>$desc</small></h4>\r\n";
              }
            }
            $subsec .= "</div>\r\n\r\n";
          }
      }

      return $subsec;
    }

    public function buildMobileCategoryAccordion() {
      $sql = "SELECT category.cat, sub_category.sub_cat, sub_category.description, sub_category.fa_icon, cat_color.color FROM sub_category INNER JOIN category ON category.id = sub_category.cat INNER JOIN cat_color ON sub_category.color = cat_color.id ORDER BY cat ASC, sub_cat ASC";
      $subcats = $this->query($sql);
      $cats = array();
      $subsec = " ";
      $count = 1;
      foreach ($subcats as $row) {
          if (!in_array($row['cat'], $cats)) {
            $cat      = $row['cat'];
            array_push($cats, $cat);
            $subsec .= "\t<!-- $cat -->\r\n\t<h3><a href=''>$count. $cat</a></h3>\r\n\t\r<div>";
            $count += 1;
            foreach ($subcats as $sub) {
              if ($cat == $sub['cat']) {
                $sub_cat  = $sub[1];
                $desc     = $sub[2];
                $fonticon = $sub[3];
                $color    = $sub[4];
                $subsec .= "\t<h4><i class='fa fa-$fonticon fa-2x pull-left align m-$color'></i> <div class='mobile-align'><a class='tab_value' data-toggle='modal' data-target='#incidentModal' data-title='$sub_cat' href='#'>$sub_cat</a><br />".
                  "\t<small>$desc</small></div></h4>\r\n\r\n";
              }
            }
            $subsec .= "\t\r\n\r\n</div>";
          }
      }

      return $subsec;
    }

    // SR Functions
    public function buildRequestsTable($username) {
      $user = $this->getUserID($username);
      $sql = "SELECT sr_id, title, status_id, submitted_when, assigned_admin, last_updated
              FROM service_record
              WHERE submitted_by = '$user'";
      $html = "";
      $result = $this->query($sql);
      foreach ($result as $res) {
          $title_info = $this->getTitleInfo($res[1]);
          $category = $this->getCategoryById($title_info[0][2])[0][1];
          $status = $this->getStatus($res[2])[0][0];

          $html .= '<tr data-href="?page=ViewRequests&sr='. $res[0] .'">';
          $html .= '<td>'. $res[0] . '</td>'
                . '<td class="mobile-table">' . $category . '</td>'
                . '<td>'. $status .'</td>'
                . '<td>'. $title_info[0][3] .'</td>'
                . '<td class="mobile-table">'. $res[4] .'</td>'
                . '<td class="mobile-table">'. $res[3] .'</td>'
                . '<td>'. $res[5] .'</td>'
                . '</tr>';
      }
      return $html;
    }

    /* Need more info from db   */

    public function buildSRView($sr_num) {
        $sql = "SELECT title, submitted_when, last_updated, description, submitted_by
        FROM service_record
        WHERE sr_id = '$sr_num'";
        $result = $this->query($sql);
        $title_info = $this->getTitleInfo($result[0][0]);
        $person = $this->getPersonInfo($result[0][4]);
        $result_array = array(
                   "title" => $title_info[0][3],
                   "submitted_when" => $result[0][1],
                   "last_updated" => $result[0][2],
                   "description" => $result[0][3],
                   "submitted_by" => $person[0][4]
        );
        return $result_array;
    }
    // Mailbox
    public function buildMailbox($username) {
        /*
          TODO: Add select query to extract specific comments and mail
        */
    }
    // Modal Functions
    public function submitModalForm($title, $building, $room_number, $description) {
      $title_number = intval($this->getTitleNumber($title));
      $record_type = intval($this->getRecordType($title));
      $username = intval($this->getUserID($_SESSION['username']));
      $now = date('Y-m-d H:i:s');
      $sql = "INSERT INTO service_record (title, type, description, bldg, room, submitted_by, last_updated)
              VALUES('$title_number', '$record_type', '$description', '$building', '$room_number', '$username', '$now')";
      $this->queryChange($sql);
      return true;
    }

    //-------Help Desk Staff Functions ---->
    public function getRecordTypes() {
      $sql = "SELECT * FROM record_type";
      $types = $this->query($sql);
      $option_html = '<option selected disabled>Choose a Type</option>';
      if($types) {
        foreach($types as $result) {
          $option_html .= '<option value="'.$result[0].'">'.$result[1].'</option>';
        }
      }
      return $option_html;
    }

    public function getRecordCateogries($type) {
      if ($type) {
        $sql = "SELECT category.id, category.cat FROM category INNER JOIN sub_category ON category.id = sub_category.cat INNER JOIN record_type ON sub_category.type = record_type.id WHERE sub_category.type = '$type' GROUP BY category.id ORDER BY cat ASC";
        $option_html = '<option selected disabled>Choose a Category</option>';
        $cats = $this->query($sql);
        if($cats) {
          foreach($cats as $result) {
            $option_html .= '<option value="'.$result[0].'">'.$result[1].'</option>';
          }
        }
        return $option_html;
      } else {
        return false;
      }
    }

    public function getRecordSubCateogries($type, $cat) {
      if ($type && $cat) {
        $sql = "SELECT sub_category.id, sub_category.sub_cat FROM sub_category INNER JOIN category ON sub_category.cat = category.id INNER JOIN record_type ON sub_category.type = record_type.id WHERE sub_category.type = '$type' AND sub_category.cat = '$cat' GROUP BY sub_category.sub_cat ORDER BY sub_cat ASC";
        $option_html = '<option selected disabled>Choose a Sub-Category</option>';
        $cats = $this->query($sql);
        if($cats) {
          foreach($cats as $result) {
            $option_html .= '<option value="'.$result[0].'">'.$result[1].'</option>';
          }
        }
        return $option_html;
      } else {
        return false;
      }
    }

    public function getPersonInfo($name){
      if ($name == 'all') {
          $sql = "SELECT * FROM users ORDER BY lname ASC";
      } else {
        $sql = "SELECT * FROM users WHERE id = '$name'";
      }
      return $this->query($sql);
    }

    //-------Admin Functions--------------->
    public function getHDUsers() {
      $sql = "SELECT id, fname, lname, email
              FROM users
              JOIN user_roles
              WHERE users.id = user_roles.userID AND user_roles.roleID <> '2'
              GROUP BY id";
      return $this->query($sql);
    }
    public function addUser() {
      $now = date("Y-m-d");
      $now2 = date('Y-m-d H:i:s');
      $user = $_POST['username'];
      $DoAdd = $this->checkUsernameExists($user);
      if ($DoAdd) {
        $sql = "INSERT INTO users (fname, lname, email, username, banner_id, phone, creation_date, confirmcode)
                VALUES('".$_POST['fname']."','".$_POST['lname']."','".$_POST['email']."','".$_POST['username']."','".$_POST['banner_id']."','".$_POST['phone']."','$now','y')";

        $succ = $this->queryChange($sql);
        $sql = "SELECT id FROM users WHERE username = '$user' LIMIT 1";

        $userID = $this->getUserID($user);
        foreach ($_POST as $k => $v) {
          if (substr($k,0,5) == "role_") {
            $roleID = intval(substr($k,5));
            if ($v == '0' || $v == 'x') {} else {
              $strSQL = "INSERT INTO user_roles (userID, roleID, addDate) VALUES('$userID', '$roleID', '$now2')";
              $this->queryChange($strSQL);
            }
          }
        }
        $this->ResetPassword($userID);
        return true;
      } else {
        return false;
      }
    }

    public function deleteUser($who) {
      if ($who != $_SESSION['userID']) {
        $sql = "DELETE FROM users WHERE id = '$who'";
        $this->queryChange($sql);
        $sql = "DELETE FROM user_roles WHERE userID = '$who'";
        $this->queryChange($sql);
        $sql = "DELETE FROM user_perms WHERE userID = '$who'";
        $this->queryChange($sql);
        return true;
      }
    }
    public function getUserID($who) {
      $sql = "SELECT id FROM users WHERE username='$who' LIMIT 1";
      $result = $this->query($sql);
      foreach ($result as $res) {
        $id = $res[0];
      }
      return $id;
    }

    function GetAbsoluteURLFolder() {
        $scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
        $urldir ='';
        $pos = strrpos($_SERVER['REQUEST_URI'],'/');
        if(false !==$pos)
        {
            $urldir = substr($_SERVER['REQUEST_URI'],0,$pos);
        }
        $scriptFolder .= $_SERVER['HTTP_HOST'].$urldir;
        return $scriptFolder;
    }

    function SanitizeForSQL($str) {
        if( function_exists( "mysql_real_escape_string" )) {
              $ret_str = mysql_real_escape_string( $str );
        } else {
              $ret_str = addslashes( $str );
        }
        return $ret_str;
    }

    function hashSSHA($password) {
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    function ResetPassword($userID) {
        $new_password = $this->ResetUserPasswordInDB($userID);
        $this->SendNewPassword($new_password);

        return true;
    }

    function ResetUserPasswordInDB($userID) {
        $new_password = substr(md5(uniqid()),0,10);
        if(false == $this->ChangePasswordInDB($userID,$new_password)) {
            return false;
        }
        return $new_password;
    }

    function ChangePasswordInDB($userID, $newpwd) {
        $newpwd = $this->SanitizeForSQL($newpwd);
        $hash = $this->hashSSHA($newpwd);
        $new_password = $hash["encrypted"];
        $salt = $hash["salt"];
        $qry = "UPDATE users SET password='".$new_password."', salt='".$salt."' Where  id='".$userID."'";
        $this->queryChange($qry);
        return true;
    }

    function SendNewPassword($new_password) {
        $email = $_POST['email'];
        $mailer = new PHPMailer();
        $mailer->CharSet = 'utf-8';
        $mailer->AddAddress($email,$_POST['fname']);
        $mailer->Subject = "Your new password for ".$this->conf['site']['company_name'].": Service Desk Pro";
        $mailer->From = $this->conf['customize']['sysemail'];
        $mailer->FromName = $this->conf['site']['company_name']." Support";
        $mailer->Body ="Hello ".$_POST['fname']." ".$_POST['lname'].",\r\n\r\n".
        "Welcome to ".$this->conf['site']['company_name']."!\r\n".
        "Your account has been created successfully.\r\n\r\n".
        "Here is your updated login:\r\n".
        "Username: ".$_POST['username']."\r\n".
        "Password: $new_password\r\n".
        "\r\n".
        "Login here: ".$this->GetAbsoluteURLFolder()."/login.php\r\n".
        "\r\n".
        "Regards,\r\n".
        "Support\r\n";
        if(!$mailer->Send())
        {
            return false;
        }
        return true;
    }

    function loadSetting($setting) {
      if ($setting == "maintenance") {
        $sql = "SELECT * FROM admin_settings WHERE setting='maintenance'";
        $result = $this->query($sql);
        return $result;
      }
    }

    function updateSetting($setting) {
      if ($setting == "maintenance") {
        $msg = $_POST['dev_msg'];
        $tmsg = $_POST['dev_alert'];
        if (!$tmsg) $tmsg = 0;
        else $tmsg = 1;
        $toggle = $_POST['dev_on'];
        if (!$toggle) $toggle = 0;
        else $toggle = 1;
        echo $msg.$tmsg.$toggle;
        $sql = "UPDATE admin_settings SET msg='$msg', toggle_display='$toggle', toggle_msg='$tmsg' WHERE setting='maintenance'";
        $this->queryChange($sql);
        header("Location: Admin.php?page=cpanel&subpage=devops");
        exit;
      }
    }
  }
}
//Need this to init class ----> $dali = new DALi();

/* Sample Future-Proof Protected Functions

    public function insert($table, $data, $format) {
      // Check for $table or $data not set
      if ( empty( $table ) || empty( $data ) ) {
        return false;
      }

      // Connect to the database
      $db = $this->connect();

      // Cast $data and $format to arrays
      $data = (array) $data;
      $format = (array) $format;

      // Build format string
      $format = implode('', $format);
      $format = str_replace('%', '', $format);

      list( $fields, $placeholders, $values ) = $this->prep_query($data);

      // Prepend $format onto $values
      array_unshift($values, $format);
      // Prepary our query for binding
      $stmt = $db->prepare("INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})");
      // Dynamically bind values
      call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

      // Execute the query
      $stmt->execute();

      // Check for successful insertion
      if ( $stmt->affected_rows ) {
        return true;
      }

      return false;
    }
    public function update($table, $data, $format, $where, $where_format) {
      // Check for $table or $data not set
      if ( empty( $table ) || empty( $data ) ) {
        return false;
      }

      // Connect to the database
      $db = $this->connect();

      // Cast $data and $format to arrays
      $data = (array) $data;
      $format = (array) $format;

      // Build format array
      $format = implode('', $format);
      $format = str_replace('%', '', $format);
      $where_format = implode('', $where_format);
      $where_format = str_replace('%', '', $where_format);
      $format .= $where_format;

      list( $fields, $placeholders, $values ) = $this->prep_query($data, 'update');

      //Format where clause
      $where_clause = '';
      $where_values = '';
      $count = 0;

      foreach ( $where as $field => $value ) {
        if ( $count > 0 ) {
          $where_clause .= ' AND ';
        }

        $where_clause .= $field . '=?';
        $where_values[] = $value;

        $count++;
      }
      // Prepend $format onto $values
      array_unshift($values, $format);
      $values = array_merge($values, $where_values);
      // Prepary our query for binding
      $stmt = $db->prepare("UPDATE {$table} SET {$placeholders} WHERE {$where_clause}");

      // Dynamically bind values
      call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($values));

      // Execute the query
      $stmt->execute();

      // Check for successful insertion
      if ( $stmt->affected_rows ) {
        return true;
      }

      return false;
    }
    public function select($query, $data, $format) {
      // Connect to the database
      $db = $this->connect();

      //Prepare our query for binding
      $stmt = $db->prepare($query);

      //Normalize format
      $format = implode('', $format);
      $format = str_replace('%', '', $format);

      // Prepend $format onto $values
      array_unshift($data, $format);

      //Dynamically bind values
      call_user_func_array( array( $stmt, 'bind_param'), $this->ref_values($data));

      //Execute the query
      $stmt->execute();

      //Fetch results
      $result = $stmt->get_result();

      //Create results object
      while ($row = $result->fetch_object()) {
        $results[] = $row;
      }
      return $results;
    }
    public function delete($table, $id) {
      // Connect to the database
      $db = $this->connect();

      // Prepary our query for binding
      $stmt = $db->prepare("DELETE FROM {$table} WHERE ID = ?");

      // Dynamically bind values
      $stmt->bind_param('d', $id);

      // Execute the query
      $stmt->execute();

      // Check for successful insertion
      if ( $stmt->affected_rows ) {
        return true;
      }
    }
    private function prep_query($data, $type='insert') {
      // Instantiate $fields and $placeholders for looping
      $fields = '';
      $placeholders = '';
      $values = array();

      // Loop through $data and build $fields, $placeholders, and $values
      foreach ( $data as $field => $value ) {
        $fields .= "{$field},";
        $values[] = $value;

        if ( $type == 'update') {
          $placeholders .= $field . '=?,';
        } else {
          $placeholders .= '?,';
        }

      }

      // Normalize $fields and $placeholders for inserting
      $fields = substr($fields, 0, -1);
      $placeholders = substr($placeholders, 0, -1);

      return array( $fields, $placeholders, $values );
    }
    private function ref_values($array) {
      $refs = array();
      foreach ($array as $key => $value) {
        $refs[$key] = &$array[$key];
      }
      return $refs;
    }
  }
}
*/

?>