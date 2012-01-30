<?php
defined('C5_EXECUTE') or die("Access Denied.");

$ih    = Loader::helper('concrete/interface');
$cap   = Loader::helper('concrete/dashboard');
$valt  = Loader::helper('validation/token');
$form  = Loader::helper('form');
$flash = Loader::helper('flash_data','flash_data');

?>
<? if($n = $flash->notice()): ?>
  <div class='message success'><?= $n; ?></div>
<? elseif($e = $flash->error()): ?>
  <div class='message error'><?= $e; ?></div>
<? endif ?>
<h1>
  <span><?= t('Twitter Configuration') ?></span>
</h1>
<div class="ccm-dashboard-inner">
  <form action="<?= $form->action('/dashboard/social/twitter','update'); ?>" method="post" accept-charset="utf-8" id="update_twitter">
    <fieldset>
      <label for="api_key">API Key</label>
      <input type="text" name="api_key" value="<?= $credentials->getApiKey(); ?>" class="text" id="api_key">
      
      <label for="secret">Secret Key</label>
      <input type="text" name="secret" value="<?= $credentials->getSecret(); ?>" class="text" id="secret">
    </fieldset>
    <p><input type="submit" value="Save &rarr;"></p>
  </form>
</div>
<style type="text/css" media="screen">
  label {
    display: block;
    margin-top: 10px;
  }
  input.text {
    font-size: 14px;
    width: 300px;
  }
</style>
