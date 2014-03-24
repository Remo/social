<?php

defined('C5_EXECUTE') or die('Access Denied.');

Loader::model('user_list');

class DashboardSocialController extends Controller {

    public function view() {
        $html = Loader::helper('html');
        $this->addHeaderItem($html->css('zocial/css/zocial.css', 'social'));
        $this->addHeaderItem($html->css('master.css', 'social'));
    }

    public function on_before_render() {
        $subnav = array(
            array('/dashboard/social/', t('General'), true),
            array('/dashboard/social/facebook', t('Facebook')),
            array('/dashboard/social/linkedin', t('LinkedIn')),
            array('/dashboard/social/twitter', t('Twitter')),
        );
        $this->set('subnav', $subnav);
    }

}
