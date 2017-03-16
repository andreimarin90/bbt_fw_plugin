<?php
require BBT_PL_DIR . 'twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

if (session_status() == PHP_SESSION_NONE)
    session_start();

$consumer_key = esc_html(toco_go('consumer_key'));
$consumer_secret = esc_html(toco_go('consumer_secret'));
$outh_token = esc_html(toco_go('oauth_token'));
$oauthTokenSecret = esc_html(toco_go('oauth_token_secret'));

if(!empty($consumer_key) && !empty($consumer_secret) && !empty($outh_token) && !empty($oauthTokenSecret))
{
    $oauth_callback = site_url('?bbt-tw-login=callback');

    if($_GET['bbt-tw-login'] == 'callback')
    {
        $request_token = [];
        $request_token['oauth_token'] = $_SESSION['oauth_token'];
        $request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

        if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
            wp_die( "Abort! Something is wrong." );
        }
        //get access tocken
        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $request_token['oauth_token'], $request_token['oauth_token_secret']);
        $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
        $_SESSION['access_token'] = $access_token;

        //get user info
        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $user = $connection->get("account/verify_credentials");

        $result = bbt_pl_twitter_login($user);
        //send data to main window and close auth popup
        ?>
        <script type="text/javascript">
            window.opener.BBT_TwLoginCallback(<?php bbt_print($result); ?>);
            window.close();
        </script>
        <?php
    }
    else
    {
        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $outh_token, $oauthTokenSecret);
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $oauth_callback));
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

        header('Location: ' . $url, true, 302);
        die();
    }
}