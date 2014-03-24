<?php
defined('C5_EXECUTE') or die('Access Denied.');

if ($popupCallback) {
    ?>
    <a href="#" id="closeAndContinue">Close window to continue</a>
    <script type="text/javascript" charset="utf-8">
        var closeAndContinue = function() {
            if (window.opener)
                window.opener["<?php echo $popupCallback; ?>"]();
            window.close();
            return false;
        };
        closeAndContinue();
        document.getElementById('closeAndContinue').onclick = closeAndContinue;
    </script>
    <?php exit; ?>
<?php } else { ?>
    <h1>Login</h1>
    <a href="/social/login/facebook" class="zocial facebook"><span>Login with Facebook</span></a>
    <a href="/social/login/linkedin" class="zocial linkedin"><span>Login with LinkedIn</span></a>
    <a href="/social/login/twitter" class="zocial twitter"><span>Login with Twitter</span></a>
<?php
}
