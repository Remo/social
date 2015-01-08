<?php

defined('C5_EXECUTE') or die('Access Denied.');

Loader::model('google_api_credentials', 'social');

class DashboardSocialGoogleController extends Controller {

    public function view() {
        $credentials = GoogleApiCredentials::load();
        $this->set('credentials', $credentials);
    }

    public function on_before_render() {
        $subnav = array(
            array('/dashboard/social/', t('General')),
            array('/dashboard/social/facebook', t('Facebook')),
            array('/dashboard/social/linkedin', t('LinkedIn')),
            array('/dashboard/social/twitter', t('Twitter')),
            array('/dashboard/social/google', t('Google'), true),
        );
        $this->set('subnav', $subnav);
    }

    public function update() {
        $flash = Loader::helper('flash_data', 'social');

        $credentials = GoogleApiCredentials::load();
        $credentials->setApiKey($_POST['api_key']);
        $credentials->setSecret($_POST['secret']);

        $updated = $credentials->save();

        if ($updated) {
            $flash->notice(t("Successfully updated configuration."));
        } else {
            $flash->error(t("Couldn't save configuration to the database."));
        }
        $this->redirect('/dashboard/social/google');
    }

}
