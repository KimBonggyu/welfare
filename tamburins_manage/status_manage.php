<?php
include "_conf.php";
include_once rootDir."layout/top.php";

$cntPerPage = 30; // 한페이지 출력개수
$PAGE_PER_BLOCK = 10;

if($rows){
    $cntPerPage = $rows;
}

if ($pageno == "")
    $pageno = 1;

/////////////////////////////////////////////////////////////////////////////////////
//////////////////////// 복지가능한 제품들 PCODE 최신등록순 START ////////////////////////
$checksql = "SELECT * FROM AvailableProduct_TB ORDER BY AP_IDX ASC";
$checkres = sql_query($checksql, null, $Conn4);
$checkdata = array();
while($row = sql_fetch_assoc($checkres)){
    $checkdata[] = $row["PCODE"];
}
$order_pcode = implode(',', $checkdata);
//////////////////////// 복지가능한 제품들 PCODE 최신등록순 END /////////////////////////
////////////////////////////////////////////////////////////////////////////////////


$searchSql = array();

if($check_active){
    if($order_pcode==""){
    }else{
        if($check_active=="active"){
            $searchSql[]=" P_CODE IN (".$order_pcode.")";
        }else if($check_active=="deactive"){
            $searchSql[]=" P_CODE NOT IN (".$order_pcode.")";
        }
    }
}

if($name){
    $searchSql[]=" (PRODUCT_DESCRIPTION_SHORT like '%$name%' OR P_CODE like '%$name%')";
}

if ($searchSql) {
    $search_res = " where " . @implode(" and ", $searchSql);
}

$num_per_page = 10;
$page_per_block = 10;


$sql = "SELECT MP_IDX, P_CODE, P_88CODE, PRODUCT_DESCRIPTION_SHORT FROM Master_Product_copy ".$search_res;
$res = sql_query($sql,null,$Conn7);


// //////////////////////////////////////////////////////////////////////////////
// 페이징정보 마무리
// //////////////////////////////////////////////////////////////////////////////
$recordCnt = $recordCnt2 = $total_c = sql_num_rows($res);

$totalpage = ceil($total_c / $cntPerPage);
if ($totalpage < 1)
    $totalpage = 1;
$this_start_num = $pageno * $cntPerPage - $cntPerPage;

// //////////////////////페이징끝///
$addSql .= " ORDER BY FIELD(P_CODE, $order_pcode) DESC, MP_IDX DESC limit $this_start_num,$cntPerPage ";
//$res = sql_query($sql,null,$Conn2);
?>
<!--##등록기능 주석 start-->
<!--
<div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form name="insert_form" id="insert_form" autocomplete="off">
                    <input type="hidden" name="mode" value="insert_ap">
                    <div class="row" >
                        <div class="col-sm-9 store" >
                            <div class="form-group form-group-default">
                                <label>검색</label>
                                <input type="text" class="form-control" placeholder="이름 검색" id="search_name" name="search_name" onkeyup="name_search(this.value)"/>
                                <div id="display"></div>
                                <input type="hidden" id="add_pcode" name="add_pcode">
                                <input type="hidden" id="add_88code" name="add_88code">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group form-group-default" style="height: 60px; line-height: 60px;">
                                <button type="button" class="btn btn-sm" style="width:90px;" onclick="add_ap();">등록</button>
                                <button type="button" class="btn btn-sm" style="width:90px;" onclick="popupOpen('excel_pop.php',500,300)">엑셀등록</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
