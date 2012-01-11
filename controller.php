<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('single_page');
Loader::model('user_attributes');

class SocialPackage extends Package {

  protected $pkgHandle = 'social';
  protected $appVersionRequired = '5.4.2.2';
  protected $pkgVersion = '0.9';

  public function getPackageDescription() {
    return t("Adds social login and registration.");
  }

  public function getPackageName() {
    return t("Social");
  }

  public function install() {
    $pkg = parent::install();
    
    // Add social single page
    SinglePage::add('social', $pkg);
    
    // Add dashboard pages.
    $d = SinglePage::add('dashboard/social', $pkg);
    $d->update(
      array(
        'cName'         => t('Social'),
        'cDescription'  => t('Configure social networks.')
      )
    );
    SinglePage::add('dashboard/social/facebook', $pkg);
    
    // TODO: Implement LinkedIn Authentication
    // SinglePage::add('dashboard/social/linkedin', $pkg);
    
    // TODO: Implement Twitter Authentication
    // SinglePage::add('dashboard/social/twitter', $pkg);
    
    // Add attribute keys
    $social_network_name = UserAttributeKey::getByHandle('social_network_name');
    if(!is_object($social_network_name)) {
      UserAttributeKey::add('text', array(
        'akHandle' => 'social_network_name', 
        'akName' => t('Social Network Name'))
      , $pkg);
    }
    
    $social_network_id = UserAttributeKey::getByHandle('social_network_id');
    if(!is_object($social_network_id)) {
      UserAttributeKey::add('text', array(
        'akHandle' => 'social_network_id', 
        'akName' => t('Social Network ID'))
      , $pkg);
    }
    
    $first_name = UserAttributeKey::getByHandle('first_name');
    if(!is_object($first_name)) {
      UserAttributeKey::add('text', array(
        'akHandle' => 'first_name', 
        'akName' => t('First Name'))
      , $pkg);
    }
    
    $last_name = UserAttributeKey::getByHandle('last_name');
    if(!is_object($last_name)) {
      UserAttributeKey::add('text', array(
        'akHandle' => 'last_name', 
        'akName' => t('Last Name'))
      , $pkg);
    }
  }
}

?>