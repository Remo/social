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
  <span><?= t('Facebook Configuration') ?></span>
</h1>
<div class="ccm-dashboard-inner">
  <form action="<?= $form->action('/dashboard/social/facebook','update'); ?>" method="post" accept-charset="utf-8" id="update_facebook">
    <fieldset>
      <label for="api_key"><?php echo t('App ID/API Key')?></label>
      <input type="text" name="api_key" value="<?= $credentials->getApiKey(); ?>" class="text" id="api_key">
      
      <label for="secret"><?php echo t('App Secret')?></label>
      <input type="text" name="secret" value="<?= $credentials->getSecret(); ?>" class="text" id="secret">
    </fieldset>
    <p><input type="submit" value="<?php echo t('Save &rarr;')?>"></p>
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