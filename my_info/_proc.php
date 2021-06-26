<?php
include "_conf.php";
$mode = $_REQUEST['mode'];


if ($mode == 'old_pw_check') {
    $sql = "select * from GLOBAL_MEMBER WHERE PGM_IDX='" . $_SESSION['PGM_IDX'] . "' and LoginPwd='" . md5($val) . "'";
    $res = sql_query($sql, null, $Conn2);
    if (sql_num_rows($res) > 0) {
        echo "OK";
    } else {
        echo "비밀번호가 맞지 않습니다.";
    }

}

if ($mode == "update_pw") {
    $sql = "update GLOBAL_MEMBER set LoginPwd='" . md5($new_pw) . "' where PGM_IDX='" . $_SESSION['PGM_IDX'] . "'";
    if (sql_query($sql, null, $Conn2)) {
        echo "OK";
    } else {
        echo "FALSE";
    }
}

if ($mode == "load_product") {
    $searchSql = array();


    if ($sdate != "" && $edate != "") {
        $searchSql[] = " a.REGDATE >= '$sdate 00:00:00' and a.REGDATE <= '$edate 23:59:59' ";
    }


    if ($flag != "") {
        $searchSql[] = "b.FLAG='$flag' ";
    }else{
        $searchSql[] = " b.FLAG not in ('3') ";
    }
    $searchSql[] = " PGM_IDX='" . $_SESSION['PGM_IDX'] . "' and `GROUP`='member'";

    if ($searchSql) {
        $search_res = " where " . @implode(" and ", $searchSql);
    }

    $num_per_page = 10;
    $page_per_block = 10;

    $sql = "SELECT FULLNAME, b.FLAG, DELI_YN, ADDRESS, ADDRESS_SUB, `COMMENT`, PA_IDX,PI_IDX, PGM_IDX, a.REGDATE, QTY ,P_NAME,MP_IDX,ISCLIP
            FROM `gm_intranet`.ProductApplies AS a 
            LEFT JOIN `gm_intranet`.ProductItems AS b USING(PA_IDX)
            LEFT JOIN global.GLOBAL_MEMBER AS c USING(PGM_IDX) LEFT JOIN global.MASTER_PRODUCT AS d using(PCODE)
            $search_res";
    /*$sql = "SELECT *, b.FLAG AS flag FROM ProductApplies AS a LEFT JOIN ProductItems AS b USING(PA_IDX)
        LEFT JOIN global.GLOBAL_MEMBER AS c USING(PGM_IDX) LEFT JOIN global.MASTER_PRODUCT AS d using(PCODE)
        $search_res $idx_sql";*/
    $res = sql_query($sql, null, $Conn4);

//
//    $msg["sql"] = $sql;
//    echo json_encode($msg);
//    exit;


    $cntPerPage = $rows; // 한페이지 출력개수
    $PAGE_PER_BLOCK = 10;
    $recordCnt = $recordCnt2 = $total_c = sql_num_rows($res);

    $totalpage = ceil($total_c / $cntPerPage);
    if ($totalpage < 1)
        $totalpage = 1;
    $this_start_num = $pageno * $cntPerPage - $cntPerPage;
    $sql .= " ORDER BY PA_IDX desc limit $this_start_num,$cntPerPage ";

    $res = sql_query($sql, null, $Conn4);
    $html = "";
    if (sql_num_rows($res) != 0) {
        while ($data = sql_fetch_assoc($res)) {
            $html .= " <tr class=\"\">";
            $html .= "<td class='text-center'>" . $data['FULLNAME'] . "</td>";
            $html .= "<td class='text-center'>" . $data['QTY'] . "</td>";
            $html .= "<td class='text-center'>" . ($data['ADDRESS'] != "" ? $data['ADDRESS'] . "/" . $data['ADDRESS_SUB'] : "배송지 없음") . "</td>";
            if ($data['FLAG'] == 0) {
                $data['FLAG'] = "결재대기";
            } else if ($data['FLAG'] == 1) {
                $data['FLAG'] = "승인";
            } else if ($data['FLAG'] == 2){
                $data['FLAG'] = "반려";
            }else{
                $data['FLAG'] = "취소";
            }
            $html .= "<td class='text-center'>" . $data['FLAG'] .($data['COMMENT'] == "" ? "" : "<br>(" . $data['COMMENT'] . ")"). "</td>";
            $html .= "<td class='text-center'><button type=\"button\" onclick=\"f_cancel('".$data['PI_IDX']."');\" class=\"btn btn-blank btn-sm\" ".($data['FLAG']!="결재대기"?"disabled":"")." >취소</button>";
            if($data['ISCLIP']!=1){
                //클립이있는것만
                $clip_sql = "SELECT * FROM global.MASTER_PRODUCT AS a
                                                WHERE K_PRICE IN ('60000','40000') AND a.ASS_NAME='세트품' AND a.P_NAME='".$data['P_NAME']."'  ORDER BY a.MP_IDX DESC ";
                $clip_res = sql_query($clip_sql, null, $Conn5);
                if(sql_num_rows($clip_res)!=0) {
                    //클립신청안한것만
                    $ccsql = "select * from `gm_intranet`.ProductItems where REF_PI_IDX='".$data['PI_IDX']."' and FLAG not IN ('2','3') ";
                    $ccres =sql_query($ccsql, null, $Conn4);
                    if(sql_num_rows($ccres)==0){
                        $html .= "<button type=\"button\" onclick=\"clip_list('".$data['MP_IDX']."','".$data['PI_IDX']."');\" class=\"btn btn-blank btn-sm\" ".($data['FLAG']!="승인"?"disabled":"")." >클립 재신청</button>";
                    }
                }
            }

            $html .="</td>";

        }
        $msg["rows"] = $html;
    } else {
        $html .= "<tr><td colspan='5'>No data available</td></tr>";
        $msg["rows"] = $html;
    }
    //--------------------------------------페이징---------------------------
    $p_pageNo = $pageno;
    $p_pageLength = $cntPerPage;
    $p_recordCnt = $recordCnt;

    $startPageNo = (ceil($p_pageNo / $PAGE_PER_BLOCK) - 1) * $PAGE_PER_BLOCK + 1;
    $PrevPageNo = $p_pageNo - 1;
    $NextPageNo = $p_pageNo + 1;
    $PrevBlockNo = $startPageNo - $PAGE_PER_BLOCK;
    $NextBlockNo = $startPageNo + $PAGE_PER_BLOCK;

    if ($p_recordCnt > 0)
        $TotalPage = ceil($p_recordCnt / $p_pageLength);
    else
        $TotalPage = 0;

    $BlockCount = @ceil($TotalPage / $PAGE_PER_BLOCK);
    $lastBlockStartNum = $BlockCount * $PAGE_PER_BLOCK - $PAGE_PER_BLOCK + 1;

    $pageHtml = "<ul class=\"pagination pagination-sm\">";

    if ($PAGE_PER_BLOCK < $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $PrevBlockNo . "')\">&laquo;</li>";
    }

    for ($i = $startPageNo; $i < ($startPageNo + $PAGE_PER_BLOCK) && $i <= $TotalPage; $i++) {
        if ($i == $p_pageNo)
            $pageHtml .= "<li class='disable active'>" . $i . "</li>";
        else
            $pageHtml .= "<li onclick='paging(\"" . $i . "\")'>" . $i . "</li>";
    }
    if ($lastBlockStartNum > $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $NextBlockNo . "')\">&raquo;</li>";
    }

    $pageHtml .= "</ul>";
    $msg["paging"] = $pageHtml;


    echo json_encode($msg);

}

