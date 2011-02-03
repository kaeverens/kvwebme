<?php
/**
  * Login Tests
  *
  * PHP Version 5
  *
  * @category Tests
  * @package  Webworks_Webme
  * @author   Belinda Hamilton <bhamilton@webworks.ie>
  * @license  GPL Version 2
  * @link     www.webworks.ie
**/

require_once 'PHPUnit/Framework/TestCase.php';

/**
  * Tests to login to the admin area
  *
  * @category Tests
  * @package  Webworks_Webme
  * @author   Belinda Hamilton <bhamilton@webworks.ie>
  * @license  GPL Version 2
  * @link     www.webworks.ie
**/
class LoginTests extends PHPUnit_Framework_TestCase{
	private $_curl_handle;
	private $_url;

	/**
	  * Constructor
	**/
	function LoginTests() {
	}
	/**
	  * Sets up the variables
	  *
	  * @return void
	**/
	function setUp() {
		$tmpCurlHandle=curl_init();
		$tmpUrl='http://127.0.0.1/?page=logout';
		curl_setopt($tmpCurlHandle, CURLOPT_URL, $tmpUrl);
		curl_exec($tmpCurlHandle);
		curl_close($tmpCurlHandle);
		$this->_curl_handle = curl_init();
		$this->_url = 'http://127.0.0.1/ww.admin/pages.php';
		curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->_curl_handle, CURLOPT_URL, $this->_url);
		curl_setopt($this->_curl_handle, CURLOPT_POST, true);
	}
	/**
	  * Closes the session
	  *
	  * @return void
	**/
	function tearDown() {
		curl_close($this->_curl_handle);
	}
	/**
	  * Login with correct credentials
	  *
	  * @return void
	**/
	function testAuthorisedLogin() {
		$email='bhamilton@webworks.ie';
		$pass='ive8448';
		$fields
			=array(
				'email'=>$email,
				'password'=>$pass,
				'action'=>'login'
			);
		curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $fields);
		$response=curl_exec($this->_curl_handle);
		$dir=dirname(__FILE__);
		$hasPageForm=strpos($response, 'div id="pages-wrapper"');
		$hasLoginTab=strpos($response, 'div id="admin-login"');
		$this->assertEquals(false, $hasLoginTab);
		$this->assertNotEquals(false, $hasPageForm);
		curl_setopt($this->_curl_handle, CURLOPT_POST, false);
		$response=curl_exec($this->_curl_handle);
		$hasPageForm=strpos($response, 'div id="pages-wrapper"');
		$hasLoginForm=strpos($response, 'div id="admin-login"');
		$this->assertNotEquals(false, $hasLoginForm);
		$this->assertEquals(false, $hasPageForm);
	}
	/**
	  * Tests a user logging in with the wrong password
	  *
	  * @return void
	**/
	function testWrongPasswordLogin() {
		$email='bhamilton';
		$password='wrongpass';
		$fields
			=array(
				'email'=>$email,
				'password'=>$password,
				'action'=>'login'
			);
		curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $fields);
		$response=curl_exec($this->_curl_handle);
		$hasPageForm=strpos($response, 'div id="pages-wrapper"');
		$hasLoginForm=strpos($response, 'div id="admin-login"');
		$this->assertEquals(false, $hasPageForm);
		$this->assertNotEquals(false, $hasLoginForm);
	}
	/**
	  * Tests that a non admin with a user account can't login to the admin area
	  *
	  * @return void
	**/
	function testNonAdminLogin() {
		$email='belinda0304@hotmail.com';
		$password='belindapass';
		$fields
			=array(
				'email'=>$email,
				'password'=>$password,
				'action'=>'login'
			);
		curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $fields);
		$response=curl_exec($this->_curl_handle);
		$hasPageForm=strpos($response, 'div id="pages-wrapper"');
		$hasLoginForm=strpos($response, 'div id="admin-login"');
		$this->assertEquals(false, $hasPageForm);
		$this->assertNotEquals(false, $hasLoginForm);
	}
}
