    <?php
include_once "_conf.php";
include_once rootDir . "layout/top.php";
if ($sdate == "") {
    $sdate = date("Y-m-d", strtotime(date("Y") . "-01-01"));
}
if ($edate == "") {
    $edate = date("Y-m-d");
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body pm-top-wrap">
            <div class="text-right">
                <button type="button" class="button"  onclick="location.href='status_manage.php';">
                    <i class="material-icons">settings</i>
                </button>
            </div>
            <div class="row card-search">
                <div class="col-sm-5">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group form-group-default">
                                <select class="form-control" name="excel_year">
                                    <?php
                                    for ($i = date("Y"); $i >= 2011; $i--) {
                                        ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-group-default">
                                <select class="form-control" name="excel_month">
                                    <?php
                                    for ($i = 1; $i <= 13; $i++) {
                                        if($i==13){
                                        ?>
                                        <!--<option value="<?/*= $i */?>" <?/*=date("n")==$i ? "selected" : "" */?>>전체</option>-->
                                        <? }else{?>
                                        <option value="<?= $i ?>" <?=date("n")==$i ? "selected" : "" ?>><?= $i ?>월</option>
                                        <?php
                                    } }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group form-group-default">
                                <select class="form-control" name="excel_type">
                                    <!--<option value="normal">엑셀 양식 다운로드</option>-->
                                    <option value="3pl">3PL 전달용</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group form-group-default">
                                <button type="button" class="btn btn-blank btn-sm" onclick="f_excel();">
                                    EXCEL&nbsp;&nbsp;<i class="fa fa-download" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-7">
                    <div class="form-group form-group-default" style="text-align: right;">
                        <button type="button" class="btn btn-blank btn-sm checkbtn" onclick="updateApply_arr('1');" disabled>
                        선택항목 사용 승인
                        </button>
                        <button type="button" class="btn btn-blank btn-sm checkbtn" onclick="updateApply_arr('8');" disabled>
                        선택항목 반려
                        </button>
                        <button type="button" class="btn btn-blank btn-sm checkbtn" onclick="updateApply_arr('0');" disabled>
                        선택항목 처리 취소
                        </button>
                        <button type="button" class="btn btn-defualt btn-sm checkbtn"
                            onclick="updateApplyForExportCode_arr();" disabled>선택항목 출고번호 입력
                        </button>
                        <!-- 
                        <button type="button" class="btn btn-default " onclick="open_modal();">
                            개별 사용내역 등록
                        </button>
                        -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<form action="" method="get" autocomplete="off">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group form-group-default">
                            <label>신청자유형</label>
                            <select class="form-control" name="group" id="group">
                                <option value="member" <?= ($group == "member" ? "selected" : "") ?>>직원
                                <!--<option value="parttimer" <?/*= ($group == "parttimer" ? "selected" : "") */?>>계약직 직원</option>-->
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group form-group-default">
                            <label>검색유형</label>
                            <select class="form-control" name="column" id="column">
                                <option value="UserName" <?= ($column == "UserName" ? "selected" : "") ?>>신청자
                                <option value="P_NAME" <?= ($column == "P_NAME" ? "selected" : "") ?>>상품 종류
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group form-group-default">
                            <label style="color:white;">#</label>
                            <div class="form-search">
                                <input type="text" id="keyword" name="keyword" class="form-control"
                                    placeholder="검색하기(신청자,상품종류)" value="<?= $keyword ?>"
                                    onkeypress="if(event.keyCode==13){searching();}"/>
                                <button class="btn btn-round btn-just-icon" type="button" onclick="searching();">
                                    <i class="material-icons">search</i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group form-group-default">
                            <label>시작일</label>
                            <input type="text" name="sdate" id="sdate" class="form-control datedate"
                                   value="<?= $sdate ?>" style="background-color: unset;" readonly/>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group form-group-default">
                            <label>종료일</label>
                            <input type="text" id="edate" name="edate" class="form-control datedate"
                                   value="<?= $edate ?>"  style="background-color: unset;" readonly/>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group form-group-default">
                            <label>처리상태</label>
                            <div class="form-search">
                                <select class="form-control" name="flag" id="flag">
                                    <option value="">전체</option>
                                    <option value="'0'" <?= ($flag == "'0'" ? "selected" : "") ?>>구매신청</option>
                                    <option value="1" <?= ($flag == "1" ? "selected" : "") ?>>구매승인(결제대기)</option>
                                    <option value="2" <?= ($flag == "2" ? "selected" : "") ?>>결제진행중</option>
                                    <option value="3" <?= ($flag == "3" ? "selected" : "") ?>>결제완료(배송준비)</option>
                                    <option value="5" <?= ($flag == "5" ? "selected" : "") ?>>배송처리(완료)</option>
                                    <option value="8" <?= ($flag == "8" ? "selected" : "") ?>>구매반려</option>
                                    <option value="9" <?= ($flag == "9" ? "selected" : "") ?>>구매기간 만료</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-wrap">
                    <div class="table-fixed-wrap">
                        <table class="table table--tamburins table-hover" id="table">
                            <colgroup>
                                <col style="width: 30px;">
                                <col style="width:">
                                <col style="width:">
                                <col style="width: 15%;">
                                <col style="width:">
                                <col style="width:">
                                <col style="width: 19%;">
                                <col style="width:">
                                <col style="width:">
                                <col style="width:">
                                <?if($_SESSION["PGM_IDX"]=='101647'||$_SESSION["PGM_IDX"]=='101722'){?>
                                <col style="width:">
                                <?}?>
                            </colgroup>
                            <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" name="multi_check" value="T"
                                        onclick="if($(this).is(':checked')==true){$('.check_idx').prop('checked',true);$('.checkbtn').attr('disabled',false);}else{$('.check_idx').prop('checked',false);$('.checkbtn').attr('disabled',true);}"/>
                                </th>
                                <th class="text-center">결재번호</th>
                                <th class="text-center">신청일</th>
                                <th class="text-center">부서명</th>
                                <th class="text-center">신청자</th>
                                <th class="text-center">처리상태</th>
                                <th class="text-center">상품 종류</th>
                                <th class="text-center">상품 코드</th>
                                <th class="text-center">수량</th>
                                <th class="text-center">출고번호</th>
                                <th class="text-center">상세보기</th>
                            </tr>

                            </thead>
                            <tbody id="list_box">

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="paging-wrap">
                    <div class="paging"></div>
                    <div class="row-select">
                        행 개수 : <select name="rows" id="rows" class="form-control"
                                       style="width: 50px; display: inline-block;" onchange="searching();">
                            <option value="100" <?= ($rows == 100 ? "selected" : "") ?>>100</option>
                            <option value="200" <?= ($rows == 200 ? "selected" : "") ?>>200</option>
                            <option value="300" <?= ($rows == 300 ? "selected" : "") ?>>300</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
include "modal.php";
include "order_detail.php";
include "export_code.php";
include "comment.php";
?>


<script type="text/javascript">
    var pageno = 1;
    var keyword_base = "";
    var column_base = "";
    var sdate_base = '<?=date("Y-m-d", strtotime(date("Y") . "-01-01"))?>';
    var edate_base = '<?=date("Y-m-d")?>';
    var flag_base = "";
    var rows_base = 100;
    var group_base = "member";
    $(document).ready(function () {
        searching();
    });

    function searching() {
        keyword_base = $("#keyword").val();
        column_base = $("#column").val();
        sdate_base = $("#sdate").val();
        edate_base = $("#edate").val();
        flag_base = $("#flag").val();
        rows_base = $("#rows").val();
        group_base = $("#group").val();
        load_list(1, keyword_base, column_base, sdate_base, edate_base, flag_base, rows_base,group_base);
    }

    function paging(flag) {
        pageno = flag;
        load_list(pageno, keyword_base, column_base, sdate_base, edate_base, flag_base, rows_base,group_base);
    }

    function load_list(pageno, keyword, column, sdate, edate, flag, rows, group) {
        $.ajax({
            type: "GET",
            url: "_proc.php",
            data: {
                mode: "load_list",
                pageno: pageno,
                keyword: keyword,
                column: column,
                sdate: sdate,
                edate: edate,
                flag: flag,
                rows: rows,
                group: group
            },
            dataType: "json"
        }).done(function (msg) {
            $("#list_box").empty();
            $("#list_box").append(msg.rows);
            $(".paging").empty();
            $(".paging").append(msg.paging);
            btn_onoff();
        });
    }

    function f_excel() {
        var year = $("select[name=excel_year]").val();
        var month = $("select[name=excel_month]").val();
        var type = $("select[name=excel_type]").val();
        var arr_item = Array();
        var check = false;
        $("input[name='check_idx[]']").each(function () {
            if ($(this).is(":checked") == true) {
                check = true;
                arr_item[arr_item.length] = $(this).val();
            }
        });
        if(type=="3pl"){
            if(check){
                location.href = 'print_excel_out_kbg.php?arr_item='+arr_item;
            }else{
                location.href = 'print_excel_out_kbg.php?year='+year+'&month='+month;
            }
        }else{
            location.href = 'print_excel.php?year=' + year+'&month='+month+'&type='+type;
        }
    }

    function btn_onoff() {
        var check = false;
        $("input[name='check_idx[]']").each(function () {
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

    function open_modal() {

        $("#add_product_applies").fadeIn();
        open_member_list(1);
    }

    function f_close() {
        $("#add_product_applies").fadeOut();
    }
    
    function updateApply_arr(flag) {
        var arr_item = Array();
        var check = false;
        $("input[name='check_idx[]']").each(function () {
            if ($(this).is(":checked") == true) {
                if ($(this).attr("flag") != '0') {
                    check = true;
                }
                arr_item[arr_item.length] = $(this).val();
            }
        });
        if(flag!='0'){
            if (check == true) {
                alert("이미 승인 또는 반려된 신청 건이 포함되어 있습니다..");
                return;
            }
        }
        if (flag == '1') {
            if (confirm("선택항목들을 승인하시겠습니까?")) {
            } else {
                return;
            }
        } else if(flag == '8') {
            if (confirm("선택항목들을 반려하시겠습니까?")) {
                updateApplyForComment_arr();
                return;
            } else {
                return;
            }
        }else{
            if (confirm("결재대기 상태로 변경하시겠습니까?")) {
                flag=0;
            } else {
                return;
            }
        }

        $.ajax({
            type: "GET",
            url: "_proc.php",
            data: {mode: "update_status_arr", arr_item: arr_item, flag:flag}

        }).done(function (msg) {
            alert(msg);
            load_list(pageno, keyword_base, column_base, sdate_base, edate_base, flag_base, rows_base,group_base);
        });
    }

    function open_detail(idx){
        $("#tamburins-order-detail").fadeIn();
        load_order(idx);
    }

    function close_detail() {
        $("#tamburins-order-detail").fadeOut();
    }

    function updateOrder(idx, flag) {
        if(confirm("해당 품목을 반려하시겠습니까?")){
            updateApplyForComment_arr('unit', idx);
            $("#iflag").val(flag);
        }else{
            return;
        }
    }

    function order_btn_onoff() {
        var check = false;
        $("input[name='order_check_idx[]']").each(function () {
            if ($(this).is(":checked") == true) {
                check = true;
            }
        });
        if (check == true) {
            $(".order_checkbtn").attr("disabled", false);
        } else {
            $("input[name='multi_order_check']").attr("checked",false);
            $(".order_checkbtn").attr("disabled", true);
        }
    }

    function updateOrder_arr(flag) {
        var piidx_arr = Array();
        var check = false;
        $("input[name='order_check_idx[]']").each(function () {
            if ($(this).is(":checked") == true) {
                if ($(this).attr("flag") != '0') {
                    check = true;
                }
                piidx_arr[piidx_arr.length] = $(this).val();
            }
        });
        if(flag!='0'){
            if (check == true) {
                alert("이미 승인 또는 반려된 신청 건이 포함되어 있습니다..");
                return;
            }
        }
        if (flag == '1') {
            if (confirm("선택항목들을 승인하시겠습니까?")) {
            } else {return;}
        } else if(flag == '8') {
            if (confirm("선택항목들을 반려하시겠습니까?")) {
                updateApplyForComment_arr('unit_arr', piidx_arr);
                $("#iflag").val(flag);
            } else {return;}
            return;
        }else{
            if (confirm("결재대기 상태로 변경하시겠습니까?")) {
            } else {return;}
        }
        $.ajax({
            type: "GET",
            url: "_proc.php",
            data: {mode: "update_order_arr", piidx_arr:piidx_arr, flag: flag},
            dataType: "json"
        }).done(function (msg) {
            if(msg.final=="OK"){
                if(flag=='1'){
                    alert("모든 품목의 구매 결재가 완료되어 신청 처리상태를 갱신합니다.");
                    for(var i in piidx_arr){
                        $("#update_order_btn_"+piidx_arr[i], document).attr("disabled", true);
                        $("#order_pname_"+piidx_arr[i], document).append('<span class="notice2">(승인)</span>');
                    }
                }else{
                    alert("결재 취소 처리되었습니다.");
                    for(var i in piidx_arr){
                        $("#update_order_btn_"+piidx_arr[i], document).attr("disabled", false);
                        $("#order_pname_"+piidx_arr[i], document).find('span').remove();
                    }
                }

            }else if(msg.fail>0){
                alert("승인 처리 도중 오류가 발생했습니다. IT기획파트에 문의 바랍니다.");
                return;

            }else{
                alert(msg.num+" 건 승인 처리되었습니다.");
                for(var i in piidx_arr){
                    $("#update_order_btn_"+piidx_arr[i], document).attr("disabled", true);
                    $("#order_pname_"+piidx_arr[i], document).append('<span class="notice2">(승인)</span>');
                }
            }
            
            for(var i in piidx_arr){
                $("#order_chk_"+piidx_arr[i], document).attr("checked", false);
                $("#order_chk_"+piidx_arr[i], document).attr("flag", flag);
            }
            $("#order_check_idx_th", document).attr("checked", false);
            load_list(pageno, keyword_base, column_base, sdate_base, edate_base, flag_base, rows_base,group_base);
        });
    }
    
</script>


<style>

    #insert_export_code{
        z-index:2;
    }
    #tamburins-order-detail{
        z-index:2;
    }
    #insert_comment{
        z-index:2;
    }

</style>
<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<?php

include_once rootDir . "layout/foot.php";
?>
