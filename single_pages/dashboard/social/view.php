<?php
defined('C5_EXECUTE') or die("Access Denied.");

$ih    = Loader::helper('concrete/interface');
$cap   = Loader::helper('concrete/dashboard');
$valt  = Loader::helper('validation/token');
$form  = Loader::helper('form');
$flash = Loader::helper('flash_data','social');

?>
<? if($n = $flash->notice()): ?>
  <div class='message success'><?= $n; ?></div>
<? elseif($e = $flash->error()): ?>
  <div class='message error'><?= $e; ?></div>
<? endif ?>
<?php
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Social Configuration'));
?>
  <p>
    <a href="/dashboard/social/facebook" class="zocial facebook"><span><?php echo t('Configure Facebook')?></span></a>
    <a href="/dashboard/social/linkedin" class="zocial linkedin"><span><?php echo t('Configure LinkedIn')?></span></a>
    <a href="/dashboard/social/twitter" class="zocial twitter"><span><?php echo t('Configure Twitter')?></span></a>
  </p>
<?php
echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);
?>