if($mode=='load_clip'){
    $data_rows = array();

    if($keyword!=""){
        $keyword = strtoupper($keyword);
        $keywordsql = "AND (a.FULLNAME LIKE '%$keyword%' OR a.PCODE LIKE '%$keyword%') ";
    }
    $pnamesql = "SELECT * FROM global.MASTER_PRODUCT where MP_IDX='".$idx."' ";
    $pnamedata = sql_fetch($pnamesql, null, $Conn5);

    $product_sql = "SELECT * FROM global.MASTER_PRODUCT AS a
                                                WHERE K_PRICE IN ('60000','40000') AND a.ASS_NAME='세트품' AND a.P_NAME='".$pnamedata['P_NAME']."'   $keywordsql ORDER BY a.MP_IDX DESC ";
    $product_res = sql_query($product_sql, null, $Conn5);


    if(sql_num_rows($product_res)!=0) {
        while ($r = sql_fetch_assoc($product_res)) {
            array_push($data_rows, $r);
        }
        $msg["rows"] = $data_rows;
        $msg["msg"]="OK";
    }else{
        $msg["msg"]="NO DATA";
    }

    echo json_encode($msg);
}

if($mode=="check_address"){
    $stack_adr = 0;
    $no_adr = array(
        '서울 마포구 어울마당로5길 41',
        '서울 마포구 독막로7길 40',
        '서울 마포구 독막로7길 23',
        '서울 마포구 어울마당로5길 17',
        '서울 마포구 독막로8길 49'
    );
    $result = array_search($adr, $no_adr);
    if($result>-1){
        $stack_adr++;
    } else {
    }
    if($stack_adr){
        echo "no";
    }else{
        echo "ok";
    }
}

if($mode=="insert_clip"){
    $success = 0;

    $sql = "insert ProductApplies set
    PGM_IDX='".$pgm_idx."'
    ,NOTE='".$note."'
    ,DELI_YN='1'
    ,ZIPCODE='".$zipcode."'
    ,ADDRESS='".$address."'
    ,ADDRESS_SUB='".$addressSub."'
    ,DELI_NAME='".$deliName."'
    ,PHONE='".$phone."'
    ,MODDATE=NOW()
    ,REGDATE=now()
    ";
    if(sql_query($sql,null,$Conn4)){
        $pa_idx =sql_insert_id($Conn4);

        $product_sql = "SELECT * FROM global.MASTER_PRODUCT where MP_IDX='".$clip_idx."'";
        $product_data = sql_fetch($product_sql,null,$Conn4);
        if($product_data['PCODE']!=''){
            $itemsql =  "insert ProductItems set
                PA_IDX='".$pa_idx."'
                ,PCODE='".$product_data['PCODE']."'
                ,ISCLIP='1'
                ,ISEXCEPT='0'
                ,REF_PI_IDX='".$pi_idx."'
                ,QTY='1'
                ,MODDATE=NOW()
                ,REGDATE=NOW()
                ";
            if(sql_query($itemsql,null,$Conn4)){
                $success++;
            }else{
            }
        }
    }else{

    }
    echo $success."건 신청성공하였습니다.";
}



