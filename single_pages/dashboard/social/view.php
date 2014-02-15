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
  <span><?= t('Social Configuration') ?></span>
</h1>
<div class="ccm-dashboard-inner">
  <p>
    <a href="/dashboard/social/facebook" class="zocial facebook"><span><?php echo t('Configure Facebook')?></span></a>
    <a href="/dashboard/social/linkedin" class="zocial linkedin"><span><?php echo t('Configure LinkedIn')?></span></a>
    <a href="/dashboard/social/twitter" class="zocial twitter"><span><?php echo t('Configure Twitter')?></span></a>
  </p>
</div>