</div>
-->
<!--##등록기능 주석 end-->
<form id="search" name="search" autocomplete="off">
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row row--middle">
                <div class="col-sm-2">
                    <div class="form-group form-group-default">
                        <select class="form-control" name="check_active" id="check_active">
                            <option value="" <?=$check_active=="" ? "selected" : ""?>>전체</option>
                            <option value="active" <?=$check_active=="active" ? "selected" : ""?>>활성</option>
                            <option value="deactive" <?=$check_active=="deactive" ? "selected" : ""?>>비활성</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-5 " >
                    <div class="form-group form-group-default">
                        <div class="form-search">
                            <input type="text" class="form-control" placeholder="제품명, 제품코드 검색" id="name" name="name" value="<?=$name?>"  onkeypress="if(event.keyCode==13){submit();}"/>
                            <button class="btn btn-round btn-just-icon" type="submit"
                                    onclick="">
                                <i class="material-icons">search</i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 text-right">
                    <button type="button" class="btn btn-default btn-sm checkbtn"
                            onclick="activation_item_arr(1);" disabled>선택항목 활성화
                    </button>
                    <button type="button" class="btn btn-blank btn-sm checkbtn"
                            onclick="activation_item_arr(2);" disabled>선택항목 비활성화
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="form-group form-group-default">
                    <span class="notice">* 활성화 시 해당 제품만 복지 신청이 가능합니다.</span>
                </div>
                <div class="table-wrap">
                    <div class="status-table-fixed-wrap">
                        <table class="table">
                            <thead>
                            <tr >
                                <th class="text-center" width="29px" >
                                    <input type="checkbox" name="multi_check" value="T"
                                        onclick="if($(this).is(':checked')==true){$('.check_pidx').prop('checked',true);$('.checkbtn').attr('disabled',false);}else{$('.check_pidx').prop('checked',false);$('.checkbtn').attr('disabled',true);}"/>
                                </th>
                                <th class="text-center">이름</th>
                                <th class="text-center">제품코드</th>
                                <th class="text-center">88코드</th>
                                <th class="text-center">활성화/비활성화</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $sql =$sql.$addSql;
                            $res = sql_query ($sql,null,$Conn7);
                            if($recordCnt != 0){
                                for($i = $this_start_num; $i <= $data = sql_fetch_assoc ( $res ); $i ++) {
                                    ?>
                                    <tr class="">
                                        <td style="text-align:center;" onclick="event.cancelBubble=true;">
                                            <input type="checkbox" id="chk_<?=$i?>" name="check_pidx[]" class="check_pidx"
                                                value="<?=$data['P_CODE']?>" title="내역선택" onclick="btn_onoff();">
                                        </td>
                                        <td class="text-center" >
                                            <?=$data['PRODUCT_DESCRIPTION_SHORT']?>
                                        </td>
                                        <td class="text-center" >
                                            <?=$data['P_CODE']?>
                                        </td>
                                        <td class="text-center" >
                                            <?=$data['P_88CODE']?>
                                        </td>
                                        <td class="text-center" >
                                            <? if(array_search($data['P_CODE'], $checkdata) === false) { ?>
                                            <button type="button" class="btn btn-default btn-sm" onclick="active_item('<?=$data['P_CODE']?>')">활성화</button>
                                            <? }else{ ?>
                                            <button type="button" class="btn btn-blank btn-sm" onclick="deactive_item('<?=$data['P_CODE']?>')">비활성화</button>
                                            <? } ?>
                                        </td>

                                    </tr>
                                    <?php
                                }
                            }else{
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        No Data
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="paging-wrap">
                    <div class="pagination">
                        <?php
                        parse_str($_SERVER['QUERY_STRING'],$HTTPQueryArray);
                        unset($HTTPQueryArray['pageno']);

                        $param = '&'.http_build_query($HTTPQueryArray);
                        include rootDir."inc/pageing.php";
                        ?>
                    </div>
                    <div class="row-select">
                        행 개수 : <select name="rows" id="rows" class="form-control"
                                    style="width: 50px; display: inline-block;" onchange="change_rows();">
                            <option value="30" <?= ($rows == 30 ? "selected" : "") ?>>30</option>
                            <option value="50" <?= ($rows == 50 ? "selected" : "") ?>>50</option>
                            <option value="100" <?= ($rows == 100 ? "selected" : "") ?>>100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">

    function change_rows(){
        var keyword = $("#name").val();
        var rows = $("#rows").val();
        var check_active = $("#check_active").val();
        location.href='status_manage.php?name='+keyword+'&rows='+rows+'&check_active='+check_active;
    }

    function btn_onoff() {
        var check = false;
        $("input[name='check_pidx[]']").each(function () {
            if ($(this).is(":checked") == true) {
                check = true;
            }
        });
        if (check == true) {
            $(".checkbtn").attr("disabled", false);
        } else {
            $("input[name='multi_check']").attr("checked",false);
            $(".checkbtn").attr("disabled", true);
        }
    }

    function active_item(idx) {
        if(confirm("활성화 하시겠습니까?")){}else{return;}

        $.ajax({
            type:"GET",
            url:"_proc.php",
            data:{mode:"active_item", p_code: idx}
        }).done(function(msg){
            if(msg=="OK"){
                alert("활성화 되었습니다.");
                location.reload();
            }else{
                alert("활성화 실패하였습니다. IT기획파트에 문의바랍니다.");
            }
        });
    }
    function deactive_item(idx) {
        if(confirm("비활성화 하시겠습니까?")){}else{return;}

        $.ajax({
            type:"GET",
            url:"_proc.php",
            data:{mode:"deactive_item", p_code: idx}
        }).done(function(msg){
            if(msg=="OK"){
                alert("비활성화 되었습니다.");
                location.reload();
            }else if(msg=="FALSE"){
                alert("비활성화 실패하였습니다. IT기획파트에 문의바랍니다.");
            }else{
                alert("비활성화 도중 오류가 발생하였습니다. IT기획파트에 문의바랍니다.");
            }
        });
    }

    function activation_item_arr(flag) {
        var arr_item = Array();
        $("input[name='check_pidx[]']").each(function () {
            if ($(this).is(":checked") == true) {
                arr_item[arr_item.length] = $(this).val();
            }
        });

        if (flag == '1') {
            if (confirm("선택항목들을 활성화 하시겠습니까?")) {
                if(check_active_item(flag, arr_item)){
                }else{
                    alert("선택하신 제품 중 이미 활성화된 제품이 있습니다.");
                    return;
                }
            } else {return;}
        }else{
            if (confirm("선택항목들을 비활성화 하시겠습니까?")) {
                if(check_active_item(flag, arr_item)){
                }else{
                    alert("선택하신 제품 중 이미 비활성화된 제품이 있습니다.");
                    return;
                }
            } else {return;}
        }
        $.ajax({
            type: "GET",
            url: "_proc.php",
            data: {mode: "activation_item_arr", arr_item: arr_item, flag: flag}
        }).done(function (msg) {
            alert(msg);
            location.reload();
        });
    }

    function check_active_item(flag, arr_item) {
        var check = false;
        $.ajax({
            type: "GET",
            url: "_proc.php",
            async: false,
            data: {mode: "check_active_item", arr_item: arr_item, flag: flag}
        }).done(function (msg) {
            if(msg=="OK"){
                check = true;
            }else{}
        });
        return check;
    }

    /* ##불필요기능 주석 start */
    /*
    function add_ap() {
        if($("#add_pcode").val()==""){
            alert("등록할 제품이 없습니다.");
            return;
        }
        $.ajax({
            type:"GET",
            url:"_proc.php",
            data: $("#insert_form").serialize()
        }).done(function(msg){
            if(msg=="OK"){
                alert("등록성공");
                location.reload();
            }else if(msg=="overlap") {
                alert("이미 등록된 제품입니다.");
            }else {
                alert("등록실패");
            }
        });
    }
    function name_search(val){
        if(val=='') {
            $("#display").hide();
        } else {
            $.ajax({
                type: "POST",
                url: "_proc.php",
                data: {mode:"search_product", val:val},
                cache: false,
                success: function(data) {
                    $("#display").html(data).show();
                }
            });
        } return false;
    }
    function setDetail(idx){
        $.ajax({
            type: "POST",
            url: "_proc.php",
            dataType:"json",
            data: {mode:"set_detail", idx:idx},
            cache: false,
            success: function(data) {
                console.log(data);
                document.getElementById("search_name").value = data.name;
                document.getElementById("add_pcode").value = data.code;
                document.getElementById("add_88code").value = data.p88code;
                $("#display").hide();
            }
        });
    }
    */
    /* ##불필요기능 주석 end */
</script>

<?php

include_once rootDir."layout/foot.php";
?>
