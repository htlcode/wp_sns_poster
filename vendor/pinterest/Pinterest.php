<?php
/**
 * Steve's Pinterest API for PHP
 *
 * copyright © 2015 Steve Havelka
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Pinterest {

    const SUCCESS = 0;
    const INVALID_LOGIN = -1;
    const NOT_LOGGED_IN = -2;
    const MISSING_PIN_URL = -10;
    const MISSING_PIN_DESCRIPTION = -11;
    const MISSING_PIN_IMAGE_PREVIEW = -12;
    const UNABLE_TO_PIN = -13;
    const BAD_REPIN_URL = -14;
    const REPIN_URL_NOT_FOUND = -15;
    const UNABLE_TO_REPIN = -16;
    const IMAGE_DOESNT_EXIST = -20;
    const UNABLE_TO_CREATE_IMAGE_PREVIEW = -21;
    const UNABLE_TO_GET_BOARDS = -30;
    const UNABLE_TO_GET_ACCOUNT_NAME = -31;
    const UNABLE_TO_DELETE = -40;

    /**
     * The path to the cookie file, set at object creation time.
     */
    protected $cookie_jar = "";

    /**
     * Our CSRF token, which we've gotten from our cookie jar
     */
    protected $csrf_token = "";

    /**
     * Are we logged in?
     */
    protected $is_logged_in = false;

    /**
     * Our pin URL
     */
    public $pin_url = "";

    /**
     * Our pin description
     */
    public $pin_description = "";

    /**
     * The pin image preview
     */
    public $pin_image_preview = "";


    /**
     * The pin board names and IDs
     */
    public $boards = array();


    /**
     * The pin ID of the last-pinned pin
     */
    public $last_pin_id = 0;



    /**
     * Create a Pinterest object.  Call with an argument to
     * define a cookie jar.  Otherwise, we'll use a temp file.
     */
    function __construct($cookie_path = null) {

        if( $cookie_path ) {

            // If the given cookie path exists, then let's assume
            // we're already logged in
            $this->cookie_jar = $cookie_path;
            if( file_exists($this->cookie_jar) ) {

                // Set up our logged-in state
                $this->csrf_token = Pinterest::get_csrf_token($this->cookie_jar);
                $this->is_logged_in = true;

                // And get the list of boards
                $this->get_boards();

            }

        } else
            $this->cookie_jar = tempnam(sys_get_temp_dir(), "cookies"); // good 

    }



    /**
     * This method logs you into Pinterest, using the
     * cookie store tied to this instance of the object.
     *
     * NOTES on login:
     *
     * The follow variables are joined with a &, urlencoded,
     * and posted to
     * https://www.pinterest.com/resource/UserSessionResource/create/
     *
     * source_url (string): /login/
     * data (JSON): {"options":{"username_or_email":"mylogin","password":"mypass"},"context":{}}
     * module_path (string): App()>LoginPage()>Login()>Button(class_name=primary, text=Log In, type=submit, size=large)
     *
     */
    private $login_url = "https://www.pinterest.com/resource/UserSessionResource/create/";

    function login($username, $password) {

        // Prepare the login data json
        $data_json = array(
            "options" => array(
                "username_or_email" => $username,
                "password" => $password
            ),
            "context" => array()
        );

        // And prepare the post data array
        $post = array(
            "source_url" => "/login/",
            "data" => json_encode($data_json, JSON_FORCE_OBJECT),
            "module_path" => "App()>LoginPage()>Login()>Button(class_name=primary, text=Log In, type=submit, size=large)"
        );

        // Now make the post string
        $post_arr = array();
        foreach( $post as $k => $v )
            $post_arr[] = "{$k}=" . urlencode($v);

        $post_string = join("&", $post_arr);

        // Fix up parens
        $post_string = Pinterest::fix_encoding($post_string);


        // Now set up the CURL call
        $ch = \curl_init($this->login_url);
        \curl_setopt_array($ch, array(
            CURLOPT_COOKIEJAR => $this->cookie_jar,
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0",
            CURLOPT_REFERER => "https://www.pinterest.com/login/",
            CURLOPT_HTTPHEADER => array(
                'Host: www.pinterest.com',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: en-US,en;q=0.5',
                'DNT: 1',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'X-Pinterest-AppState: active',
                'X-CSRFToken: 1234',
                'X-NEW-APP: 1',
                'X-APP-VERSION: 04cf8cc',
                'X-Requested-With: XMLHttpRequest',
                'Cookie: csrftoken=1234;'
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_string,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false   // remove me later
        ));

        // Run the CURL call
        $res = \curl_exec($ch);
        \curl_close($ch);

        // If the result is json, we've succeeded!
        if( json_decode($res) === null ) {
            $this->is_logged_in = false;
            return Pinterest::INVALID_LOGIN;
        } else {
            $this->csrf_token = Pinterest::get_csrf_token($this->cookie_jar);
            $this->is_logged_in = true;
            $this->get_boards();
            return Pinterest::SUCCESS;
        }

    }





    /**
     * Get the board IDs for this Pinterest account.
     *
     * NOTES on getting board IDs:
     *
     * The follow variables are joined with a &, urlencoded,
     * and getted to
     * http://www.pinterest.com/resource/BoardPickerBoardsResource/get/
     *
     * source_url (string): /pin/create/bookmarklet/?url=http%3A%2F%2Fyellow5.com%2F
     * pinFave (numeric): 1
     * description (string): YELLOW+NUMPER+FIVE
     * data={"options":{"filter":"all","field_set_key":"board_picker"},"context":{}}
     * module_path=App()>PinBookmarklet()>PinCreate()>PinForm()>BoardPickerDropdownButton(view_type=compact, dropdown_options=[object Object], selected_index=0, disabled=false, color="", arrow=down, label_module=[object Object], use_dropdown2=true, selected_board_id=null, resource=BoardPickerBoardsResource(filter=all))
     * _ (timestamp): 1422584574944
     */
    private $get_boards_url = "https://www.pinterest.com/resource/BoardPickerBoardsResource/get/";

    public function get_boards() {

        // Can't do anything if we're not logged in
        if( !$this->is_logged_in )
            return Pinterest::NOT_LOGGED_IN;


        // OK!  We're ready!  Prepare the board get JSON
        $data_json = array(
            "options" => array(
                "filter" => "all",
                "field_set_key" => "board_picker"
            ),
            "context" => array()
        );

        // And prepare the get data array
        $get = array(
            "source_url" => "/pin/create/bookmarklet/?url=",
            "pinFave" => "1",
            "description" => "",
            "data" => json_encode($data_json, JSON_FORCE_OBJECT)
        );

        // Now make the get string
        $get_arr = array();
        foreach( $get as $k => $v )
            $get_arr[] = "{$k}=" . urlencode($v);

        $get_string = join("&", $get_arr);

        // Fix up parens
        $get_string = Pinterest::fix_encoding($get_string);


        // Now set up the CURL call
        $ch = \curl_init($this->get_boards_url . "?{$get_string}");
        \curl_setopt_array($ch, array(
            CURLOPT_COOKIEFILE => $this->cookie_jar,
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0",
            CURLOPT_REFERER => "https://www.pinterest.com/pin/create/bookmarklet/?url=&pinFave=1&description=",
            CURLOPT_HTTPHEADER => array(
                'Host: www.pinterest.com',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: en-US,en;q=0.5',
                'DNT: 1',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'X-Pinterest-AppState: active',
                'X-NEW-APP: 1',
                'X-APP-VERSION: 04cf8cc',
                'X-Requested-With: XMLHttpRequest'
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false   // remove me later
        ));

        // Run the CURL call
        $res = \curl_exec($ch);
        $json = json_decode($res, TRUE);
        \curl_close($ch);

        // If the result is json, we've succeeded!
        if( $json === null ) {
            return Pinterest::UNABLE_TO_GET_BOARDS;
        } else {

            // Ok, we got json--did we actually make an image preview?
            if( isset($json['resource_response']['data']['all_boards']) ) {

                // Pull out the board name and ID pair
                foreach( $json['resource_response']['data']['all_boards'] as $board )
                    $this->boards[$board['name']] = $board['id'];

                return Pinterest::SUCCESS;

            } else
                return Pinterest::UNABLE_TO_GET_BOARDS;

        }

    }






    /**
     * Get the logged-in account username
     */
    private $get_account_name_url = "https://www.pinterest.com/";
    public function get_account_name() {

        // Can't do anything if we're not logged in
        if( !$this->is_logged_in )
            return Pinterest::NOT_LOGGED_IN;


        // Now set up the CURL call
        $ch = \curl_init($this->get_account_name_url);
        \curl_setopt_array($ch, array(
            CURLOPT_COOKIEFILE => $this->cookie_jar,
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0",
            CURLOPT_REFERER => "",
            CURLOPT_HTTPHEADER => array(
                'Host: www.pinterest.com',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: en-US,en;q=0.5',
                'DNT: 1',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'X-Pinterest-AppState: active',
                'X-NEW-APP: 1',
                'X-APP-VERSION: 04cf8cc',
                'X-Requested-With: XMLHttpRequest'
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false   // remove me later
        ));

        // Run the CURL call
        $res = \curl_exec($ch);
        $json = json_decode($res, TRUE);
        \curl_close($ch);


        // If the result is json, we've succeeded!
        if( $json === null ) {
            return Pinterest::UNABLE_TO_GET;
        } else {

            // Ok, we got json--did we actually get the data we want?
            if( isset($json['resource_data_cache'][1]['resource']['options']['username']) )
                return $json['resource_data_cache'][1]['resource']['options']['username'];
            else
                return Pinterest::UNABLE_TO_GET_ACCOUNT_NAME;

        }

    }








    /**
     * Generate an image preview on Pinterest's AWS.
     *
     * NOTES on image preview:
     *
     * The follow variables are joined with a &, urlencoded,
     * and posted to
     * http://www.pinterest.com/resource/ImagePreviewResource/create/
     *
     * source_url (string): /pin/create/bookmarklet/?url=http%3A%2F%2Fyellow5.com%2Fpokey%2F
     * pinFave (numeric): 1
     * description (string): yellow5.com
     * data (JSON): {"options":{"content_type":"image/jpeg","base64_payload":"/9j/4A ... ALL BASE64-ENCODED DATA OF AN IMAGE ... xz8/vQB//2Q=="},"context":{}}
     *
     */
    private $image_preview_url = "https://www.pinterest.com/resource/ImagePreviewResource/create/";

    public function generate_image_preview($path) {

        // Can't do anything if we're not logged in
        if( !$this->is_logged_in )
            return Pinterest::NOT_LOGGED_IN;

        // Need a pin URL and description, too
        if( !$this->pin_url )
            return Pinterest::MISSING_PIN_URL;

        if( !$this->pin_description )
            return Pinterest::MISSING_PIN_DESCRIPTION;

        // Can't make a preview if the image doesn't exist
        if( !$path )
            return Pinterest::IMAGE_DOESNT_EXIST;

        $image = file_get_contents($path);
        if( !$image )
            return Pinterest::IMAGE_DOESNT_EXIST;

        // Save the image
        $image_tmpfile = tempnam(sys_get_temp_dir(), "image");
        file_put_contents($image_tmpfile, $image);


        // OK!  We're ready!  Prepare the image preview JSON
        $data_json = array(
            "options" => array(
                "content_type" => mime_content_type($image_tmpfile),
                "base64_payload" => base64_encode($image)
            ),
            "context" => array()
        );

        // And prepare the post data array
        $post = array(
            "source_url" => "/pin/create/bookmarklet/?url=" . urlencode($this->pin_url),
            "pinFave" => "1",
            "description" => urlencode($this->pin_description),
            "data" => json_encode($data_json, JSON_FORCE_OBJECT)
        );

        // Now make the post string
        $post_arr = array();
        foreach( $post as $k => $v )
            $post_arr[] = "{$k}=" . urlencode($v);

        $post_string = join("&", $post_arr);

        // Fix up parens
        $post_string = Pinterest::fix_encoding($post_string);


        // Now set up the CURL call
        $ch = \curl_init($this->image_preview_url);
        \curl_setopt_array($ch, array(
            CURLOPT_COOKIEFILE => $this->cookie_jar,
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0",
            CURLOPT_REFERER => "https://www.pinterest.com/pin/create/bookmarklet/?url=" . urlencode($this->pin_url) . "&pinFave=1&description=" . urlencode($this->pin_url),
            CURLOPT_HTTPHEADER => array(
                'Host: www.pinterest.com',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: en-US,en;q=0.5',
                'DNT: 1',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'X-Pinterest-AppState: active',
                'X-CSRFToken: ' . $this->csrf_token,
                'X-NEW-APP: 1',
                'X-APP-VERSION: 04cf8cc',
                'X-Requested-With: XMLHttpRequest'
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_string,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false   // remove me later
        ));

        // Run the CURL call
        $res = \curl_exec($ch);
        $json = json_decode($res, TRUE);
        \curl_close($ch);

        // If the result is json, we've succeeded!
        if( $json === null ) {
            return Pinterest::UNABLE_TO_CREATE_IMAGE_PREVIEW;
        } else {

            // Ok, we got json--did we actually make an image preview?
            if( isset($json['resource_response']['data']['image_url']) )
                return $json['resource_response']['data']['image_url'];
            else
                return Pinterest::UNABLE_TO_CREATE_IMAGE_PREVIEW;

        }

    }




    /**
     * Submit the pin!
     *
     * NOTES on pinning:
     *
     * The follow variables are joined with a &, urlencoded,
     * and posted to
     * http://www.pinterest.com/resource/PinResource/create/
     *
     * source_url=/pin/create/bookmarklet/?url=http%3A%2F%2Fyellow5.com%2Fpokey%2F
     * pinFave=1
     * description=HOORAY
     * data={"options":{"board_id":"01234567890123456789","description":"HOORAY","link":"http://yellow5.com/pokey/","image_url":"https://s3.amazonaws.com/media.pinterest.com/previews/012345abcdef.jpeg","method":"bookmarklet","is_video":null},"context":{}}
     * module_path=App()>PinBookmarklet()>PinCreate()>PinForm(description=HOORAY, default_board_id=null, show_cancel_button=true, cancel_text=Close, link=http://yellow5.com/pokey/, show_uploader=false, image_url=https://s3.amazonaws.com/media.pinterest.com/previews/012345abcdef.jpeg, is_video=null, heading=Pick a board, pin_it_script_button=true)
     */
    private $create_pin_url = "https://www.pinterest.com/resource/PinResource/create/";

    public function pin($board_id) {

        // Can't do anything if we're not logged in
        if( !$this->is_logged_in )
            return Pinterest::NOT_LOGGED_IN;

        // Need a pin URL and description, too
        if( !$this->pin_url )
            return Pinterest::MISSING_PIN_URL;

        // Is it a repin?
        if( Pinterest::is_repin_url($this->pin_url) )
            return $this->repin($board_id);
        else {

            // Not repinning?  We need description and image preview
            if( !$this->pin_image_preview )
                return Pinterest::MISSING_PIN_IMAGE_PREVIEW;
            if( !$this->pin_description )
                return Pinterest::MISSING_PIN_DESCRIPTION;

        }



        // OK!  We're ready!  Prepare the pin JSON
        $data_json = array(
            "options" => array(
                "board_id" => $board_id,
                "description" => $this->pin_description,
                "link" => $this->pin_url,
                "image_url" => $this->pin_image_preview,
                "method" => "bookmarklet",
                "is_video" => null
            ),
            "context" => array()
        );

        // Set up the "module path" data
        $module_path = "module_path=App()>PinBookmarklet()>PinCreate()>PinForm(description=, default_board_id=null, show_cancel_button=true, cancel_text=Close, link=, show_uploader=false, image_url=, is_video=null, heading=Pick a board, pin_it_script_button=true)";

        // And prepare the post data array
        $post = array(
            "source_url" => "/pin/create/bookmarklet/?url=" . urlencode($this->pin_url),
            "pinFave" => "1",
            "description" => urlencode($this->pin_description),
            "data" => json_encode($data_json, JSON_FORCE_OBJECT),
            "module_path" => urlencode($module_path)
        );

        // Now make the post string
        $post_arr = array();
        foreach( $post as $k => $v )
            $post_arr[] = "{$k}=" . urlencode($v);

        $post_string = join("&", $post_arr);

        // Fix up parens
        $post_string = Pinterest::fix_encoding($post_string);


        // Now set up the CURL call
        $ch = \curl_init($this->create_pin_url);
        \curl_setopt_array($ch, array(
            CURLOPT_COOKIEFILE => $this->cookie_jar,
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0",
            CURLOPT_REFERER => "https://www.pinterest.com/pin/create/bookmarklet/?url=&pinFave=1&description=",
            CURLOPT_HTTPHEADER => array(
                'Host: www.pinterest.com',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: en-US,en;q=0.5',
                'DNT: 1',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'X-Pinterest-AppState: active',
                'X-CSRFToken: ' . $this->csrf_token,
                'X-NEW-APP: 1',
                'X-APP-VERSION: 04cf8cc',
                'X-Requested-With: XMLHttpRequest'
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_string,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false   // remove me later
        ));

        // Run the CURL call
        $res = \curl_exec($ch);
        \curl_close($ch);
        $json = json_decode($res, TRUE);

        // If the result is json, we've succeeded!
        if( $json === null ) {
            $this->last_pin_id = 0;
            return Pinterest::UNABLE_TO_PIN;
        } else {
            $this->last_pin_id = $json['resource_response']['data']['id'];
            return Pinterest::SUCCESS;
        }


    }






    /**
     * Repin an existing pin
     *
     * NOTES on repinning:
     *
     * The follow variables are joined with a &, urlencoded,
     * and posted to
     * http://www.pinterest.com/resource/RepinResource/create/
     *
     * source_url=/pin/33124503410/
     * data={"options":{"board_id":"2098031983153","description":"test","link":"http://example.com/","is_video":false,"pin_id":"314018941820"},"context":{}}
     * module_path=App()>Closeup(resource=PinResource(link_selection=true, fetch_visual_search_objects=true, id=))>PinActionBar(resource=PinResource(link_selection=true, fetch_visual_search_objects=true, id=))>ShowModalButton(module=PinCreate)#Modal(module=PinCreate(resource=PinResource(id=)))
     */
    private $repin_url = "https://www.pinterest.com/resource/RepinResource/create/";

    public function repin($board_id) {

        // Can't do anything if we're not logged in
        if( !$this->is_logged_in )
            return Pinterest::NOT_LOGGED_IN;

        // Let's make sure the pin URL conforms to the pinterest URL format
        if( !preg_match("/https?:\/\/(?:www\.|)pinterest\.com\/pin\/(\d+)/", $this->pin_url, $matches) )
            return Pinterest::BAD_REPIN_URL;

        // Get the original pin ID
        $pin_id = $matches[1];

        // And get the source URL and description
        $repin_source = file_get_contents(trim($this->pin_url));
        if( !$repin_source )
            return Pinterest::REPIN_URL_NOT_FOUND;

        // this is the source URL
        preg_match("<meta property=\"og:see_also\" name=\"og:see_also\" content=\"(.*?)\" data-app>", $repin_source, $matches);
        $pin_url = $matches[1];

        // this is the source URL
	if( $this->pin_description )
	  $pin_description = $this->pin_description;
	else {
	    preg_match("<meta property=\"og:description\" name=\"og:description\" content=\"(.*?)\" data-app>", $repin_source, $matches);
	    $pin_description = html_entity_decode($matches[1]);
	    $pin_description = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $pin_description);
	}


        // OK!  We're ready!  Prepare the pin JSON
        $data_json = array(
            "options" => array(
                "board_id" => $board_id,
                "description" => stripslashes($pin_description),
                "link" => stripslashes($pin_url),
                "is_video" => null,
                "pin_id" => $pin_id,
            ),
            "context" => array()
        );

        // Set up the "module path" data
        $module_path = "App()>Closeup(resource=PinResource(link_selection=true, fetch_visual_search_objects=true, id=))>PinActionBar(resource=PinResource(link_selection=true, fetch_visual_search_objects=true, id=))>ShowModalButton(module=PinCreate)#Modal(module=PinCreate(resource=PinResource(id=)))";

        // And prepare the post data array
        $post = array(
            "source_url" => "/pin/{$pin_id}/",
            "data" => json_encode($data_json, JSON_FORCE_OBJECT),
            "module_path" => urlencode($module_path)
        );

        // Now make the post string
        $post_arr = array();
        foreach( $post as $k => $v )
            $post_arr[] = "{$k}=" . urlencode($v);

        $post_string = join("&", $post_arr);

        // Fix up parens
        $post_string = Pinterest::fix_encoding($post_string);


        // Now set up the CURL call
        $ch = \curl_init($this->repin_url);
        \curl_setopt_array($ch, array(
            CURLOPT_COOKIEFILE => $this->cookie_jar,
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0",
            CURLOPT_REFERER => $this->pin_url,
            CURLOPT_HTTPHEADER => array(
                'Host: www.pinterest.com',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: en-US,en;q=0.5',
                'DNT: 1',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'X-Pinterest-AppState: active',
                'X-CSRFToken: ' . $this->csrf_token,
                'X-NEW-APP: 1',
                'X-APP-VERSION: 04cf8cc',
                'X-Requested-With: XMLHttpRequest'
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_string,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false   // remove me later
        ));

        // Run the CURL call
        $res = \curl_exec($ch);
        \curl_close($ch);
        $json = json_decode($res, TRUE);

        // If the result is json, we've succeeded!
        if( $json === null ) {
            $this->last_pin_id = 0;
            return Pinterest::UNABLE_TO_REPIN;
        } else {
            $this->last_pin_id = $json['resource_response']['data']['id'];
            return Pinterest::SUCCESS;
        }


    }








    /**
     * Delete a pin
     *
     * NOTES on deleting:
     *
     * The follow variables are joined with a &, urlencoded,
     * and posted to
     * http://www.pinterest.com/resource/PinResource/delete/
     *
     * source_url=/pin/3314398133410/
     * data={"options":{"id":"31098183153"},"context":{}}
     * module_path:Modal()>ConfirmDialog(template=delete_pin, ga_category=pin_delete)
     */
    private $delete_pin_url = "https://www.pinterest.com/resource/PinResource/delete/";

    public function delete_pin($pin_id) {

        // Can't do anything if we're not logged in
        if( !$this->is_logged_in )
            return Pinterest::NOT_LOGGED_IN;

        // OK!  We're ready!  Prepare the pin JSON
        $data_json = array(
            "options" => array(
                "id" => $pin_id
            ),
            "context" => array()
        );

        // Set up the "module path" data
        $module_path = "Modal()>ConfirmDialog(template=delete_pin, ga_category=pin_delete)";

        // And prepare the post data array
        $post = array(
            "source_url" => "/pin/{$pin_id}/",
            "data" => json_encode($data_json, JSON_FORCE_OBJECT),
            "module_path" => urlencode($module_path)
        );

        // Now make the post string
        $post_arr = array();
        foreach( $post as $k => $v )
            $post_arr[] = "{$k}=" . urlencode($v);

        $post_string = join("&", $post_arr);

        // Fix up parens
        $post_string = Pinterest::fix_encoding($post_string);


        // Now set up the CURL call
        $ch = \curl_init($this->delete_pin_url);
        \curl_setopt_array($ch, array(
            CURLOPT_COOKIEFILE => $this->cookie_jar,
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0",
            CURLOPT_REFERER => "https://www.pinterest.com/pin/{$pin_id}/",
            CURLOPT_HTTPHEADER => array(
                'Host: www.pinterest.com',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: en-US,en;q=0.5',
                'DNT: 1',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'X-Pinterest-AppState: active',
                'X-CSRFToken: ' . $this->csrf_token,
                'X-NEW-APP: 1',
                'X-APP-VERSION: 04cf8cc',
                'X-Requested-With: XMLHttpRequest'
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_string,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false   // remove me later
        ));

        // Run the CURL call
        $res = \curl_exec($ch);
        \curl_close($ch);
        $json = json_decode($res, TRUE);
	print_r($json);

        // If the result is json, we've succeeded!
        if( $json === null ) {
            return Pinterest::UNABLE_TO_DELETE;
        } else {
            return Pinterest::SUCCESS;
        }


    }






    /**
     * Fix URL-encoding for some characters
     */
    public static function fix_encoding($str) {
        return str_replace(
            array("%28", "%29", "%7E"),
            array("(", ")", "~"),
            $str
        );
    }


    /**
     * Is the pin URL a repin URL?
     */
    public static function is_repin_url($str) {
      return preg_match("/https?:\/\/(?:www\.|)pinterest\.com\/pin\/(\d+)/", $str, $matches);
    }




    /**
     * Get a pin's preview URL
     */
    public static function get_pin_preview_url($pin_url) {

        // Let's make sure the pin URL conforms to the pinterest URL format
        if( !preg_match("/https?:\/\/(?:www\.|)pinterest\.com\/pin\/(\d+)/", $pin_url, $matches) )
            return Pinterest::BAD_REPIN_URL;

        // Get the original pin ID
        $pin_id = $matches[1];

        // And get the source URL and description
        $repin_source = file_get_contents(trim($pin_url));

        // this is the source URL
        preg_match("<meta property=\"og:image\" name=\"og:image\" content=\"(.*?)\" data-app>", $repin_source, $matches);
        $image_preview_url = $matches[1];

        return $image_preview_url;

    }




    /**
     * Get a pin's description
     */
    public static function get_pin_description($pin_url) {

        // Let's make sure the pin URL conforms to the pinterest URL format
        if( !preg_match("/https?:\/\/(?:www\.|)pinterest\.com\/pin\/(\d+)/", $pin_url, $matches) )
            return Pinterest::BAD_REPIN_URL;

        // Get the original pin ID
        $pin_id = $matches[1];

        // And get the source URL and description
        $repin_source = file_get_contents(trim($pin_url));

        // this is the source URL
        preg_match("<meta property=\"og:description\" name=\"og:description\" content=\"(.*?)\" data-app>", $repin_source, $matches);
        $description = html_entity_decode($matches[1]);
        $description = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $description);

        return $description;

    }





    /**
     * Get a pin's image URL
     */
    public static function get_pin_image_url($pin_url) {

        // Let's make sure the pin URL conforms to the pinterest URL format
        if( !preg_match("/https?:\/\/(?:www\.|)pinterest\.com\/pin\/(\d+)/", $pin_url, $matches) )
            return Pinterest::BAD_REPIN_URL;

        // Get the original pin ID
        $pin_id = $matches[1];

        // And get the source URL and description
        $repin_source = file_get_contents(trim($pin_url));

        // this is the source URL
        preg_match("<meta property=\"twitter:image:src\" name=\"twitter:image:src\" content=\"(.*?)\" data-app>", $repin_source, $matches);
        $image_url = $matches[1];

        return $image_url;

    }





    /**
     * Get a pin's pinner
     */
    public static function get_pin_pinner($pin_url) {

        // Let's make sure the pin URL conforms to the pinterest URL format
        if( !preg_match("/https?:\/\/(?:www\.|)pinterest\.com\/pin\/(\d+)/", $pin_url, $matches) )
            return Pinterest::BAD_REPIN_URL;

        // Get the original pin ID
        $pin_id = $matches[1];

        // And get the source URL and description
        $repin_source = file_get_contents(trim($pin_url));

        // this is the source URL
        preg_match("<meta property=\"pinterestapp:pinner\" name=\"pinterestapp:pinner\" content=\"(.*?)\" data-app>", $repin_source, $matches);
        $pinner = $matches[1];
        $pinner = str_replace("https://www.pinterest.com/", "", $pinner);
        $pinner = rtrim($pinner, "/");

        return $pinner;

    }





    /**
     * Get a CSRF token from the given cookie file
     */
    public static function get_csrf_token($file) {

        // Failsafe
        if( !file_exists($file) )
            return null;

        // Step through the file, line by line..
        foreach( file($file) as $line ) {

            $line = trim($line);

            // Skip blank and comment lines
            if( $line == "" or substr($line, 0, 2) == "# " )
                continue;

            list($domain, $tailmatch, $path, $secure, $expires, $name, $value) = explode("\t", $line);

            // Do we have our token?
            if( $name == "csrftoken" )
                return $value;

        }

        // Couldn't find it..
        return null;

    }

}