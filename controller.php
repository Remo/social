<?php

defined('C5_EXECUTE') or die('Access Denied.');

Loader::model('single_page');
Loader::model('user_attributes');

class SocialPackage extends Package
{
    protected $pkgHandle = 'social';
    protected $appVersionRequired = '5.4.2.2';
    protected $pkgVersion = '0.9';

    public function getPackageDescription()
    {
        return t("Adds social login and registration.");
    }

    public function getPackageName()
    {
        return t("Social");
    }

    public function install()
    {
        $pkg = parent::install();

        // Add social single page
        SinglePage::add('social', $pkg);

        // Add dashboard pages.
        $d = SinglePage::add('dashboard/social', $pkg);
        $d->update(
                array(
                    'cName' => t('Social'),
                    'cDescription' => t('Configure social networks.'),
                )
        );
        SinglePage::add('dashboard/social/facebook', $pkg);
        SinglePage::add('dashboard/social/linkedin', $pkg);
        SinglePage::add('dashboard/social/twitter', $pkg);
        SinglePage::add('dashboard/social/google', $pkg);

        // Add social network attribute keys.
        $this->add_user_attribute_key('facebook_id', 'Facebook ID');
        $this->add_user_attribute_key('linkedin_id', 'LinkedIn ID');
        $this->add_user_attribute_key('twitter_id', 'Twitter ID');
        $this->add_user_attribute_key('google_id', 'Google ID');

        // Basic fields for profile information.
        $this->add_user_attribute_key('first_name', 'First Name');
        $this->add_user_attribute_key('last_name', 'Last Name');
    }

    public function add_user_attribute_key($handle, $name, $type = 'text')
    {
        $ak = UserAttributeKey::getByHandle($handle);
        if (!is_object($ak)) {
            UserAttributeKey::add($type, array(
                'akHandle' => $handle,
                'akName' => t($name), ), $pkg);
        }
    }
}