if ($mode == "load_tamburins_product") {
    $searchSql = array();


    if ($sdate != "" && $edate != "") {
        $searchSql[] = " REGDATE >= '$sdate 00:00:00' and REGDATE <= '$edate 23:59:59' ";
    }


    if ($payment_cmpl != "") {
//        $payment_cmpl = $payment_cmpl;
        $searchSql[] = " PAYMENT_CMPL ='".$payment_cmpl."'";
    }else{
        $searchSql[] = " PAYMENT_CMPL IN (0,1,2,3,5,8,9) ";
    }
    $searchSql[] = " PGM_IDX='" . $_SESSION['PGM_IDX'] . "' and `GROUP`='member'";

    $search_res = " where " . @implode(" and ", $searchSql);

    $num_per_page = 10;
    $page_per_block = 10;

    $sql = "SELECT PA_IDX, ADDRESS, ADDRESS_SUB, PAYMENT_CMPL, REGDATE FROM `gm_intranet`.ProductApplies_TB ".$search_res." ORDER BY PA_IDX DESC ";
    $res = sql_query($sql, null, $Conn4);

    $cntPerPage = $rows; // 한페이지 출력개수
    $PAGE_PER_BLOCK = 10;
    $recordCnt = $recordCnt2 = $total_c = sql_num_rows($res);

    $totalpage = ceil($total_c / $cntPerPage);
    if ($totalpage < 1)
        $totalpage = 1;
    $this_start_num = $pageno * $cntPerPage - $cntPerPage;
    $sql .= " limit $this_start_num,$cntPerPage ";
    $res = sql_query($sql, null, $Conn4);
    $html = "";
    if (sql_num_rows($res) != 0) {
        while ($data = sql_fetch_assoc($res)) {
            $pcode_arr = array();
            $pqty_arr = array();
            $pcmt_arr = array();
            $i=0;

            $html .= "<tr class=\"\" style='height:130px;'>";
            $html .= "<td class='text-center'>" . $data['PA_IDX'] . "</td>";

            $product_sql="SELECT PCODE, QTY, COMMENT FROM `gm_intranet`.ProductItems_TB WHERE PA_IDX='".$data['PA_IDX']."' ORDER BY PCODE DESC";
            $product_res=sql_query($product_sql,null,$Conn4);
            while($pdata=sql_fetch_assoc($product_res)){
                $pcode_arr[] = "'".$pdata["PCODE"]."'";
                $pqty_arr[] = $pdata["QTY"];
                $pcmt_arr[] = $pdata["COMMENT"];
            }
            $pcode_str=implode(',', $pcode_arr);

            $name_sql="SELECT PRODUCT_DESCRIPTION_SHORT FROM `Master_Data`.Master_Product_copy WHERE P_CODE IN ($pcode_str) ORDER BY P_CODE DESC";
            $name_res=sql_query($name_sql,null,$Conn7);

            $html .= "<td class='text-center'>";
            while($ndata=sql_fetch_assoc($name_res)){
                $html .= $ndata["PRODUCT_DESCRIPTION_SHORT"];
                if($pcmt_arr[$i]!="" || $pcmt_arr[$i]!=null){
                    $html .= "<span class='notice'>(".$pcmt_arr[$i].")</span>";
                }
                $html .= "<br/>";
                $i++;
            }
            $html .= "</td>";

            $html .= "<td class='text-center'>";
            foreach($pqty_arr as $value){
                $html .= $value."<br/>";
            }
            $html .= "</td>";

            $html .= "<td class='text-center'>" . $data['ADDRESS']." ".$data['ADDRESS_SUB'] . "</td>";
            if($data["PAYMENT_CMPL"]==0) {
                $data["PAYMENT_CMPL"]="구매신청";
            }else if($data["PAYMENT_CMPL"]==1){
                $data["PAYMENT_CMPL"]="구매승인";
            }else if($data["PAYMENT_CMPL"]==2){
                $data["PAYMENT_CMPL"]="결제완료";
            }else if($data["PAYMENT_CMPL"]==3){
                $data["PAYMENT_CMPL"]="결제완료(배송준비)";
            }else if($data["PAYMENT_CMPL"]==4){
                $data["PAYMENT_CMPL"]="구매취소";
            }else if($data["PAYMENT_CMPL"]==5){
                $data["PAYMENT_CMPL"]="배송처리(완료)";
            }else if($data["PAYMENT_CMPL"]==8){
                $data["PAYMENT_CMPL"]="구매반려";
            }else{
                $data["PAYMENT_CMPL"]="구매기간 만료";
            }
            $html .= "<td class='text-center'>" . $data['PAYMENT_CMPL'] . "</td>";


            unset($pcode_arr);
            unset($pqty_arr);
            unset($pcmt_arr);
            unset($pcode_str);
        }
        $msg["rows"] = $html;
    } else {
        $html .= "<tr><td colspan='5'>No data available</td></tr>";
        $msg["rows"] = $html;
    }
    //--------------------------------------페이징---------------------------
    $p_pageNo = $pageno;
    $p_pageLength = $cntPerPage;
    $p_recordCnt = $recordCnt;

    $startPageNo = (ceil($p_pageNo / $PAGE_PER_BLOCK) - 1) * $PAGE_PER_BLOCK + 1;
    $PrevPageNo = $p_pageNo - 1;
    $NextPageNo = $p_pageNo + 1;
    $PrevBlockNo = $startPageNo - $PAGE_PER_BLOCK;
    $NextBlockNo = $startPageNo + $PAGE_PER_BLOCK;

    if ($p_recordCnt > 0)
        $TotalPage = ceil($p_recordCnt / $p_pageLength);
    else
        $TotalPage = 0;

    $BlockCount = @ceil($TotalPage / $PAGE_PER_BLOCK);
    $lastBlockStartNum = $BlockCount * $PAGE_PER_BLOCK - $PAGE_PER_BLOCK + 1;

    $pageHtml = "<ul class=\"pagination pagination-sm\">";

    if ($PAGE_PER_BLOCK < $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $PrevBlockNo . "')\">&laquo;</li>";
    }

    for ($i = $startPageNo; $i < ($startPageNo + $PAGE_PER_BLOCK) && $i <= $TotalPage; $i++) {
        if ($i == $p_pageNo)
            $pageHtml .= "<li class='disable active'>" . $i . "</li>";
        else
            $pageHtml .= "<li onclick='paging(\"" . $i . "\")'>" . $i . "</li>";
    }
    if ($lastBlockStartNum > $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $NextBlockNo . "')\">&raquo;</li>";
    }

    $pageHtml .= "</ul>";
    $msg["paging"] = $pageHtml;


    echo json_encode($msg);

}
if($mode=="load_vacation"){
    $searchSql = array();

    $cntPerPage = $rows; // 한페이지 출력개수
    $PAGE_PER_BLOCK = 10;


    if ($year!="") {
        $searchSql[] = "((DATE_FORMAT(BEG_DATE,'%Y') = '$year' and DATE_FORMAT(END_DATE,'%Y') = '$year') or  (DATE_FORMAT(BEG_DATE,'%Y') = '$year' and END_DATE is null ))";
    }
    if ($keyword != "") {
        $searchSql[] = " $column = '$keyword'";
    }

    $searchSql[] ="FLAG not in ('3')"; //회수아닌것만
    $searchSql[] = " PGM_IDX='" . $_SESSION['PGM_IDX'] . "' ";

    if ($searchSql) {
        $search_res = " where " . @implode(" and ", $searchSql);
    }

    $num_per_page = 10;
    $page_per_block = 10;


    $sql = "SELECT a.*,a.VA_IDX as va_idx,a.NOTE as note, DEPARTMENT, GROUP_NAME, c.UserName, c.UserId FROM VacationApplies AS a
        LEFT JOIN global.GLOBAL_MEMBER AS c USING(PGM_IDX)
        $search_res ";
    $res = sql_query($sql, null, $Conn4);

    $recordCnt = $recordCnt2 = $total_c = sql_num_rows($res);

    $totalpage = ceil($total_c / $cntPerPage);
    if ($totalpage < 1)
        $totalpage = 1;
    $this_start_num = $pageno * $cntPerPage - $cntPerPage;
    $sql .= " ORDER BY a.VA_IDX desc limit $this_start_num,$cntPerPage ";
    $msg["sql"] = $sql;

    $res = sql_query($sql, null, $Conn4);
    $html = "";

    if (sql_num_rows($res) != 0) {
        while ($data = sql_fetch_assoc($res)) {
            $html .= " <tr class=\"\">";
            $html .= "<td class='text-center'>" . $data['va_idx'] . "</td>";
            if($data['gwDocumentId']!=""){
                $html .= "<td style='text-align:center;'><a href='#' onclick=\"f_viewApp('".$data['gwApprovalId']."')\">".$data['gwDocumentId']."</a></td>";
            }else{
                $html.="<td style='text-align:center;'>(없음)</td>";
            }
            if($data['VA_TYPE']=='1'){
                $va_type_name="연차휴가";
            }elseif ($data['VA_TYPE']=='2'){
                $va_type_name="안식휴가";
            }elseif ($data['VA_TYPE']=='3'){
                $va_type_name="경조휴가";
            }elseif ($data['VA_TYPE']=='4'){
                $va_type_name="병가";
            }elseif ($data['VA_TYPE']=='5'){
                $va_type_name="육아휴직";
            }elseif ($data['VA_TYPE']=='6'){
                $va_type_name="출산휴가";
            }elseif ($data['VA_TYPE']=='7'){
                $va_type_name="기타휴가";
            }elseif ($data['VA_TYPE']=='8'){
                $va_type_name="리프레쉬 쿠폰";
            }elseif ($data['VA_TYPE']=='9'){
                $va_type_name="보건휴가";
            }elseif ($data['VA_TYPE']=='10'){
                $va_type_name="대체휴가";
            }elseif ($data['VA_TYPE']=='11'){
                $va_type_name="무급휴가";
            }elseif ($data['VA_TYPE']=='12'){
                $va_type_name="난임휴가";
            }elseif ($data['VA_TYPE']=='13'){
                $va_type_name="가족돌봄휴가";
            }elseif ($data['VA_TYPE']=='14'){
                $va_type_name="단축근무";
            }else if ($data['VA_TYPE']=='15'){
                $va_type_name="연동테스트";
            }
            
            if($data['QTY']<0){
                $va_type_name.="<small class='sub-notice'>(".$data['RE_VA_IDX']."번 반환신청)</small>";
            }
            
            $html.="<td style='text-align:center;' class='va_type_".$data['va_idx']."'>".$va_type_name."</td>";

            $begdate=date("Y.m.d",strtotime($data['BEG_DATE']));
            if($data['END_DATE']!=""){
                $enddate=date("Y.m.d",strtotime($data['END_DATE']));
                $period=$begdate."~".$enddate;
            }else{
                $period=$begdate;
            }

            $html .= "<td class='text-center period_".$data['va_idx']."'>" . $period . "</td>";
            $html .= "<td class='text-center qty_".$data['va_idx']."'>" . $data['QTY'] . "</td>";

            if($data['FLAG']==0){$status ="상신 대기";}else if($data['FLAG']==1){$status ="상신";}else if($data['FLAG']==2){$status ="회신";}else if($data['FLAG']==5){$status ="결재완료";}else{$status ="반려";}
            $html .= "<td class='text-center status_".$data['va_idx']."'>" . $status . "</td>";
            $html .= "<td class='text-center note_".$data['va_idx']."'>" . $data['note'] . "</td>";
            $html .= "<td class='text-center'><button type='button' class='btn btn-blank btn-sm'
                                    onclick='apply_va(".$data['va_idx'].")'".($data['FLAG']!=0?"disabled":"")." >결재상신</button>";
            $diffsql="SELECT * FROM VacationApplies WHERE RE_VA_IDX = '".$data['VA_IDX']."'";
            $diffres = sql_query($diffsql, null, $Conn4);
            if(sql_num_rows($diffres)==0&&$data['QTY']>0){
                $html .= "<button type='button' class='btn btn-default btn-sm return_vbtn_".$data['va_idx']."' onclick='return_apply(\"".$data['va_idx']."\");' style='display:".($data['FLAG']=='5'?"inline-block":"none").";'>반환 신청</button>";
            }
            $html .= "<button type='button' class='btn btn-blank btn-sm'
                                    onclick=\"del_va('".$data['va_idx']."');\" style='display:".($data['FLAG']!=0?"none":"inline-block").";' >취소</button></td></tr>";
        }
        $msg["rows"] = $html;
    } else {
        $html .= "<tr><td colspan='8'>No data available</td></tr>";
        $msg["rows"] = $html;
    }
    //--------------------------------------페이징---------------------------
    $p_pageNo = $pageno;
    $p_pageLength = $cntPerPage;
    $p_recordCnt = $recordCnt;

    $startPageNo = (ceil($p_pageNo / $PAGE_PER_BLOCK) - 1) * $PAGE_PER_BLOCK + 1;
    $PrevPageNo = $p_pageNo - 1;
    $NextPageNo = $p_pageNo + 1;
    $PrevBlockNo = $startPageNo - $PAGE_PER_BLOCK;
    $NextBlockNo = $startPageNo + $PAGE_PER_BLOCK;

    if ($p_recordCnt > 0)
        $TotalPage = ceil($p_recordCnt / $p_pageLength);
    else
        $TotalPage = 0;

    $BlockCount = @ceil($TotalPage / $PAGE_PER_BLOCK);
    $lastBlockStartNum = $BlockCount * $PAGE_PER_BLOCK - $PAGE_PER_BLOCK + 1;

    $pageHtml = "<ul class=\"pagination pagination-sm\">";

    if ($PAGE_PER_BLOCK < $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $PrevBlockNo . "')\">&laquo;</li>";
    }

    for ($i = $startPageNo; $i < ($startPageNo + $PAGE_PER_BLOCK) && $i <= $TotalPage; $i++) {
        if ($i == $p_pageNo)
            $pageHtml .= "<li class='disable active'>" . $i . "</li>";
        else
            $pageHtml .= "<li onclick='paging(\"" . $i . "\")'>" . $i . "</li>";
    }
    if ($lastBlockStartNum > $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $NextBlockNo . "')\">&raquo;</li>";
    }
    $msg["sql"] = $sql;
    $pageHtml .= "</ul>";
    $msg["paging"] = $pageHtml;

    echo json_encode($msg);
}

