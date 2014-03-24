<?php

defined('C5_EXECUTE') or die('Access Denied.');

Loader::model('linkedin_api_credentials', 'social');

class DashboardSocialLinkedinController extends Controller {

    public function view() {
        $credentials = LinkedinApiCredentials::load();
        $this->set('credentials', $credentials);
    }

    public function on_before_render() {
        $subnav = array(
            array('/dashboard/social/', t('General')),
            array('/dashboard/social/facebook', t('Facebook')),
            array('/dashboard/social/linkedin', t('LinkedIn'), true),
            array('/dashboard/social/twitter', t('Twitter')),
        );
        $this->set('subnav', $subnav);
    }

    public function update() {
        $flash = Loader::helper('flash_data', 'social');

        $credentials = LinkedinApiCredentials::load();
        $credentials->setApiKey($_POST['api_key']);
        $credentials->setSecret($_POST['secret']);

        $updated = $credentials->save();

        if ($updated) {
            $flash->notice(t("Successfully updated configuration."));
        } else {
            $flash->error(t("Couldn't save configuration to the database."));
        }
        $this->redirect('/dashboard/social/linkedin');
    }

}
