<?php defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('twitter_api_credentials', 'social');

class DashboardSocialTwitterController extends Controller { 
  
  public function view() {
    $credentials = TwitterApiCredentials::load();
    $this->set('credentials', $credentials);
  }
  
  public function on_before_render() {
    $subnav = array(
			array('/dashboard/social/', t('General')),
			array('/dashboard/social/facebook', t('Facebook')),
			array('/dashboard/social/linkedin', t('LinkedIn')),
			array('/dashboard/social/twitter', t('Twitter'), true),
		);
		$this->set('subnav', $subnav);
  }
  
  public function update() {
    $flash = Loader::helper('flash_data','social');
    
    $credentials = TwitterApiCredentials::load();
    $credentials->setApiKey($_POST['api_key']);
    $credentials->setSecret($_POST['secret']);
    
    $updated = $credentials->save();
    
    if($updated) {
      $flash->notice(t("Successfully updated configuration."));
    }
    else {
      $flash->error(t("Couldn't save configuration to the database."));
    }
    $this->redirect('/dashboard/social/twitter');
  }
}
?>