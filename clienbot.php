<?php
header("Content-Type: text/html; charset=utf-8");

define('CLIEN_LOGIN_URL', 'https://www.clien.net/cs2/bbs/login_check.php');
define('CLIEN_COMMENT_URL', 'http://www.clien.net/cs2/bbs/write_comment_update.php');
define('CLIEN_COOKIE_FILE', 'clien_cookie.txt');

define('CLIEN_ID', 		 'please_set_id');
define('CLIEN_PASSWORD', 'please_set_password');

$bottest 	= $_REQUEST['bottest'];
$bo_table	= $_REQUEST['bo_table'];
$wr_id		= $_REQUEST['wr_id'];
$comment_id	= $_REQUEST['comment_id'];
$wr_content	= $_REQUEST['wr_content'];

function debug_print($mesg) {
	print $mesg;
}

function get_external_ip() {
	$url = "http://ip.keithscode.com/";
	$externalContent = file_get_contents($url);
	
	return $externalContent;
}

function clien_login($mb_id, $mb_password) {
	
	$post_data = http_build_query(
		array(
			'mb_id'			=> "$mb_id",
			'mb_password'	=> "$mb_password",
			'url'			=> "http://m.clien.net/cs3"
		)
	);

	$opts = array(
		'http'=>array(
			'method'	=> "POST",
			'header'	=> "Content-type: application/x-www-form-urlencoded",
			'content'	=> $post_data
		)
	);
	
	$context = stream_context_create($opts);
	$page = file_get_contents(CLIEN_LOGIN_URL, false, $context);

	$cookie = "";
	if (strstr($page, 'nowlogin')) {
		// login success	
		foreach($http_response_header as $s){
			if(preg_match('|^Set-Cookie:\s*([^=]+)=([^;]+);(.+)$|',$s,$parts)) {
				if (!strstr($parts[2], 'deleted')) {
					$cookie .= $parts[1] . '=' . $parts[2] . '; ';
				}				
			}
		}
	} else {
		// login fail
	}

	return $cookie;
}

function clien_write_comment($cookie, $data) {
	$post_data = http_build_query(
		array(
			'w'				=> "c",
			'bo_table'		=> "$data[bo_table]",
			'wr_id'			=> "$data[wr_id]",
			'comment_id'	=> "$data[comment_id]",
			'wr_content'	=> "$data[wr_content]",
		)
	);

	$header = "Content-type: application/x-www-form-urlencoded\r\n" . "Cookie: " . $cookie;
	$opts = array(
		'http'=>array(
			'method'	=> "POST",
			'header'	=> $header,
			'content'	=> $post_data
		)
	);
	
	$context = stream_context_create($opts);

	$page = file_get_contents(CLIEN_COMMENT_URL, false, $context);
	$page = strip_tags($page);
	echo $page;		
	
	if (strstr($page, "login.php")) {
		// 로그인이 필요함
	} else if (strstr($page, "location.replace")) {
		// 글쓰기 성공?
		return true;
	} else {

	}
	return false;
}

function load_cookie_from_file() {
	$cookie = "";
	
	$fp = fopen(CLIEN_COOKIE_FILE, "r");
	if ($fp) {
		$cookie = fgets($fp);		
		fclose($fp);
	}
	return $cookie;
}

function save_cookie_to_file($cookie) {
	
	$ret = false;
	$fp = fopen(CLIEN_COOKIE_FILE, "w");
	if ($fp) {
		fwrite($fp, $cookie);
		fclose($fp);
		$ret = true;
	} else {
		echo "cookie file write error";
	}
	return $ret;
}

function write_comment_simple($data) {
	$success = false;
	$cookie = clien_login(CLIEN_ID, CLIEN_PASSWORD);
	if ($cookie) {
		// login 성공
		
		// 코멘트 쓰기
		$success = clien_write_comment($cookie, $data);
	}
	return $success;
}

