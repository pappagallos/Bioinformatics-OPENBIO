<?php include_once 'Components/Header.php'; ?>
    <link href="Assets/style/openbio_search.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'Components/Menu.php'; ?>
    <?php include_once 'Core/UniProt_API.php'; ?>
    
    <div class="openbio-wrapper">
        <?php
        // 전달받은 파라미터 내용을 변수에 초기화
        $query = $_GET['query'];
        $search_start_point = $_GET['offset'];
        $search_end_point = $_GET['limit'];
        $file_type = $_GET['format'];
        $filter = $_GET['filter'];

        $query = str_replace(" ", "+", $query);

        // Core 함수인 UNIPROT_KB_GetSearchContents()를 호출하여 검색한 키워드에 대한 내용 추출
        $UNIPROT_KB_search_result = UNIPROT_KB_GetSearchContents($query, 'default', $search_start_point, $search_end_point, 'tab', $filter);

        // 필요에 의해 사용될 수 있으므로 Reviewed, Unreviewed URL을 함께 생성하여 검색 개수 초기화
        $UNIPROT_KB_get_contents_counter = UNIPROT_KB_GetSearchCount($UNIPROT_KB_search_result['request_url']);
        $UNIPROT_KB_get_reviewed_counter = UNIPROT_KB_GetSearchCount($UNIPROT_KB_search_result['reviewed_url']);
        $UNIPROT_KB_get_unreviewed_counter = UNIPROT_KB_GetSearchCount($UNIPROT_KB_search_result['unreviewed_url']);
        ?>

        <div class="seacrh-result-box">
            <a href="#" onclick="javascript: leftPageRedirect(<?php echo $search_start_point; ?>, <?php echo $search_end_point; ?>); return false;">
                <div class="left-arrow">
                    <svg version="1.1" class="btn-left" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 129 129" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 129 129">
                        <g>
                            <path d="m88.6,121.3c0.8,0.8 1.8,1.2 2.9,1.2s2.1-0.4 2.9-1.2c1.6-1.6 1.6-4.2 0-5.8l-51-51 51-51c1.6-1.6 1.6-4.2 0-5.8s-4.2-1.6-5.8,0l-54,53.9c-1.6,1.6-1.6,4.2 0,5.8l54,53.9z"/>
                        </g>
                    </svg>
                    <span class="page"><?php echo ($_GET['offset'] + 1); ?></span>
                </div>
            </a>

            <div class="search-keyword">
                <?php echo $_GET['query']; ?>
            </div>

            <a href="#" onclick="javascript: rightPageRedirect(<?php echo $search_start_point; ?>, <?php echo $search_end_point; ?>); return false;">
                <div class="right-arrow">
                    <svg version="1.1" class="btn-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 129 129" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 129 129">
                        <g>
                            <path d="m40.4,121.3c-0.8,0.8-1.8,1.2-2.9,1.2s-2.1-0.4-2.9-1.2c-1.6-1.6-1.6-4.2 0-5.8l51-51-51-51c-1.6-1.6-1.6-4.2 0-5.8 1.6-1.6 4.2-1.6 5.8,0l53.9,53.9c1.6,1.6 1.6,4.2 0,5.8l-53.9,53.9z"/>
                        </g>
                    </svg>
                    <?php
                    /* 최대 보여질 아이템의 개수가 총 검색 개수를 넘어갈 경우 다음 페이지로 넘어가지 못하게 필터링하는 기능 */
                    $maxPage = 0;
                    switch($_GET['filter']) {
                        case 'a' : $maxPage = $UNIPROT_KB_get_contents_counter; break;
                        case 'r' : $maxPage = $UNIPROT_KB_get_reviewed_counter; break;
                        case 'u' : $maxPage = $UNIPROT_KB_get_unreviewed_counter; break;
                    }
                    ?>
                    <span class="page"><?php if(($_GET['offset'] + $_GET['limit']) > $maxPage) echo $maxPage; else echo ($_GET['offset'] + $_GET['limit']); ?></span>
                </div>
            </a>
        </div>
        
        <!-- 상단 컨트롤 기능 -->
        <div class="control-box">
            <span class="reviewed-display">Reviewed <b><?php echo $UNIPROT_KB_get_reviewed_counter; ?></b> and Unreviewed <b><?php echo $UNIPROT_KB_get_unreviewed_counter; ?></b></span>
            <span class="page-display">Search result <b><?php echo ($_GET['offset'] + 1); ?></b> to <b><?php if(($_GET['offset'] + $_GET['limit']) > $maxPage) echo $maxPage; else echo ($_GET['offset'] + $_GET['limit']); ?></b> of <b><?php echo $UNIPROT_KB_get_contents_counter; ?></b></span>
            Show
            <select id="view-counter">
                <option value="25" <?php if($search_end_point == 25) echo 'selected="selected"'; ?>>25</option>
                <option value="50" <?php if($search_end_point == 50) echo 'selected="selected"' ; ?>>50</option>
                <option value="100" <?php if($search_end_point == 100) echo 'selected="selected"'; ?>>100</option>
                <option value="200" <?php if($search_end_point == 200) echo 'selected="selected"'; ?>>200</option>
            </select>
            Filter
            <select id="filter">
                <option value="a" <?php if(!strcmp($filter, 'a')) echo 'selected="selected"'; ?>>All</option>
                <option value="r" <?php if(!strcmp($filter, 'r')) echo 'selected="selected"'; ?>>Reviewed</option>
                <option value="u" <?php if(!strcmp($filter, 'u')) echo 'selected="selected"'; ?>>Unreviewed</option>
            </select>
        </div> 

        <div class="search-table">
            <table>
                <?php
                if($file_type == 'xml') {
                    foreach($UNIPROT_KB_search_result['result'] as $search) {
                        echo '<tr>';
                        echo '    <td>'.$search->accession.'</td>'; 
                        echo '    <td>'.$search->name.'</td>';
                        echo '    <td>'.$search['dataset'].'</td>';
                        echo '    <td>'.$search->protein->recommendedName->fullName.'</td>'; 
                        echo '    <td>'.$search->gene->name.'</td>'; 
                        echo '    <td>'.$search->organism->name.'('.$search->organism->name[1].')</td>'; 
                        echo '    <td>'.$search->sequence['length'].'</td>';
                        echo '</tr>';
                    }

                } else if($file_type == 'tab') {
                    // 최초 1회 실행 확인 변수, false일 경우 실행, true일 경우 else if로 분기
                    $chk = false;

                    // table의 thead를 출력하는 부분으로 최초 1회 실행
                    foreach($UNIPROT_KB_search_result['result'] as $search) {
                        if($chk == false) {
                            echo '<tr class="title">';
                            echo '    <td>'.$search[0].'</td>'; // Entry
                            echo '    <td>'.$search[1].'</td>'; // Entry name
                            echo '    <td>'.$search[2].'</td>'; // Status(Review)
                            echo '    <td class="w50">'.$search[3].'</td>'; // Protein names
                            echo '    <td>'.$search[4].'</td>'; // Gene names
                            echo '    <td>'.$search[5].'</td>'; // Organism
                            echo '    <td>'.$search[6].'</td>'; // Length
                            echo '</tr>';

                            // 최초 1회 실행시키면 true로 초기화
                            $chk = true;

                        } else if($chk == true) {
                            // Gene names의 띄어쓰기는 전부 줄내림으로 바꾸고 모든 영문 대문자로 변환
                            $search[4] = strtoupper(str_replace(' ', '<br>', $search[4]));

                            echo '<tr>';
                            echo '    <td><a href="OpenBioProteinDesc.php?entry='.$search[0].'">'.$search[0].'</a></td>'; // Entry value
                            echo '    <td>'.$search[1].'</td>'; // Entry name  value
                            echo '    <td>'.$search[2].'</td>'; // Status(Review) value
                            echo '    <td class="text-left">'.$search[3].'</td>'; // Protein names value
                            echo '    <td>'.$search[4].'</td>'; // Gene names value
                            echo '    <td>'.$search[5].'</td>'; // Organism value
                            echo '    <td>'.$search[6].'</td>'; // Length value
                            echo '</tr>';
                        }
                    }
                }
                ?>
            </table>
        </div>

        <!-- 하단 컨트롤 기능 -->
        <div class="control-box">
            <span class="reviewed-display">Reviewed <b><?php echo $UNIPROT_KB_get_reviewed_counter; ?></b> and Unreviewed <b><?php echo $UNIPROT_KB_get_unreviewed_counter; ?></b></span>
            <span class="page-display">Search result <b><?php echo ($_GET['offset'] + 1); ?></b> to <b><?php if(($_GET['offset'] + $_GET['limit']) > $maxPage) echo $maxPage; else echo ($_GET['offset'] + $_GET['limit']); ?></b> of <b><?php echo $UNIPROT_KB_get_contents_counter; ?></b></span>
            Show
            <select id="view-counter">
                <option value="25" <?php if($search_end_point == 25) echo 'selected="selected"'; ?>>25</option>
                <option value="50" <?php if($search_end_point == 50) echo 'selected="selected"' ; ?>>50</option>
                <option value="100" <?php if($search_end_point == 100) echo 'selected="selected"'; ?>>100</option>
                <option value="200" <?php if($search_end_point == 200) echo 'selected="selected"'; ?>>200</option>
            </select>
            Filter
            <select id="filter">
                <option value="a" <?php if(!strcmp($filter, 'a')) echo 'selected="selected"'; ?>>All</option>
                <option value="r" <?php if(!strcmp($filter, 'r')) echo 'selected="selected"'; ?>>Reviewed</option>
                <option value="u" <?php if(!strcmp($filter, 'u')) echo 'selected="selected"'; ?>>Unreviewed</option>
            </select>
            <span class="top-btn"><b><a href="#">Top</a></b></span>
        </div> 
    </div>

    <script>
        function leftPageRedirect(_offset, _limit) {
            var redirectURL = modifyParmasAfterGetURL(location.origin + location.pathname + location.search, 'offset', (_offset - _limit));
            if((_offset - _limit) < 0) return;
            window.location.href = redirectURL['getURL'];
        }

        function rightPageRedirect(_offset, _limit) {
            var redirectURL = modifyParmasAfterGetURL(location.origin + location.pathname + location.search, 'offset', (_offset + _limit));
            var maxPage = 0;
            switch('<?php echo $_GET['filter']; ?>') {
                case 'a' : maxPage = <?php echo $UNIPROT_KB_get_contents_counter; ?>; break;
                case 'r' : maxPage = <?php echo $UNIPROT_KB_get_reviewed_counter; ?>; break;
                case 'u' : maxPage = <?php echo $UNIPROT_KB_get_unreviewed_counter; ?>; break;
            }
            if((_offset + _limit) > maxPage) return;
            window.location.href = redirectURL['getURL'];
        }

        // view-counter의 select box에 변경이 감지되었을 경우 event 실행
        var showBtn = document.getElementById('view-counter');
        showBtn.addEventListener('change', function() {
            var showNum = showBtn.options[showBtn.selectedIndex].value;
            var redirectURL = modifyParmasAfterGetURL(location.origin + location.pathname + location.search, 'limit', showNum);
            window.location.href = redirectURL['getURL'];
        });
        
        // filter의 select box에 변경이 감지되었을 경우 event 실행
        var filterBtn = document.getElementById('filter');
        filterBtn.addEventListener('change', function() {
            var filter = filterBtn.options[filterBtn.selectedIndex].value;
            var redirectURL = modifyParmasAfterGetURL(location.origin + location.pathname + location.search, 'filter', filter);
            redirectURL = modifyParmasAfterGetURL(redirectURL['getURL'], 'offset', 0);
            redirectURL = modifyParmasAfterGetURL(redirectURL['getURL'], 'limit', 25);

            // reviewed와 unreviewed를 모두 출력
            if(filter === 'a') {
                window.location.href = redirectURL['getURL'];

            // reviewed만 출력
            } else if(filter === 'r') {
                params = redirectURL['getURL'];
                params.replace('query=', 'query=reviewed:no+AND+');
                window.location.href = params;

            // unreviewed만 출력
            } else if(filter === 'u') {
                params = redirectURL['getURL'];
                params.replace('query=', 'query=reviewed:no+AND+');
                window.location.href = params;
            }
        });

        // 변경하고 싶은 주소와 파라미터의 이름, 변경할 값을 넣은 후 변경해주는 함수
        function modifyParmasAfterGetURL(_targetURL, _paramName, _paramValue) {
            var hostURL = location.origin;
            var pathURL = location.pathname;
            var paramsURL = location.search;
            var makeURL = '';
            
            // 먼저 ?query 부분 중 ?을 분리시켜주어야 하기 때문에 split으로 ?을 분리
            if(_targetURL.indexOf('?')) makeURL = _targetURL.split('?');

            // makeURL의 INDEX 1번에 있는 pathname 중 &을 기준으로 모두 분리
            var path = makeURL[1].split('&');
            var params = new Array;
            var paramsName = new Array;
            var paramsValue = new Array;
            var comParams = new Array;
            var selParamIndex = 0;

            // 배열로 선언한 params 변수에 =을 기준으로 분리하여 path 크기 만큼 배열 뒤에 순서대로 추가
            for(i = 0; i < path.length; i++) params.push(path[i].split('='));

            // 파라미터들의 이름을 저장
            for(i = 0; i< params.length; i++) paramsName.push(params[i][0]);

            // 파라미터들의 값을 저장 
            for(i = 0; i< params.length; i++) paramsValue.push(params[i][1]);

            // 사용자가 설정한 값으로 파라미터 다시 초기화
            selParamIndex = paramsName.indexOf(_paramName);
            paramsValue[selParamIndex] = _paramValue;
            
            // 파라미터들의 키 = 값으로 변경
            for(i = 0; i < paramsName.length; i++) comParams = comParams.concat(paramsName[i], '=', paramsValue[i]);

            // 파라미터 구분자 & 추가
            var index = 3;
            for(i = 0; i < ((comParams.length / 4) - 1); i++) {
                comParams.splice((index), 0, '&');
                index += 4;
            }

            // 파라미터 다시 합친 후 페이지 리다이렉트
            var completeParams = '';
            for(i = 0; i < comParams.length; i++) completeParams += comParams[i];

            // 파라미터 수정 후 다시 조합한 주소 배열의 키, 값 정보로 반환
            return { 'frontURL':makeURL[0], 'getURL':makeURL[0] + '?' + completeParams };
        }
    </script>
</body>
</html>