<?php
    // XML 파일 형식으로 UniProt API를 통해 검색에 대한 내용을 가져오는 함수
    // search_keyword, 검색한 내용
    // search_columns, 검색할 DB의 테이블명
    function UNIPROT_KB_GetSearchContents($search_keyword, $search_columns, $start_point, $end_point, $type, $filter) {
        $__UNIPROT_WEBSITE_API_BASE_URL = 'https://www.uniprot.org/uniprot/?query=';
        $__UNIPROT_WEBSITE_API_BASE_REVIEWED_URL = 'https://www.uniprot.org/uniprot/?query=reviewed:yes+AND+';
        $__UNIPROT_WEBSITE_API_BASE_UNREVIEWED_URL = 'https://www.uniprot.org/uniprot/?query=reviewed:no+AND+';

        // $search_keyword의 내용이 비어있을 경우 에러메세지 출력
        if(empty($search_keyword)) {
            echo '<br>UNIPROT KB API ERROR MSSAGE: Empty search keyword. please check it.<br>';
            exit();
        }

        // $search_columns, $start_point, $end_point, $type의 내용이 default 이거나 비어있을 경우
        if($search_columns == 'default' || $search_columns == 'DEFAULT' || $search_columns == '') {
            $search_columns = 'id,entry%20name,reviewed,protein%20names,genes,organism,length';

        } else if(empty($search_columns)) {
            echo '<br>UNIPROT KB API ERROR MSSAGE: Empty columns parameter. please check it.<br>';
            exit();
        }

        if($start_point == 'default' || $start_point == 'DEFAULT' || $start_point == '') {
            $start_point = 0;
        } else if ($start_point < 0) {
            echo '<br>UNIPROT KB API ERROR MSSAGE: Empty or error offset parameter. please check it.<br>';
            exit();
        } 

        if($end_point == 'default' || $end_point == 'DEFAULT' || $end_point == '') {
            $end_point = 25;

        } else if(empty($end_point)) {
            echo '<br>UNIPROT KB API ERROR MSSAGE: Empty limit parameter. please check it.<br>';
            exit();
        }

        if($type == 'default' || $type == 'DEFAULT' || $type == '') {
            $type = 'tab';

        } else if(empty($type)) {
            echo '<br>UNIPROT KB API ERROR MSSAGE: Empty type parameter. please check it.<br>';
            exit();
        }

        if(!$type == 'tab' || !$type == 'xml') {
            echo '<br>UNIPROT KB API ERROR MSSAGE: Invalid file type. please check it.<br>';
            exit();
        }
        
        /*******************************************************
        search_columns에 들어가는 default 값 종류
        1. id
        2. entry name
        3. reviewed
        4. protein names
        5. genes
        6. organism
        7. length

        여러가지의 종류를 불어오고 싶을 경우 ,(콤마)를 찍어 구분
        *******************************************************/

        // 전달 받은 params 조합
        $params = $search_keyword.'&sort=score'.'&columns='.$search_columns.'&offset='.$start_point.'&limit='.$end_point.'&format='.$type;

        // UniProt API basic request URL
        $UNIPROT_KB_GET_SEARCH_CONTENTS_URL = $__UNIPROT_WEBSITE_API_BASE_URL.$params;
        // UniProt API reviewed request URL
        $UNIPROT_KB_GET_SEARCH_REVIEWED_URL = $__UNIPROT_WEBSITE_API_BASE_REVIEWED_URL.$params;
        // UniProt API unreviewed request URL
        $UNIPROT_KB_GET_SEARCH_UNREVIEWED_URL = $__UNIPROT_WEBSITE_API_BASE_UNREVIEWED_URL.$params;

        // filter가 a, r, u인지에 따라 불러올 API URL을 변경, a가 기본적인 URL로 코딩되어 있으므로 r, u에 대해서만 예외처리
        if(!strcmp($filter, 'r')) $UNIPROT_KB_GET_SEARCH_CONTENTS_URL = $__UNIPROT_WEBSITE_API_BASE_REVIEWED_URL.$params;
        else if(!strcmp($filter, 'u')) $UNIPROT_KB_GET_SEARCH_CONTENTS_URL = $__UNIPROT_WEBSITE_API_BASE_UNREVIEWED_URL.$params;

        if($type == 'tab') {
            // 가져온 TAB 결과 저장하기
            $fp = fopen($UNIPROT_KB_GET_SEARCH_CONTENTS_URL, "r");
            $file_contents = Array();

            while($row = fgets($fp)){
                $row = explode("\t", $row);
                $file_contents[] = $row;
            }
            
            // 가져온 결과 반환
            return array('result'=>$file_contents, 
                        'request_url'=>$UNIPROT_KB_GET_SEARCH_CONTENTS_URL, 
                        'reviewed_url'=>$UNIPROT_KB_GET_SEARCH_REVIEWED_URL,
                        'unreviewed_url'=>$UNIPROT_KB_GET_SEARCH_UNREVIEWED_URL
                    );

        } else if($type == 'xml') {
            // UniProt 서버를 통해 검색한 XML 파일 가져오기
            $UNIPROT_KB_SEARCH_CONTENTS_GET_FILE = file_get_contents($UNIPROT_KB_GET_SEARCH_CONTENTS_URL);

            // 가져온 XML 결과 저장하기
            $UNIPROT_KB_SEARCH_RESULT = simplexml_load_string($UNIPROT_KB_SEARCH_CONTENTS_GET_FILE) or die("Error.");

            // 가져온 결과 반환
            return array('result'=>$UNIPROT_KB_SEARCH_RESULT, 
                        'request_url'=>$UNIPROT_KB_GET_SEARCH_CONTENTS_URL, 
                        'reviewed_url'=>$UNIPROT_KB_GET_SEARCH_REVIEWED_URL,
                        'unreviewed_url'=>$UNIPROT_KB_GET_SEARCH_UNREVIEWED_URL
                    );

        } else {

            echo '<br>UNIPROT KB API ERROR MSSAGE: Someting is wrong. please check correct using function.<br>';
            exit();
        }

        // 가져온 XML의 내용을 보고싶다면 print_r($UNIPROT_KB_SEARCH_RESULT);
    }

    /********************************************************************************************************************************/

    // UNIPROT의 Proteins API를 이용하여 원하는 Accession ID값으로부터 해당 ID의 상세 내용을 JSON으로 가져오는 함수
    function UNIPROT_PROTEINS_GetAccessionContents($assession) {
        // 받아온 파라미터의 내용이 비어있을 경우의 예외처리, 비어있을 경우 함수 실행이 중단
        if(empty($assession)) {
            echo '<br>UNIPROT PROTEINS API ERROR MSSAGE: Empty assession parameter. please check it.<br>';
            exit();
        }

        // 파라미터의 내용이 모두 채워져 있을 경우 함수가 실행되는 부분의 시작지점
        // UniProt의 Proteins API의 BASE URL
        $__PROTEINS_ACCESSION_API_BASE_URL = 'https://www.ebi.ac.uk/proteins/api/proteins/';

        // UniProt의 Proteins API로 가져올 내용 주소
        $get_proteins_contents_url = $__PROTEINS_ACCESSION_API_BASE_URL.$assession;

        // UniProt Proteins API 서버를 통해 검색한 JSON 파일 가져오기
        $get_protein_contents = file_get_contents($get_proteins_contents_url);

        // 가져온 JSON 결과 저장 후 반환
        return json_decode($get_protein_contents, true);
    }

    /********************************************************************************************************************************/

    /* 검색 개수 조회 기능 */
    function UNIPROT_KB_GetSearchCount($UNIPROT_KB_GET_URL) {
        /* 전체 contents 개수가 header에 표시되어 curl을 이용하여 전체 검색 contents 수 가져오기 */
        $curl = curl_init($UNIPROT_KB_GET_URL);
            
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);

        $result = curl_exec($curl);

        curl_close($curl);

        $headers = UNIPROT_KB_GetHeadersArray($result);

        /* UniProt KB API의 Documents에 의하면 X-Total-Results 이름을 가진 헤더가 전체 검색 수를 의미 */
        return $headers['X-Total-Results'];
    }

    /********************************************************************************************************************************/

    /* CURL의 HEADER를 배열로 변환하는 과정 */
    /* 소스 출처, https://codeday.me/ko/qa/20190401/185929.html */
    function UNIPROT_KB_GetHeadersArray($response) {
        $headers = array();
        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

        foreach (explode("\r\n", $header_text) as $i => $line) {
            if ($i === 0)
                $headers['http_code'] = $line;
            else {
                list ($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }

        return $headers;
    }
?>