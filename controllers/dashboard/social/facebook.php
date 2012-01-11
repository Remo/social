<?php defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('facebook_api_credentials', 'social');

class DashboardSocialFacebookController extends Controller { 
  
  public function view() {
    $credentials = FacebookApiCredentials::load();
    $this->set('credentials', $credentials);
  }
  
  public function on_before_render() {
    $subnav = array(
			array('/dashboard/social/', t('General')),
			array('/dashboard/social/facebook', t('Facebook'), true),
			array('/dashboard/social/linkedin', t('LinkedIn')),
		);
		$this->set('subnav', $subnav);
  }
  
  public function update() {
    $flash = Loader::helper('flash_data','flash_data');
    
    $credentials = FacebookApiCredentials::load();
    $credentials->setApiKey($_POST['api_key']);
    $credentials->setSecret($_POST['secret']);
    
    $updated = $credentials->save();
    
    if($updated) {
      $flash->notice("Successfully updated configuration.");
    }
    else {
      $flash->error("Couldn't save configuration to the database.");
    }
    $this->redirect('/dashboard/social/facebook');
  }
}
?>
