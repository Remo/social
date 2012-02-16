<?php defined('C5_EXECUTE') or die("Access Denied.");

define('SOCIAL_REDIRECT_HANDLE', 'redirectUrl');
define('SOCIAL_POPUP_CALLBACK', 'popupCallback');

Loader::model('facebook_api_credentials', 'social');
Loader::model('linkedin_api_credentials', 'social');
Loader::model('twitter_api_credentials', 'social');
Loader::model('user_list');
Loader::tool('hybridauth/Hybrid/Auth', null, 'social');

class SocialController extends Controller {
  var $user_profile,
      $network,
      $auth;

  public function view() {
    $html = Loader::helper('html');
    $this->addHeaderItem($html->css('zocial/css/zocial.css', 'social'));
    $this->addHeaderItem($html->css('master.css', 'social'));
  }

  public function login($network = '') {
    $this->network = $network;
    $this->setContentType("text/plain");
    $this->setRedirectUrl();
    $this->setPopupCallback();
    $config = $this->get_hybrid_auth_config();
    $hybridauth = new Hybrid_Auth($config);

    if($this->network == 'facebook') {
      $this->auth = $hybridauth->authenticate("Facebook");
    }
    elseif($this->network == 'linkedin') {
      $this->auth = $hybridauth->authenticate("LinkedIn");
    }
    elseif($this->network == 'twitter') {
      $this->auth = $hybridauth->authenticate("Twitter");
    }
    else {
      $this->redirect('/');
    }

    $is_user_logged_in = $this->auth->isUserConnected();
    $this->user = $this->auth->getUserProfile();

    $u = new User();
    if($u->checkLogin()) {
      $this->setProfile();
    }
    else {
      if(!$this->do_login()) {
        if($this->do_register()) {
          if($this->do_login()) {
            $this->setProfile();
          }
        }
      }
    }
    if($popupCallback = $this->getPopupCallback()) {
      $this->setContentType('text/html');
      $this->set('popupCallback', $popupCallback);
    }
    else {
      $this->externalRedirect($this->getRedirectUrl());
    }
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
      $response = $ui;
    }

    return $response;
  }

  protected function setProfile() {
    $u  = new User();
    $ui = UserInfo::getById($u->getUserId());

    $ui->setAttribute("{$this->network}_id", $this->user->identifier);
    if($ui->getAttribute('first_name') == '') {
      $ui->setAttribute('first_name', $this->user->firstName);
    }
    if($ui->getAttribute('last_name') == '') {
      $ui->setAttribute('last_name', $this->user->lastName);
    }

    $this->setPicture($ui);

    if($this->network == 'linkedin') {
      $this->auth->api()->setResponseFormat('JSON');
      $resp = $this->auth->api()->profile('~:(id,first-name,last-name,industry,positions)');
      $profile = json_decode($resp['linkedin']);

      if(UserAttributeKey::getByHandle('company')) {
        $company = $profile->positions->values[0]->company->name;
        $ui->setAttribute('company', $company);
      }
      if(UserAttributeKey::getByHandle('title')) {
        $title = $profile->positions->values[0]->title;
        $ui->setAttribute('title', $title);
      }
    }
  }

  protected function setPicture($ui) {
    $img = "";
    if(isset($this->user->photoURL) && $this->user->photoURL <> '') {
      $img = $this->user->photoURL;
    }

    if($img == "") {
      return; // No image, no try.
    }

    $fullpath = DIR_FILES_AVATARS."/".$ui->getUserID().".jpg";

    $ch = curl_init($img);

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

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
  protected function setPopupCallback() {
    if(isset($_REQUEST[SOCIAL_POPUP_CALLBACK]) && !empty($_REQUEST[SOCIAL_POPUP_CALLBACK])) {
      $_SESSION[SOCIAL_POPUP_CALLBACK] = $_REQUEST[SOCIAL_POPUP_CALLBACK];
    }
    else {
      $_SESSION[SOCIAL_POPUP_CALLBACK] = null;
    }
  }
  protected function setRedirectUrl() {

    if(isset($_REQUEST[SOCIAL_REDIRECT_HANDLE]) && !empty($_REQUEST[SOCIAL_REDIRECT_HANDLE])) {
      $_SESSION[SOCIAL_REDIRECT_HANDLE] = $_REQUEST[SOCIAL_REDIRECT_HANDLE];
    }
    elseif(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],'/social') === false) {
      $_SESSION[SOCIAL_REDIRECT_HANDLE] = $_SERVER['HTTP_REFERER'];
    }
    else {
      $_SESSION[SOCIAL_REDIRECT_HANDLE] = "/";
    }
  }
  protected function getPopupCallback() {
    $callback = null;
    if(isset($_SESSION[SOCIAL_POPUP_CALLBACK])) {
      $callback = $_SESSION[SOCIAL_POPUP_CALLBACK];
      unset($_SESSION[SOCIAL_POPUP_CALLBACK]);
    }
    return $callback;
  }
  protected function getRedirectUrl() {
    $url = "/";
    if(isset($_SESSION[SOCIAL_REDIRECT_HANDLE])) {
      $url = $_SESSION[SOCIAL_REDIRECT_HANDLE];
      unset($_SESSION[SOCIAL_REDIRECT_HANDLE]);
    }
    return $url;
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
    $baseUrl  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] <> "off") ? "https://" : "http://";
    $baseUrl .= $_SERVER['SERVER_NAME'] . "/packages/social/tools/hybridauth/";

    $config   = array(
     "base_url" => $baseUrl,
     "providers" => array (
       "Facebook" => array (
         "enabled" => true,
         "keys"    => array ( "id" => $facebook->getApiKey(), "secret" => $facebook->getSecret() ),
         "scope"   => "email"
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
