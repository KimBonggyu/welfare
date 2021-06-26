<?php
include "_conf.php";


if ($sdate == "") {
    $sdate = date("Y-m-d", strtotime(date("Y") . "-01-01"));
}
if ($edate == "") {
    $edate = date("Y-m-d");
}

?>

<?php include "my_tamburins.php"; ?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group form-group-default">
                        <label>시작일</label>
                        <input type="text" name="sdate" id="sdate" class="form-control datedate-ajax datedate"
                               value="<?= $sdate ?>" style="background-color: unset;" readonly/>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group form-group-default">
                        <label>종료일</label>
                        <input type="text" name="edate" id="edate" class="form-control datedate-ajax"
                               value="<?= $edate ?>"  style="background-color: unset;" readonly/>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group form-group-default">
                        <label>처리상태</label>
                        <select class="form-control" name="flag" id="flag">
                            <option value="">전체</option>
                            <option value="'0'" <?= ($flag == "'0'" ? "selected" : "") ?>>구매신청</option>
                            <option value="1" <?= ($flag == "1" ? "selected" : "") ?>>구매승인(걸제대기)</option>
                            <option value="2" <?= ($flag == "2" ? "selected" : "") ?>>결제진행중</option>
                            <option value="3" <?= ($flag == "3" ? "selected" : "") ?>>결제완료(배송준비)</option>
                            <option value="5" <?= ($flag == "5" ? "selected" : "") ?>>배송처리(완료)</option>
                            <option value="8" <?= ($flag == "8" ? "selected" : "") ?>>구매반려</option>
                            <option value="9" <?= ($flag == "9" ? "selected" : "") ?>>구매기간 만료</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-1">
                    <div class="form-group form-group-default" style="position: relative; height: 100%;">
                        <button class="btn btn-round btn-just-icon search-btn" type="button" onclick="searching();">
                            <i class="material-icons">search</i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th class="text-center">주문번호</th>
                        <th class="text-center">신청상품</th>
                        <th class="text-center">수량</th>
                        <th class="text-center">주소</th>
                        <th class="text-center">처리상태</th>
                    </tr>
                    </thead>
                    <tbody id="list_box" class="info-table-btn--2">
                    </tbody>
                </table>
            </div>
            <div class="paging-wrap">
                <div class="paging"></div>
                <div class="row-select">
                    행 개수 : <select name="rows" id="rows" class="form-control"
                                   style="width: 50px; display: inline-block;" onchange="searching();">
                        <option value="10" <?= ($rows == 10 ? "selected" : "") ?>>10</option>
                        <option value="20" <?= ($rows == 20 ? "selected" : "") ?>>20</option>
                        <option value="30" <?= ($rows == 30 ? "selected" : "") ?>>30</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var pageno = 1;
    var sdate_base = '<?=date("Y-m-d", strtotime(date("Y") . "-01-01"))?>';
    var edate_base = '<?=date("Y-m-d")?>';
    var flag_base = "";
    var rows_base = 10;
    $(document).ready(function () {
        searching();
    });

    function searching() {
        sdate_base = $("#sdate").val();
        edate_base = $("#edate").val();
        flag_base = $("#flag").val();
        rows_base = $("#rows").val();
        load_list(1, sdate_base, edate_base, flag_base, rows_base);
    }

    function paging(flag) {
        pageno = flag;
        load_list(pageno, sdate_base, edate_base, flag_base, rows_base);
    }

    function load_list(pageno, sdate, edate, flag, rows) {
        $.ajax({
            type: "GET",
            url: "_proc.php",
            data: {mode: "load_tamburins_product", pageno: pageno, sdate: sdate, edate: edate, payment_cmpl: flag, rows: rows},
            dataType: "json"
        }).done(function (msg) {
            $("#list_box").empty();
            $("#list_box").append(msg.rows);
            $(".paging").empty();
            $(".paging").append(msg.paging);

        });
    }

    function comma_toprice(price){
        return price.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
    }

    function f_cancel(idx) {
        if (confirm("해당 신청을 취소하시겠습니까?")) {
        } else {
            return;
        };
        $.ajax({
            type: "GET",
            url: "_proc.php",
            data: {mode: "cancel_product", idx: idx}
        }).done(function (msg) {
            alert(msg);
            open_link('product');
        });
    }

    $(function() {
        datepickerAjax();
    });

    function datepickerAjax(){
        $( ".datedate-ajax" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            dayNames: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
            dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
            monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월']
        });
    }
</script>

<style>


</style>

<?php

include_once rootDir . "layout/foot.php";
?>
