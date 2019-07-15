<?php
    function NCBI_Entrez_GetAccessionContents($db, $accession, $rettype, $retmode) {
        // NCBI Entrez API의 BASE URL
        $__NCBI_ENTREZ_EFETCH_BASE_URL = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?';
        
        // 4가지 파라미터 중 하나라도 비어있을 경우 예외처리
        if(empty($db) || empty($accession) || empty($rettype) || empty($retmode)) {
            echo '<br>NCBI ENTREZ API ERROR MSSAGE: Empty something parameter. please check it.</br>';
            exit();
        }

        // 파라미터를 모두 받아왔을 경우 BASE URL과 파라미터 조합
        $params = 'db='.$db.'&id='.$accession.'&rettype='.$rettype.'&retmode='.$retmode;
        echo $__NCBI_ENTREZ_EFETCH_BASE_URL.$params;

        // 주소를 통해 XML 파일을 가져와서 파싱
        $NCBI_ENTREZ_SEARCH_CONTENTS_GET_FILE = file_get_contents($__NCBI_ENTREZ_EFETCH_BASE_URL.$params);
        $NCBI_ENTREZ_SEARCH_RESULT = simplexml_load_string($NCBI_ENTREZ_SEARCH_CONTENTS_GET_FILE) or die('Error.');

        // XML 파일을 결과값으로 반환
        return $NCBI_ENTREZ_SEARCH_RESULT;
    }
?>