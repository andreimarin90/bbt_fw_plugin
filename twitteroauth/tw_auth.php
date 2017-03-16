<?php
require BBT_PL_DIR . 'twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

if (session_status() == PHP_SESSION_NONE)
    session_start();

$consumer_key = 'SrktO4dVgmqUdLFlHtnKt0NuH';
$consumer_secret = 'UsVXkJakZ1tClWFUuSVxnDK0W1Qin7PYkz3fe30O5JQEcSX5Lp';
$outh_token = '2315691576-J2Rac34O9rNVdlhFhwzIK7UcnMF8CIOvY8Txm51';
$oauthTokenSecret = 'YXkZ3CJHuYmPgN6ghFvfKYTAfTqvMFP0pAfQvFbb8EPVi';

/*define('CONSUMER_KEY', 'SrktO4dVgmqUdLFlHtnKt0NuH');
define('CONSUMER_SECRET', 'UsVXkJakZ1tClWFUuSVxnDK0W1Qin7PYkz3fe30O5JQEcSX5Lp');
define('OAUTH_CALLBACK', site_url('?bbtb-tw-login=callback') );*/

if(!empty($consumer_key) && !empty($consumer_secret))
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

        $result = bbt_twitter_login($user);
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
        //echo '<pre>';
        //print_r($connection);
        //print_r($connection->get("statuses/user_timeline", array("screen_name" => 'toleabivol')));
        //echo '<pre>';
        //var_dump(urlencode($oauth_callback));
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $oauth_callback));
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

        header('Location: ' . $url, true, 302);
        die();
    }
}