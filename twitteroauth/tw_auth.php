<?php
require BBT_PL_DIR . 'twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

if (session_status() == PHP_SESSION_NONE)
    session_start();

$consumer_key = 'CKUg9sd3S3gFEJ4GGgpU6hnb1';
$consumer_secret = 'L8xJ8qHYuXDzzANRQP2XzXSB6gnV2LUUbdyfjc1Wan84oKu7T9';
$outh_token = '2315691576-J2Rac34O9rNVdlhFhwzIK7UcnMF8CIOvY8Txm51';
$oauthTokenSecret = 'YXkZ3CJHuYmPgN6ghFvfKYTAfTqvMFP0pAfQvFbb8EPVi';

define('CONSUMER_KEY', 'CKUg9sd3S3gFEJ4GGgpU6hnb1');
define('CONSUMER_SECRET', 'L8xJ8qHYuXDzzANRQP2XzXSB6gnV2LUUbdyfjc1Wan84oKu7T9');
define('OAUTH_CALLBACK', site_url('?bbtb-tw-login=callback') );

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
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
        echo '<pre>';
        print_r($connection);
        echo '<pre>';
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));

        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

        //header('Location: ' . $url);
    }
}