function write_comment_by_file($data) {
	$success = false;
	$cookie = load_cookie_from_file();
	if ($cookie) {
		// 코멘트 쓰기
		debug_print("파일 쿠키로 코멘트 쓰기\n");
		$success = clien_write_comment($cookie, $data);
		if ($success == false) {
			// 코멘트 쓰기 실패
			debug_print("코멘트 쓰기 실패\n");
		}
	} else {
		// 쿠키 파일이 없음
	}

	if ($success == false) {
		$cookie = clien_login(CLIEN_ID, CLIEN_PASSWORD);
		if ($cookie) {
			// login 성공
			
			debug_print("로그인해서 코멘트 쓰기\n");
			
			// 다음을 위해서 cookie 파일 저장
			save_cookie_to_file($cookie);
			
			// 코멘트 쓰기
			$success = clien_write_comment($cookie, $data);
		} else {
			// login 실패
		}	
	}
	
	return $success;
}

function login_test() {
	$cookie = clien_login(CLIEN_ID, CLIEN_PASSWORD);
	
	if ($cookie) {
		echo "##SUCCESS##";
	} else {
		echo "##FAIL:LOGIN##";				
	}		
}

debug_print("<pre>\n");

if (isset($bottest)) {
	if (CLIEN_ID == "please_set_id" || CLIEN_PASSWORD == 'please_set_password')
	{
		echo "##FAIL:ID_PWD##";
	} else {
		login_test();
	}
} else if (!$bo_table) {
	// 웹페이지 테스트
	
	// step 1. 설치한 서버의 가상 ip 알기
	$external_ip = get_external_ip();
	
	/*
	echo "\nSTEP 1. NAS 나 개인서버에 이 파일을 올리고 웹서비스를 활성화 하세요.\n";

	echo " Synology 의 경우 메인 메뉴 > 제어판 > 웹 서비스 > 웹 응용 프로그램으로 가서 Web Station 활성화를 선택합니다. \n\n";
	
	echo "\nSTEP 2. 외부내트워크에서 현재 웹페이지에 접근할 수 있도록 포트포워딩 및 방화벽을 설정하세요. \n";

	echo "\nSTEP 3. clienbot.php 파일에서 CLIEN_ID 의 please_set_id 를 클리앙 아이디로, CLIEN_PASSWORD 에서 please_set_password 를 클리앙 비밀번호로 바꾸세요\n";
	
	if ($external_ip) {
		$php_self = $_SERVER["PHP_SELF"];
		$callback = "http://" . $external_ip . $php_self;
		
		echo "\nSTEP 4. 텔레그램 클리앙봇에 다음을 입력하세요.\n/callback " . $callback . "\n";		
	
		echo "\nSTEP 5. 클리앙봇의 응답이 [callback 을 설정했습니다.] 라고 오는지 확인하세요\n";
		
		echo "\nSTEP 6. 텔레그램에서 바로 답장을 테스트 하세요.\n";			
	} else {
		echo "\nSTEP 4. ERROR - 외부 아이피를 알 수 없습니다.\n";
	}
	*/
		
	echo "\n\n외부 아이피 : " . $external_ip . "\n";
	if (CLIEN_ID != "please_set_id") {
		echo "\n\nLOGIN TEST : ";
		login_test();	
	} else {
		echo "\nclienbot.php 파일에서 8번째 줄 CLIEN_ID 의 please_set_id 를 클리앙 아이디로, 9번째 줄 CLIEN_PASSWORD 에서 please_set_password 를 클리앙 비밀번호로 바꾸세요\n";
	}
} else {
	// 실재 동작하는 루틴
	$data = array(
		"bo_table"		=> "$bo_table",
		"wr_id"			=> "$wr_id",
		"comment_id" 	=> "$comment_id",
		"wr_content"	=> "$wr_content",
	);		

	// 1. comment 작성때마다 로그인 한 후에 comment 작성, 빈번한 로그인이 이뤄지기 때문에 테스트 용으로만 사용
	// write_comment_simple($data);

	// 2. 빈번한 로그인을 방지하기 위해서 cookie 파일을 사용하는 방법
	// 현재 파일이 있는 디렉토리에 웹서버 계정이 폴더에 write 권한이 있어야 함.
	$success = write_comment_by_file($data);
	if ($success) {
		echo "##SUCCESS##";
	} else {
		echo "##FAIL:comment##";
	}
}


debug_print("\n</pre>\n");

?>
