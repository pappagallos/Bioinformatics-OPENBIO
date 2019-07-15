<?php
// PHP의 Warning 경고 메세지가 출력되지 않도록 하는 소스
error_reporting(E_ALL^ E_WARNING);

// String의 이미지를 SVG로 변환하여 가져오는 API
function STRING_Get_Interaction_Content($type, $accession) {
    $__STRING_API_BASE_URL = 'https://string-db.org/api/'.$type.'/network?';
    $params = 'identifiers='.$accession.'&add_white_nodes=10&network_flavor=actions';

    // file_get_contents로 가져왔을 때 400 에러가 나지않았을 경우에만 return 소스를 하고 에러가 났을 경우 0을 return
    $STRING_GET_INTERACTION_CONTENTS = file_get_contents($__STRING_API_BASE_URL.$params);
    if($STRING_GET_INTERACTION_CONTENTS !== false) return $STRING_GET_INTERACTION_CONTENTS;
    else return 0;
}

?>