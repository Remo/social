<?php defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('facebook_api_credentials', 'social');
Loader::model('linkedin_api_credentials', 'social');
Loader::model('user_list');
Loader::packageElement('facebook/facebook', 'social');

class SocialController extends Controller { 
  
  public function view() {
    
  }
  
  public function login($network = '') {
    $this->setContentType("text/plain");
    $html     = Loader::helper('html');
    $facebook = FacebookApiCredentials::load();
    $linkedin = LinkedinApiCredentials::load();
    
    $this->set('facebook',$facebook);
    $this->set('linkedin',$linkedin);
    
    if($network == 'facebook') {
      $code = $_REQUEST["code"];
      
      $f = new Facebook(array(
        'appId' => $facebook->getApiKey(),
        'secret' => $facebook->getSecret()
      ));
      
      if(empty($code)) {
        $loginUrl = $f->getLoginUrl(array(
          'redirect_uri' => "http://{$_SERVER['SERVER_NAME']}/social/login/facebook"
        ));
        // $this->redirect($loginUrl);
        header("Location: $loginUrl");
        exit;
      }
      else {
        $profile = $f->api('/me','GET');
        $user = array(
          "first_name"          => $profile["first_name"],
          "last_name"           => $profile["last_name"],
          "social_network_name" => "facebook",
          "social_network_id"   => $profile["id"]
        );
        if(self::do_login($user) == false) {
          if(self::do_register($user)) {
            if(self::do_login($user)) {
              $this->redirect('/');
            }
          }
        }
        // User is logged in and ready.
        // ...
        $u = new User();
        if(!$u->checkLogin()) {
          echo 'Error: not logged in. You should be logged in by now.';
        }
        else {
          $this->redirect('/');
        }
      }
    }
    elseif($network == 'linkedin') {
      
    }
    else {
      $this->redirect('/social');
    }
    exit;
  }
  
  protected static function do_login($user) {
    $ul = new UserList(); 
    $social_network_name = $user['social_network_name'];
    $social_network_id   = $user['social_network_id'];
    
    $ul->filterByAttribute("social_network_name", $social_network_name);
    $ul->filterByAttribute("social_network_id",   $social_network_id);
    
    $list     = $ul->get(1);
    $user     = $list[0];
    $response = false;
    
    if($user <> null) {
      $response = User::loginByUserID($user->getUserID());
    }
    
    return $response;
  }
  
  protected static function do_register($user) {
    $response = null;
    $rand     = md5(uniqid());
    $uData    = array(
      'uName'            => $user['social_network_id'],
      'uPassword'        => $rand,
      'uPasswordConfirm' => $rand,
      'uEmail'           => "{$user['social_network_id']}.{$user['social_network_name']}.social.registration@noemail.com"
    );
    
    if($ui = UserInfo::register($uData)) {
      $ui->setAttribute("social_network_name", $user['social_network_name']);
      $ui->setAttribute("social_network_id", $user['social_network_id']);
      $ui->setAttribute('first_name', $user['first_name']);
      $ui->setAttribute('last_name', $user['last_name']);
      self::setPicture($user, $ui);
      $response = $ui;
    }

    return $response;
  }
  
  protected static function setPicture($user, $ui) {
    $img = "";
    if(isset($user['pictureUrl']) && $user['pictureUrl'] <> '') {
      $img = $user['pictureUrl'];
    }
    else if($user['social_network_name'] == 'facebook') {
      $img = "https://graph.facebook.com/{$user['social_network_id']}/picture?type=square";
    }
    
    $fullpath = DIR_FILES_AVATARS."/".$ui->getUserID().".jpg";
    
    $ch = curl_init($img);
    
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    
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
}
?>