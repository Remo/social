<?php defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('facebook_api_credentials', 'social');
Loader::model('linkedin_api_credentials', 'social');
Loader::model('twitter_api_credentials', 'social');
Loader::model('user_list');
Loader::tool('hybridauth/Hybrid/Auth', null, 'social');

class SocialController extends Controller { 
  var $user_profile,
      $network;
      
  public function view() {
    
  }
  
  public function login($network = '') {
    $this->network = $network;
    $this->setContentType("text/plain");
    $config = $this->get_hybrid_auth_config();
  	$hybridauth = new Hybrid_Auth($config);
		
    if($this->network == 'facebook') {
      $auth = $hybridauth->authenticate("Facebook");
    }
    elseif($this->network == 'linkedin') {
      $auth = $hybridauth->authenticate("LinkedIn");
    }
    elseif($this->network == 'twitter') {
      $auth = $hybridauth->authenticate("Twitter");
    }
    else {
      $this->redirect('/');
    }
    
    $is_user_logged_in = $auth->isUserConnected();
    $this->user = $auth->getUserProfile();
    
    if(!$this->do_login()) {
      // Register user if they can't be logged in.
      if($this->do_register()) {
        // Try logging in again.
        $this->do_login();
      }
    }
    
    $this->redirect('/');
    exit;
  }
  
  protected function do_login() {
    $ul = new UserList(); 
    $ul->filterByAttribute("{$this->network}_id", $this->user->identifier);
    
    $list     = $ul->get(1);
    $user     = $list[0];
    $response = false;
    
    if($user <> null) {
      $response = User::loginByUserID($user->getUserID());
    }
    
    return $response;
  }
  
  protected function do_register() {
    $response = null;
    $rand     = md5(uniqid());
    $uName    = $this->generateUsername();
    
    // Need to create user in Concrete5 with random data.
    $uData    = array(
      'uName'            => $uName,
      'uPassword'        => $rand,
      'uPasswordConfirm' => $rand,
      'uEmail'           => "{$rand}.social.registration@noemail.com"
    );
    
    if($ui = UserInfo::register($uData)) {
      $ui->setAttribute("{$this->network}_id", $this->user->identifier);
      $ui->setAttribute('first_name', $this->user->firstName);
      $ui->setAttribute('last_name', $this->user->lastName);
      $this->setPicture($ui);
      $response = $ui;
    }

    return $response;
  }
  
  protected function setPicture($ui) {
    $img = "";
    if(isset($this->user->photoURL) && $this->user->photoURL <> '') {
      $img = $this->user->photoURL;
    }
    
    $fullpath = DIR_FILES_AVATARS."/".$ui->getUserID().".jpg";
    
    $ch = curl_init($img);
    
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    
    $rawdata = curl_exec($ch);
    
    curl_close($ch);
    
    if(file_exists($fullpath)){
      unlink($fullpath);
    }
    
    $fp = fopen($fullpath,'x');
    
    fwrite($fp, $rawdata);
    fclose($fp);
    
  	$d['uHasAvatar'] = 1;
		$ui->update($d);
  }
  
  protected function setContentType($type) {
    header("Content-type: $type");
  }
  protected function generateUsername() {
    $name = $this->user->firstName . " " . $this->user->lastName;
    $name = str_replace(" ", "", $name); // Replace spaces.
    $name = strtolower($name);           // Make lowercase.
    
    $isUnique = false;
    $count    = 0;
    $username = $name;
    while($isUnique == false) {
      $ul = new UserList(); 
      $ul->filterByUsername($username);
      $list = $ul->get(1);
      if(count($list) == 0) {
        $isUnique = true;
      }
      else {
        $count++;
        $username =  $name . $count;
      } 
    }
    return $username;
  }
  protected function get_hybrid_auth_config() {
    $facebook = FacebookApiCredentials::load();
    $linkedin = LinkedinApiCredentials::load();
    $twitter  = TwitterApiCredentials::load();
    $config   = array(
     "base_url" => "http://{$_SERVER['SERVER_NAME']}/packages/social/tools/hybridauth/", 
     "providers" => array ( 
       "Facebook" => array ( 
         "enabled" => true,
         "keys"    => array ( "id" => $facebook->getApiKey(), "secret" => $facebook->getSecret() ),
         "scope"   => ""
       ),
       "Twitter" => array ( 
         "enabled" => true,
         "keys"    => array ( "key" => $twitter->getApiKey(), "secret" => $twitter->getSecret() ) 
       ),
       "LinkedIn" => array ( 
         "enabled" => true,
         "keys"    => array ( "key" => $linkedin->getApiKey(), "secret" => $linkedin->getSecret() ),
       ),
      ),
    );
    return $config;
  }
}
?>