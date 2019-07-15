<?php include_once 'Components/Header.php'; ?>
    <link href="Assets/style/openbio_protein_desc.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'Components/Menu.php'; ?>
    <?php include_once 'Core/NCBI_Entrez_API.php'; ?>
    <?php include_once 'Core/String_API.php'; ?>
    <?php
    // 검색 결과를 통해 사용자가 보고자하는 Entry 값을 기반으로 단백질 결과를 보여줌
    $accession = $_GET['entry'];

    // NCBI Entrez API를 이용해서 XML 파일 추출
    $protein_xml = NCBI_Entrez_GetAccessionContents('protein', $accession, 'gp', 'xml');

    // DOCUMENT가 언제 수정되었는지, 언제 만들어졌는지 정보 가져오기
    $create_date = $protein_xml->GBSeq->{'GBSeq_create-date'};
    $update_date = $protein_xml->GBSeq->{'GBSeq_update-date'};

    // Accession의 Function 정보 가져오기
    $accession_function = $protein_xml->GBSeq->GBSeq_comment;
    $accession_function = explode(';', $accession_function);
    $function_index = array(); // 특정 문자열 검색 결과 일치하는 배열 인덱스 저장용
    $accession_function_result = array(); // 특정 문자열이 검색된 배열의 시작 인덱스부터 마지막 인덱스까지 모두 저장할 배열 변수

    // 카운터를 세주는 변수 cnt를 선언하고 foreach로 [FUNCTION] 이라는 문구가 존재하는 배열 인덱스를 $function_index에 저장
    $cnt = 0;
    foreach($accession_function as $function) {
        // [FUNCTION] 문구 검색 후 있을 경우 반환되는 인덱스 정수 값을 is exist에 저장
        $is_exist = strpos($function, '[FUNCTION]');
        // 검색된 결과가 발견되었을 경우 해당 배열의 인덱스를 array_push 함수를 통해 $function_index 배열에 $cnt에 기록된 인덱스 번호 저장
        if($is_exist !== false) array_push($function_index, $cnt);
        // $cnt 증감
        $cnt++;
    }

    // $size 변수는 $function_index의 배열 개수를 저장
    $size = sizeof($function_index);
    // [FUNCTION] 문자열을 제거하고 { 으로 시작되는 지점부터 { 와 그 앞에 있는 모든 문자를 제거
    // NCBI의 API에서 내용을 가공하고 깔끔하게 보여주고자 불필요한 내용을 제거하는 작업
    for($i=0; $i<$size; $i++) {
        $accession_function[$function_index[$i]] = str_replace('[FUNCTION]', '', $accession_function[$function_index[$i]]); 
        $accession_function[$function_index[$i]] = substr($accession_function[$function_index[$i]], 0, strpos($accession_function[$function_index[$i]], '{'));
    }

    // 검색된 내용이 존재하는 배열의 시작 인덱스와 마지막 지점의 인덱스를 저장하는 변수
    $str_idx = $function_index[0];
    $end_idx = $function_index[$size-1];

    // 시작 인덱스 번호부터 마지막으로 검색된 인덱스의 번호까지 모두 $accession_function_result 배열에 저장
    for($i=$str_idx; $i<=$end_idx; $i++) {
        array_push($accession_function_result, $accession_function[$i]);
    }

    /*
    for($i=0; $i<sizeof($accession_function); $i++) {
        $is_exist = strpos($accession_function[$i], '[FUNCTION]');
        echo $is_exist;

        if($is_exist == 1) array_push($function_index, $cnt);
        $cnt = $cnt + 1;
    }
    */

    // Accession의 주명칭과 대체명칭을 분할하기 위해 정보 가공
    // $pri_name 첫 번째 인덱스에는 항상 주명칭이 저장되므로 $pri_name[0]을 불러오면 항상 주명칭을 불러옴
    $pri_name = $protein_xml->GBSeq->GBSeq_definition;
    $pri_name = str_replace('RecName: Full=', '', $pri_name);
    $pri_name = str_replace('AltName: Full=', '', $pri_name);
    $pri_name = explode(';', $pri_name);
    $pri_name = str_replace('Short=', 'Short Name: ', $pri_name);

    // Accession의 Gene 정보 가져오기
    // XML은 키 값에 GBSeq_feature-table '-' 특수문자가 있을 경우 {'KEY'} 형식으로 작성
    // https://stackoverflow.com/questions/14376259/how-to-fix-error-use-of-undefined-constant-id-assumed-id-when-parse-xml-fi/14376309#14376309
    $accession_gene = $protein_xml->GBSeq->{'GBSeq_feature-table'}->GBFeature[1]->GBFeature_quals->GBQualifier[0]->GBQualifier_value;
    
    // Accession의 Synonym 정보 가져오기
    $accession_synonym = $protein_xml->GBSeq->{'GBSeq_feature-table'}->GBFeature[1]->GBFeature_quals;

    // Accession의 Primary Accession 정보 가져오기
    $pri_accession = $protein_xml->GBSeq->{'GBSeq_primary-accession'};
    $pri_accession_version = $protein_xml->GBSeq->{'GBSeq_accession-version'};

    // Accession의 Organism 정보 가져오기
    $pri_organism = $protein_xml->GBSeq->GBSeq_source;

    // Accession의 Synonyms 정보 가져오기
    $pri_organism = $protein_xml->GBSeq->GBSeq_source;

    // Accession의 Locus 정보 가져오기
    $accession_locus = $protein_xml->GBSeq->GBSeq_locus;

    // Accession의 DNA Length 정보 가져오기
    $accession_length = $protein_xml->GBSeq->GBSeq_length;

    // Accession의 Taxonomy 정보 가져오기
    $accession_taxonomy = $protein_xml->GBSeq->GBSeq_taxonomy;
    $accession_taxonomy = str_replace(';', ' > ', $accession_taxonomy);

    // Accession의 Keywords 정보 가져오기
    $accession_keywords = $protein_xml->GBSeq->GBSeq_keywords;

    // Accession의 Sequence 정보 가져오기
    $accession_sequence = strtoupper($protein_xml->GBSeq->GBSeq_sequence);
    ?>
    
    <div class="openbio-wrapper">
        <!-- 검색한 Accession Entry 이름 및 ID 출력 -->
        <div class="seacrh-result-box">
            <div class="search-keyword">
                <?php echo $pri_name[0]; ?>
            </div>
        </div>

        <p class="date">Create <b><?php echo $create_date; ?></b> and Latest update <b><?php echo $update_date; ?></b></p>
        <!-- Accession Entry의 가장 많이 보는 주요 정보 출력 -->
        <table class="view-primary-table">
            <tr class="title">
                <td>Protein</td>
                <td>Gene</td>
                <td>Organism</td>
                <td>Accession</td>
                <td>Locus</td>
                <td>Length</td>
            </tr>
            <tr>
                <td><b><?php echo $pri_name[0]; ?></b></td>
                <td><b><?php echo $accession_gene; ?></b></td>
                <td><b><?php echo $pri_organism; ?></b></td>
                <td><?php echo $pri_accession; ?></td>
                <td><?php echo $accession_locus; ?></td>
                <td><?php echo $accession_length; ?></td>
            </tr>
        </table>

        <?php
        if(!empty($accession_function_result)) {
        ?>
        <table>
            <tr class="title">
                <td>Function</td>
            </tr>
            <tr>
                <td style="text-align: left;">
                <?php
                for($i=0; $i<sizeof($accession_function_result); $i++) {
                    echo $accession_function_result[$i];
                }
                ?>
                </td>
            </tr>
        </table>
        <?php
        }
        ?>

        <table>
            <tr>
                <td class="row-title" style="border-top-left-radius: 10px;">Protein names</td>
                <td class="row-contents">
                    <p><em>Recommended Name:</em></p>
                    <p><b><?php echo $pri_name[0]; ?></b></p><br>

                    <?php if(!empty($pri_name[1])) { ?>
                    <p><em>Alternative(s) Name:</em></p>
                    <ul>
                    <?php
                    for($i=1; $i<sizeof($pri_name); $i++) {
                        echo '<li>'.$pri_name[$i].'</li>';
                    }
                    ?>
                    <?php } ?>

                    </ul>
                </td>
            </tr>
            <tr>
                <td class="row-title">Gene names</td>
                <td class="row-contents">
                    <?php 
                    echo '<p><em>Name:</em> '.$accession_gene.'</p>';
                    if(!empty($accession_synonym->GBQualifier[1])) {
                        echo '<p><em>Synonyms:</em> ';
                        for($i=1; $i<sizeof($accession_synonym->GBQualifier); $i++) {
                            if($i == sizeof($accession_synonym->GBQualifier) - 1) echo $accession_synonym->GBQualifier[$i]->GBQualifier_value;
                            else echo $accession_synonym->GBQualifier[$i]->GBQualifier_value.', ';
                        }
                        echo '</p>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td class="row-title">Taxonomy</td>
                <td class="row-contents"><?php echo $accession_taxonomy; ?></td>
            </tr>
            <tr>
                <td class="row-title" style="border-bottom-left-radius: 10px;">Keywords</td>
                <td class="row-contents">
                <?php
                for($i=0; $i<sizeof($accession_keywords->GBKeyword); $i++) {
                    if($i == sizeof($accession_keywords->GBKeyword) - 1) echo $accession_keywords->GBKeyword[$i];
                    else echo $accession_keywords->GBKeyword[$i].', ';
                }
                ?>
                </td>
            </tr>
        </table>

        <?php
        // String의 API를 이용하여 데이터를 가져올 때 svg 형식으로 가져옴
        $string_svg_data = STRING_Get_Interaction_Content('svg', $pri_accession);
        // 0이 아닌 문자열이나 수가 return될 경우에만 interaction을 표시
        if($string_svg_data !== 0) {
        ?>
        <table>
            <tr class="title">
                <td>Interaction</td>
            </tr>
            <tr>
                <td>
                    <?php echo $string_svg_data; ?>
                </td>
            </tr>
        </table>
        <?php
        }
        ?>
    
        <table class="sequence">
            <tr class="title">
                <td>Sequence</td>
            </tr>
            <tr>
                <td id="sequence"><?php echo $accession_sequence; ?></td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <input type="text" id="seq_search" placeholder="If you know sum of specific sequence then typing here.">
                </td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    Located in <span id="locate">0</span> and Total is <span id="total">0.</span><?php echo ' ('.($accession_length * 110).'kDa, Length '.$accession_length.')'; ?>
                </td>
            </tr>
        </table>
    </div>

    <script>
    // sequence를 모두 불러와서 seq로 저장한 뒤 배열로 변환하여 seq_arr 변수에 다시 저장
    var seq = document.getElementById('sequence');
    var save_seq = seq.textContent;

    // 사용자가 검색한 특정 시퀀스 값을 이벤트 리스너의 keyup으로 업데이트하여 보여주는 부분
    var seq_search = document.getElementById('seq_search');
    var str_idx = -1;

    seq_search.addEventListener('keyup', function() {
        var locate = document.getElementById('locate'); // accsession의 위치를 나타낼 span 부분
        var total = document.getElementById('total'); // accession의 전체 개수를 나타낼 span 부분
        var display_seq = ''; // 사용자에게 보여줄 시퀀스 염기 서열
        var seq_arr = seq.textContent.split('');
        var seq_result_idx = new Array(); // 사용자가 검색한 특정 시퀀스 값이 있는 배열의 인덱스 시작 지점
        var search_value = seq_search.value.toUpperCase(); // 사용자가 검색한 특정 시퀀스 값, 소문자 입력할 경우를 대비하여 대문자로 반드시 변환
        var search_length = seq_search.value.length; // 사용자가 검색한 특정 시퀀스의 길이

        locate.innerHTML = '';
        str_idx = seq.textContent.indexOf(search_value); // 반복문 i에 검색을 시작할 초기 값을 부여하기 위해 str_idx에 미리 특정 시퀀스 값 최초 발견 지점 저장
        seq_result_idx.push(str_idx); // 최초로 발견된 배열의 인덱스 값을 seq_result_idx 배열에 push하여 저장

        // str_idx부터 시작해서 seq에 불러온 시퀀스의 총 길이만큼 indexOf로 다음 줄에도 특정 시퀀스가 있는지 검사
        for(i=str_idx; i<seq.textContent.length; i++) {
            str_idx = seq.textContent.indexOf(search_value, i); // i인덱스부터 search_value 변수안에 저장된 문자열이 있는지 검사
            if(str_idx == -1) break; // 만약 str_idx가 -1로 초기화 되었다면 더 이상 앞 줄에는 없는 것으로 간주하고 반복문 탈출
            else { // 다음 줄에도 사용자가 검색한 특정 시퀀스 값이 존재한다면 seq_result_idx에 배열로 push하여 저장
                if(seq_result_idx.indexOf(str_idx) == -1) seq_result_idx.push(str_idx);
                else continue;
            }
        }

        // 사용자가 검색한 시퀀스가 있는 인덱스 부분들을 span 태그로 묶어서 사용자에게 보여주는 부분
        for(i=0; i<seq_result_idx.length; i++) {
            var idx = seq_result_idx[i];
            var temp = seq_arr[idx];

            // 사용자가 검색한 시퀀스의 시작 부분에 태그 주입
            // 검색하는 시퀀스의 아미노산 개수가 1개 일 때, 각각 표시해야 하므로 한 개씩 span으로 묶음
            if(search_length == 1) {
                // 사용자가 검색한 시퀀스의 시작 부분 태그와 마지막 부분 종료 태그 주입
                temp = '<span class="seq_highlight">' + temp + '</span>';
                seq_arr[idx] = temp;
            
            // 검색하는 시퀀스의 아미노산 개수가 2개 이상이 일 때 묶어서 표시해야 하므로 검색한 서열 개수만큼 span으로 묶음
            } else {
                temp = '<span class="seq_highlight">' + temp;
                seq_arr[idx] = temp; 

                // 사용자가 검색한 시퀀스의 마지막 부분에 종료 태그 주입
                if((idx + search_length) < seq.textContent.length) {
                    temp = seq_arr[idx + search_length];
                    temp = '</span>' + temp;
                    seq_arr[idx + search_length] = temp;
                }
            }
        }

        // 사용자에게 보여줄 시퀀스에 ,(콤마)를 제거
        display_seq = '';
        display_seq = seq_arr.join('');
        display_seq = display_seq.replace(/,/gi, '');

        if(search_length > 0) seq.innerHTML = display_seq;
        else seq.innerHTML = save_seq;

        // 사용자에게 보여줄 해당 검색 시퀀스의 위치
        for(i=0; i<seq_result_idx.length; i++) {
            // 검색어의 글자수가 0개일 경우 locate와 total 전부 0으로 보여주고 반복문 탈출
            if(search_length == 0) {
                locate.innerHTML = '0';
                break;
            }  

            // i가 seq_result_idx의 사이즈와 동일할 경우 맨 뒤에 ,(콤마)를 찍지 않고 출력
            if(i == (seq_result_idx.length - 1)) locate.innerHTML += '<span class="locate-highlight">' + (seq_result_idx[i] + 1) + '</span>';
            // 아닐 경우 ,(콤마)를 찍어서 출력
            else locate.innerHTML += '<span class="locate-highlight">' + (seq_result_idx[i] + 1) + '</span>, ';
        }

        // seq_result_idx 배열의 length로 총 개수를 불러올 경우 검색한 시퀀스가 없음에도 불구하고 length가 1개를 표시하여 0번째 인덱스에 -1이 있을 경우 0으로 표시되게 수정
        if(seq_result_idx[0] != -1) total.innerHTML = seq_result_idx.length;
        
        if(search_length == 0) total.innerHTML = '0';
    });
    </script>
</body>
</html>