if($mode=="load_airticket"){

    $searchSql = array();

    if ($period=="") {
        $period = "REGDATE";
    }
    $searchSql[] = " $period >= '$sdate 00:00:00' and $period < '$edate 23:59:59' ";
    if ($keyword != "") {
        $searchSql[] = $column . " like '%" . $keyword . "%' ";
    }
    $searchSql[] = " PGM_IDX='" . $_SESSION['PGM_IDX'] . "' ";
    if ($searchSql) {
        $search_res = " where " . @implode(" and ", $searchSql);
    }
    $num_per_page = 10;
    $page_per_block = 10;

    $sql = " SELECT *
    FROM AirticketApplies left JOIN global.GLOBAL_MEMBER USING(PGM_IDX)
    $search_res ";
    $res = sql_query($sql, null, $Conn4);



    $cntPerPage = $rows; // 한페이지 출력개수
    $PAGE_PER_BLOCK = 10;
    $recordCnt = $recordCnt2 = $total_c = sql_num_rows($res);

    $totalpage = ceil($total_c / $cntPerPage);
    if ($totalpage < 1)
        $totalpage = 1;
    $this_start_num = $pageno * $cntPerPage - $cntPerPage;
    $sql .= " ORDER BY AA_IDX desc limit $this_start_num,$cntPerPage ";


    $res = sql_query($sql, null, $Conn4);
    $html ="";
    $old_html = "";
    if (sql_num_rows($res) != 0) {
        while ($data = sql_fetch_assoc($res)) {
            //array_push($data_rows, $r);
            $old_aa_idx = $data['AA_IDX'];
            if($data['USEVALUE'] < 0){
                $old_aa_idx.="<small class='sub-notice'>(".$data['RETURN_ID']."번 반환신청)</small>";
            }

            $html.=" <tr class=\"\">";
            $html.="<td class='text-center aa_idx_".$data['AA_IDX']."'>".$old_aa_idx."</td>";
            $html.="<td class='text-center regdate_".$data['AA_IDX']."'>".date("Y-m-d", strtotime($data['REGDATE'])) ."</td>";
            $html.="<td class='text-center ticket_date_".$data['AA_IDX']."'>". date("Y-m-d", strtotime($data['TICKET_DATE'])) ."</td>";
            $html.="<td class='text-center price_".$data['AA_IDX']."'>". ($data['RETURN_ID'] != "" ? number_format($data['PRICE']) : "") ."</td>";

            $status_msg="";
            if ($data['FLAG'] == 2) {
                $status_msg.= "반려";
            } else if ($data['FLAG'] == 1) {
                $status_msg.= "승인";
                $status_msg.= "(";
                if ($data['RETURN_ID'] > 0) {
                    if ($data['PAYMENT_DATE'] != "") {
                        $status_msg.= "반환 완료";
                    } else {
                        $status_msg.= "반환 대기";
                    }
                    $status_msg.=")";
                } else {
                    if ($data['PAYMENT_DATE'] != "") {
                        $status_msg.="지급 완료";
                    } else {
                        $status_msg.="지급 대기";
                    }
                    $status_msg.=")";
                }
            }else if ($data['FLAG'] == 0) {
                $status_msg.="결재 대기";
            }else{
                $status_msg.="취소";
            }

            $html.="<td class='text-center status_msg_".$data['AA_IDX']."'>".$status_msg."</td>";
            $html.="<td class='text-center payment_date_".$data['AA_IDX']."'>".($data['PAYMENT_DATE'] != "" ? date("Y-m-d", strtotime($data['PAYMENT_DATE'])) : "")."</td>";
            $html.="<td class='text-center actual_price_".$data['AA_IDX']."'>".number_format($data['ACTUAL_PRICE'])."</td>";
            $html.="<td class='text-center'><button type=\"button\" class=\"btn btn-blank btn-sm\"
                                    onclick=\"f_cancel(".$data['AA_IDX'].");\"".($data['FLAG']!=0?"disabled":"")." >취소</button>";
            if($data['FLAG'] == 1&&$data['RETURN_ID'] != ""&&$data['USEVALUE']>0){
                $csql = "SELECT * FROM AirticketApplies WHERE RETURN_ID = '".$data['AA_IDX']."'";
                $cres = sql_query($csql, null, $Conn4);
                $checkbtn = sql_num_rows($cres);
                $html.="<button type='button' class='btn btn-default btn-sm return_btn_".$data['AA_IDX']."' onclick='return_airticket(".$data['AA_IDX'].");' style='display:".($checkbtn != 0?"none":"inline-block").";'>반환 신청</button>";
            }
            $html.="</td></tr>";
        }
        $msg["rows"] = $html;
    } else {
        $html.="<tr><td colspan='8'>No data available</td></tr>";
        $msg["rows"] = $html;
    }
    //--------------------------------------페이징---------------------------
    $p_pageNo = $pageno;
    $p_pageLength = $cntPerPage;
    $p_recordCnt = $recordCnt;

    $startPageNo = (ceil($p_pageNo / $PAGE_PER_BLOCK) - 1) * $PAGE_PER_BLOCK + 1;
    $PrevPageNo = $p_pageNo - 1;
    $NextPageNo = $p_pageNo + 1;
    $PrevBlockNo = $startPageNo - $PAGE_PER_BLOCK;
    $NextBlockNo = $startPageNo + $PAGE_PER_BLOCK;

    if ($p_recordCnt > 0)
        $TotalPage = ceil($p_recordCnt / $p_pageLength);
    else
        $TotalPage = 0;

    $BlockCount = @ceil($TotalPage / $PAGE_PER_BLOCK);
    $lastBlockStartNum = $BlockCount * $PAGE_PER_BLOCK - $PAGE_PER_BLOCK + 1;

    $pageHtml = "<ul class=\"pagination pagination-sm\">";

    if ($PAGE_PER_BLOCK < $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $PrevBlockNo . "')\">&laquo;</li>";
    }

    for ($i = $startPageNo; $i < ($startPageNo + $PAGE_PER_BLOCK) && $i <= $TotalPage; $i++) {
        if ($i == $p_pageNo)
            $pageHtml .= "<li class='disable active'>" . $i . "</li>";
        else
            $pageHtml .= "<li onclick='paging(\"" . $i . "\")'>" . $i . "</li>";
    }
    if ($lastBlockStartNum > $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $NextBlockNo . "')\">&raquo;</li>";
    }

    $pageHtml .= "</ul>";
    $msg["paging"] = $pageHtml;


    echo json_encode($msg);
}
if($mode=="load_house"){
    $searchSql = array();

    if ($year!="") {
        $searchSql[] = " (DATE_FORMAT(DATE_IN,'%Y')) = '$year' ";
    }
    $searchSql[] = " PGM_IDX='" . $_SESSION['PGM_IDX'] . "' ";
    if ($searchSql) {
        $search_res = " where " . @implode(" and ", $searchSql);
    }
    $num_per_page = 10;
    $page_per_block = 10;

    $sql = "select * from SNOOP_HOUSE $search_res ";
    $res = sql_query($sql,null,$Conn4);



    $cntPerPage = $rows; // 한페이지 출력개수
    $PAGE_PER_BLOCK = 10;
    $recordCnt = $recordCnt2 = $total_c = sql_num_rows($res);

    $totalpage = ceil($total_c / $cntPerPage);
    if ($totalpage < 1)
        $totalpage = 1;
    $this_start_num = $pageno * $cntPerPage - $cntPerPage;
    $sql .= " ORDER BY SH_IDX desc limit $this_start_num,$cntPerPage ";
    $res = sql_query($sql, null, $Conn4);
    $html ="";
    if (sql_num_rows($res) != 0) {
        while ($data = sql_fetch_assoc($res)) {
            //array_push($data_rows, $r);
            $html.=" <tr class=\"\">";
            $html.="<td class='text-center'>".date("Y-m-d",strtotime($data['REGDATE']))."</td>";
            $html.="<td class='text-center'>".$data['DATE_IN']."</td>";
            $html.="<td class='text-center'>". $data['PEOPLE']."</td>";
            $html.="<td class='text-center'>". $data['TYPE']."</td>";
            $html.="<td class='text-center'>". $data['MSG']."</td>";
            if($data['FLAG_STATE']=='1'){
                $flag_state="예약신청";
            }elseif ($data['FLAG_STATE']=='2'){
                $flag_state="승인";
            }else{
                $flag_state="예약취소";
            }
            $html.="<td class='text-center'>". $flag_state."</td>";
            $html.="<td class='text-center'><button type=\"button\" class=\"btn btn-default btn-sm\"
                                    onclick=\"f_cancel(".$data['SH_IDX'].");\"".($data['FLAG_STATE']!=1?"disabled":"")." >신청취소</button></td></tr>";
        }
        $msg["rows"] = $html;
    } else {
        $html.="<tr><td colspan='7' class='text-center'>No data available</td></tr>";
        $msg["rows"] = $html;
    }
    //--------------------------------------페이징---------------------------
    $p_pageNo = $pageno;
    $p_pageLength = $cntPerPage;
    $p_recordCnt = $recordCnt;

    $startPageNo = (ceil($p_pageNo / $PAGE_PER_BLOCK) - 1) * $PAGE_PER_BLOCK + 1;
    $PrevPageNo = $p_pageNo - 1;
    $NextPageNo = $p_pageNo + 1;
    $PrevBlockNo = $startPageNo - $PAGE_PER_BLOCK;
    $NextBlockNo = $startPageNo + $PAGE_PER_BLOCK;

    if ($p_recordCnt > 0)
        $TotalPage = ceil($p_recordCnt / $p_pageLength);
    else
        $TotalPage = 0;

    $BlockCount = @ceil($TotalPage / $PAGE_PER_BLOCK);
    $lastBlockStartNum = $BlockCount * $PAGE_PER_BLOCK - $PAGE_PER_BLOCK + 1;

    $pageHtml = "<ul class=\"pagination pagination-sm\">";

    if ($PAGE_PER_BLOCK < $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $PrevBlockNo . "')\">&laquo;</li>";
    }

    for ($i = $startPageNo; $i < ($startPageNo + $PAGE_PER_BLOCK) && $i <= $TotalPage; $i++) {
        if ($i == $p_pageNo)
            $pageHtml .= "<li class='disable active'>" . $i . "</li>";
        else
            $pageHtml .= "<li onclick='paging(\"" . $i . "\")'>" . $i . "</li>";
    }
    if ($lastBlockStartNum > $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $NextBlockNo . "')\">&raquo;</li>";
    }

    $pageHtml .= "</ul>";
    $msg["paging"] = $pageHtml;


    echo json_encode($msg);
}

