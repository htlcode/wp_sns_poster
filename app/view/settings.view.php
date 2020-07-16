<div clas="wrap">
	<h1><?php echo __('WP Oto Poster Settings'); ?></h1>
	<form method="post">
	<hr>
    <h2><?php echo __('Timezone',WP_OTO_POSTER); ?></h2>
    <table>
        <tr>
            <td>
                <select name="timezone">
                <?php foreach($timezones as $t): ?>
                    <option value="<?php echo $t['zone'] ?>" <?php if($timezone == $t['zone']){ echo 'selected';}?>><?php echo $t['diff_from_GMT'] . ' - ' . $t['zone'] ?></option>
                <?php endforeach; ?>
                </select><br>
                <i><?php echo __('The timezone which posts will be published your social networks',WP_OTO_POSTER) ?></i>
            </td>
        </tr>
    </table>
	<h2><?php echo __('My secret key',WP_OTO_POSTER); ?></h2>
	<table>
		<tr>
            <td>
                <input type="text" name="my_secret_key" size="50" value="<?php if(!empty($my_secret_key)){echo $my_secret_key;}?>" required><br>
                <i><?php echo __('This secret key will be used as a passphrase to allow script to be executed',WP_OTO_POSTER) ?></i>
            </td>
        </tr>
	</table>
	<hr>
	<h2><?php echo __('Facebook credentials',WP_OTO_POSTER); ?></h2>
	<table>
		<tr>
            <td>
                <?php echo __('App Id',WP_OTO_POSTER) ?><br>
                <input type="text" name="facebook_app_id" size="50" value="<?php if(!empty($facebook_app_id)){echo $facebook_app_id;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('App Secret',WP_OTO_POSTER) ?><br>
                <input type="text" name="facebook_app_secret" size="50" value="<?php if(!empty($facebook_app_secret)){echo $facebook_app_secret;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Token',WP_OTO_POSTER) ?><br>
                <input type="text" name="facebook_app_token" size="50" value="<?php if(!empty($facebook_app_token)){echo $facebook_app_token;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Facebook page Id',WP_OTO_POSTER) ?><br>
                <input type="text" name="facebook_page_id" size="50" value="<?php if(!empty($facebook_page_id)){echo $facebook_page_id;}?>">
            </td>
        </tr>
	</table>
	<hr>
	<h2><?php echo __('Twitter credentials',WP_OTO_POSTER); ?></h2>
    <table>
        <tr>
            <td>
                <?php echo __('Api key',WP_OTO_POSTER) ?><br>
                <input type="text" name="twitter_consumer_api_key" size="50" value="<?php if(!empty($twitter_consumer_api_key)){echo $twitter_consumer_api_key;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Api secret key',WP_OTO_POSTER) ?><br>
                <input type="text" name="twitter_consumer_api_secret_key" size="50" value="<?php if(!empty($twitter_consumer_api_secret_key)){echo $twitter_consumer_api_secret_key;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Access Token',WP_OTO_POSTER) ?><br>
                <input type="text" name="twitter_access_token" size="50" value="<?php if(!empty($twitter_access_token)){echo $twitter_access_token;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Token Secret',WP_OTO_POSTER) ?><br>
                <input type="text" name="twitter_token_secret" size="50" value="<?php if(!empty($twitter_token_secret)){echo $twitter_token_secret;}?>">
            </td>
        </tr>
    </table>
	<hr>
	<h2><?php echo __('Instagram via Buffer credentials',WP_OTO_POSTER); ?></h2>
    <table>
        <tr>
            <td>
                <?php echo __('Client ID',WP_OTO_POSTER) ?><br>
                <input type="text" name="buffer_client_id" size="50" value="<?php if(!empty($buffer_client_id)){echo $buffer_client_id;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Client Secret',WP_OTO_POSTER) ?><br>
                <input type="text" name="buffer_client_secret" size="50" value="<?php if(!empty($buffer_client_secret)){echo $buffer_client_secret;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Redirect URI',WP_OTO_POSTER) ?><br>
                <input type="text" name="buffer_redirect_uri" size="50" value="<?php if(!empty($buffer_redirect_uri)){echo $buffer_redirect_uri;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Access Token',WP_OTO_POSTER) ?><br>
                <input type="text" name="buffer_access_token" size="50" value="<?php if(!empty($buffer_access_token)){echo $buffer_access_token;}?>">
            </td>
        </tr>
    </table>
	<hr>
	<h2><?php echo __('Pinterest credentials',WP_OTO_POSTER); ?></h2>
    <table>
        <tr>
            <td>
                <?php echo __('User',WP_OTO_POSTER) ?><br>
                <input type="text" name="pinterest_user" size="50" value="<?php if(!empty($pinterest_user)){echo $pinterest_user;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Password',WP_OTO_POSTER) ?><br>
                <input type="text" name="pinterest_password" size="50" value="<?php if(!empty($pinterest_password)){echo $pinterest_password;}?>">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('Board',WP_OTO_POSTER) ?><br>
                <input type="text" name="pinterest_board" size="50" value="<?php if(!empty($pinterest_board)){echo $pinterest_board;}?>">
            </td>
        </tr>
    </table>
	<p class="submit">
        <input id="submit" class="button button-primary" type="submit" name="submit" value="<?php echo __('Save'); ?>">
    </p>
	</form>
</div>