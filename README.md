# clienbot_callback
클리앙봇에서 실시간 댓글답장을 하기위한 공개된 callback 페이지입니다.

STEP 1. NAS 나 개인서버에 이 파일을 올리고 웹서비스를 활성화 하세요.
>>Synology 의 경우 메인 메뉴 > 제어판 > 웹 서비스 > 웹 응용 프로그램으로 가서 Web Station 활성화를 선택합니다. 


STEP 2. 외부내트워크에서 현재 웹페이지에 접근할 수 있도록 포트포워딩 및 방화벽을 설정하세요. 


STEP 3. clienbot.php 파일에서 8번째 줄 please_set_id 를 클리앙 아이디로, 9번째 줄 please_set_password 를 클리앙 비밀번호로 바꾸세요.


STEP 4. pc 브라우저에서 http://[nas ip]/clienbot.php 로 접속해 보세요.
>>맨 마지막에 LOGIN TEST : ##SUCCESS## 라고 나오면 로그인 테스트는 성공한 것입니다.


STEP 5. 위의 결과 화면에서 외부 아이피 나오는 것을 텔레그램 클리앙봇에 다음처럼 입력하세요.

>>/callback http://xxx.xxx.xxx.xxx/clienbot.php

	
STEP 5. 클리앙봇의 응답이 [callback 을 설정했습니다.] 라고 오는지 확인하세요

	
STEP 6. 텔레그램에서 바로 답장을 테스트 하세요.