if($mode=="cancel_product"){
    $check=0;
//    $sql = "update ProductItems set FLAG='3' where PI_IDX='" . $idx . "'  ";
    $sql = "UPDATE `gm_intranet`.ProductItems SET FLAG=3 WHERE PI_IDX='".$idx."'";
    if(sql_query($sql,null,$Conn4)){
    }else{
        $check++;
    }
    if($check){
        echo "취소신청 처리 중 오류가 발생했습니다. IT기획파트에 문의해주세요.";
    }else{
        echo "취소 처리 되었습니다.";
    }
}
if($mode=="cancel_house"){
    $sql = "update group_wear.SNOOP_HOUSE set FLAG_STATE='3' where SH_IDX='" . $idx . "' ";
    if(sql_query($sql,null,$Conn2)){
        echo "취소 처리 되었습니다.";
    }
}
if($mode=="cancel_airticket"){
    $sql = "update AirticketApplies set FLAG ='3' where AA_IDX='" . $idx . "' ";
    if(sql_query($sql, null, $Conn4)){
        echo "취소 처리 되었습니다.";
    }
}

if($mode=="cancel_vacation"){
    $sql = "delete from VacationApplies where VA_IDX='" . $idx . "' ";
    if(sql_query($sql, null, $Conn4)){
        echo "취소 처리 되었습니다.";
    }
}
if($mode=="return_vacation"){
    $ssql = "select * from VacationApplies where VA_IDX='".$old_idx."'";
    $sres = sql_query($ssql, null, $Conn4);
    
    if (sql_num_rows($sres) != 0) {

        $data = sql_fetch_assoc($sres);
        $sql = "insert VacationApplies set 
            uuid=''
            ,PGM_IDX='".$data['PGM_IDX']."'
            ,VA_TYPE='".$data['VA_TYPE']."'
            ,BEG_DATE='".$data['BEG_DATE']."'
            ,END_DATE='".$data['END_DATE']."'
            ,QTY='-".$data['QTY']."'
            ,FLAG='0'
            ,IS_AFTER='0'
            ,RE_VA_IDX='".$old_idx."'
            ,NOTE='".$note."'
            ,REGDATE=NOW()
            ,MODDATE=NOW()
        ";
        
        sql_query($sql, null, $Conn4);
        
        $idx = sql_insert_id($Conn4);
        
        $tsql = "SELECT * FROM VacationTypes WHERE _id = '".$data['VA_TYPE']."'";
        $tres = sql_query($tsql, null, $Conn4);
        $tdata = sql_fetch_assoc($tres);
        $work_id = $tdata['gwWorkId'];
        $absent_type = $tdata['name'];
        
        $msg["WORK_ID"] = $work_id;
        $msg["REQ_KEY"] = $idx;
        $msg["ABSENT_TYPE"] = $absent_type;
        $msg["ABSENT_SRT"] = date("Y-m-d", strtotime($data['BEG_DATE']));
        $msg["ABSENT_END"] = date("Y-m-d", strtotime($data['END_DATE']));
        $msg["ABSENT_DT"] = $data['QTY']*-1;
        $msg["ABSENT_DESC"] = $absent_type . " 반환신청";
        $msg["msg"] = "OK";
    }else {
        $msg["msg"] = "FALSE";
    }
    echo json_encode($msg);
}

if($mode=="return_airticket"){
    $ssql = "SELECT * FROM AirticketApplies WHERE AA_IDX = ".$old_idx;
    $sres = sql_query($ssql, null, $Conn4);

    if (sql_num_rows($sres) != 0) {

        $data = sql_fetch_assoc($sres);
        $sql = "insert AirticketApplies set
            userId='0'
            ,PGM_IDX='".$_SESSION['PGM_IDX']."'
            ,PRICE='".$data['PRICE']."'
            ,ACTUAL_PRICE= '".$data['ACTUAL_PRICE']."'
            ,TICKET_DATE= '".$data['TICKET_DATE']."'
            ,USEVALUE= -'".$data['USEVALUE']."'
            ,FLAG= 0
            ,RETURN_ID= '".$old_idx."'
            ,NOTE='".$note."'
            ,REGDATE=NOW(),";
        if($data['PAYMENT_DATE'] != null){
            $sql .= " PAYMENT_DATE= '".$data['PAYMENT_DATE']."'";
        }else {
            $sql .= " PAYMENT_DATE= null";
        }

        $res = sql_query($sql, null, $Conn4);

        if($res){
            $msg["msg"] = "OK";
        } else {
            $msg["msg"] = "ERROR";
        }
    }else {
        $msg["msg"] = "FAIL";
    }
    echo json_encode($msg);
}

if($mode=="apply_vacation"){
    $ssql = "select * from VacationApplies where VA_IDX='".$idx."'";
    $sres = sql_query($ssql, null, $Conn4);
    if (sql_num_rows($sres) != 0) {
        
        $data = sql_fetch_assoc($sres);
        
        $tsql = "SELECT * FROM VacationTypes WHERE _id = '".$data['VA_TYPE']."'";
        $tres = sql_query($tsql, null, $Conn4);
        $tdata = sql_fetch_assoc($tres);
        $work_id = $tdata['gwWorkId'];
        $absent_type = $tdata['name'];
        
        $msg["WORK_ID"] = $work_id;
        $msg["REQ_KEY"] = $idx;
        $msg["ABSENT_TYPE"] = $absent_type;
        $msg["ABSENT_SRT"] = date("Y-m-d", strtotime($data['BEG_DATE']));
        $msg["ABSENT_END"] = date("Y-m-d", strtotime($data['END_DATE']));
        $msg["ABSENT_DT"] = $data['QTY'];
        $msg["ABSENT_DESC"] = $data['NOTE'];
        $msg["msg"] = "OK";
    }else {
        $msg["msg"] = "FALSE";
    }
    echo json_encode($msg);
}
?>
