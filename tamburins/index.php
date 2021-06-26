<?php
include "_conf.php";
include_once rootDir . "layout/top.php";

include "../my_info/ajax/my_tamburins.php";

$in_applying=false;
$check_sql="SELECT PA_IDX, PAYMENT_CMPL FROM ProductApplies_TB WHERE PGM_IDX=".$_SESSION['PGM_IDX']." AND PAYMENT_CMPL IN (0,1,2) AND PA_IDX!=0 ORDER BY PA_IDX DESC";
$check_res=sql_query($check_sql,null,$Conn4);
if(sql_num_rows($check_res)){
    $check_data=sql_fetch_assoc($check_res);
    include "applying.php";
    $in_applying=true;
}
?>


<form id="insert_form" name="insert_form" action="_proc.php" method="POST" autocomplete="off">
    <input type="hidden" name="mode" value="insert_productApplies"/>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive sub-table-wrap">
                    <div class="sub-table-top">
                        <button type="button" onclick="product_list();" class="btn btn-blank btn-sm">제품선택</button>
                    </div>
                    <table class="table table-hover sub-table">
                        <colgroup>
                            <col style="">
                            <col style="">
                            <col style="width: 10%">
                            <col style="width: 15%">
                        </colgroup>
                        <thead>
                        <th class="text-center">제품명(영문)</th>
                        <th class="text-center">가격</th>
                        <th class="text-center">수량</th>
                        <th class="text-center">삭제</th>
                        </thead>
                        <tbody class="added_list">
                        <tr>
                            <td colspan='4' class="text-center">No data available</td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="sub-table-bottom">
                        <div class="sub-table-info">
                            <p>
                                * 한도 금액은 실 결제액 기준 연 500만 원이며 올해 1월 1일 ~ 12월 31일까지의 금액만 측정합니다.<br>
                                * 연도가 바뀌면 구매 총액은 초기화됩니다.<br><br>
                                <span class="notice">* 결제가 완료되지 않은 신청에 대한 금액은 구매 총액에 포함되지 않습니다.</span>
                            </p>
                        </div>
                    </div>
                </div>
                <br/><br/>
                <div class="sub-table-wrap">
                    <div class="sub-table-top">
                        <button type="button" onclick="openDaumPostcode();" class="btn btn-blank btn-sm">주소검색</button>
                    </div>
                    <div class="sub-table--2">
                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group form-group-default">
                                    <input type="text" id="address" name="address" class="form-control"
                                           placeholder="주소" readonly/>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group form-group-default">
                                    <input type="text" id="zipcode" name="zipcode" class="form-control"
                                           placeholder="우편번호" autocomplete="off" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <input type="text" id="addressSub" name="addressSub" class="form-control"
                                       placeholder="상세 주소" autocomplete="off" required/>
                            </div>
                            <div class="col-sm-2">
                                <input type="text" id="deliName" name="deliName" class="form-control"
                                       placeholder="받는분 성함" autocomplete="off" />
                            </div>
                            <div class="col-sm-3">
                                <input type="text" id="phone" name="phone" onkeyup="keyup_hp(this)" maxlength="11"
                                       class="form-control"
                                       placeholder="휴대폰 번호 (- 표시 없이 입력하세요.)" autocomplete="off" />
                            </div>
                            <div class="col-sm-3">
                                <input type="text" id="tamburinsId" name="tamburinsId" class="form-control"
                                       placeholder="탬버린즈 아이디" autocomplete="off" />
                            </div>
                        </div>
                        <div class="row" style="margin-top: 8px;">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="note">비고</label>
                                    <textarea class="form-control" id="note" name='note' rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="paging-wrap text-right">
                    <button id="payment-next" type="button" class="btn" onclick="f_submit(); ">신청하기</button>
                    <!-- <label id="payment-ing" class="checkbox-label" style="display: none">결제가 진행 중입니다.</label> -->
                </div>
            </div>
        </div>
    </div>
</form>

<?php
include "product_list.php";
?>

<script type="text/javascript">
    var applying_form = document.getElementById("applying_form");
    if(applying_form){
        document.getElementById("insert_form").style.display='none';
    }
    var form = document.getElementById('insert_form');
    $(document).ready(function () {

        <? if($in_applying){ ?>
        load_applying(<?=$check_data['PA_IDX']?>);
        <? } ?>
        var total_amt   = document.createElement('input');
        total_amt.setAttribute('type', 'hidden');
        total_amt.setAttribute('id', 'total_amt');
        total_amt.setAttribute('name', 'total_amt');
        form.prepend(total_amt);
        document.getElementById("myinfo_tamburins_coment").style.display='inline';

    });
    function product_list() {
        paging(1);
        $("#product_list").fadeIn();
    }

    function f_close() {
        $("#product_list").fadeOut();
    }

    function del_added_one(obj) {
        $(obj).parent().parent().remove();
        if ($(".added_one").length == 0) {
            var html = "<tr> <td colspan='4' class='text-center'>No data available</td></tr>";
            $(".added_list").append(html);
        }
    }

    function openDaumPostcode() {
        new daum.Postcode({
            oncomplete: function (data) {
                // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.
                // 우편번호와 주소 정보를 해당 필드에 넣고, 커서를 상세주소 필드로 이동한다.
                document.getElementById('address').value = data.postcode1 + data.postcode2 + ' ' + data.address
                //전체 주소에서 연결 번지 및 ()로 묶여 있는 부가정보를 제거하고자 할 경우,
                //아래와 같은 정규식을 사용해도 된다. 정규식은 개발자의 목적에 맞게 수정해서 사용가능
                //var addr = data.address.replace(/(\s|^)\(.+\)$|\S+~\S+/g, '');
                //document.getElementById('addr').value = addr;
                document.getElementById('zipcode').value = data.zonecode
                document.getElementById('addressSub').focus();
            }
        }).open();
    }

    function autoHypenPhone(str) {
        str = str.replace(/[^0-9]/g, '');
        var tmp = '';

        if (str.length > 11) {
            tmp += str.substr(0, 11);
            return tmp;
        } else {
            return str;
        }

    }

    function keyup_hp(t) {
        var res = autoHypenPhone($(t).val());
        $(t).val(res);
    }

    function f_submit() {

        var change = '<?=$balance_amount?>';
        if(change<=0){
            alert("잔여 총액을 확인해주세요.");
            return;
        }
        if ($(".added_one").length == 0) {
            alert("복지상품을 선택해주세요.");
            return;
        }
        if ($("#address").val() == ""|| $("#zipcode").val() == "") {
            alert("주소를 입력해주세요.");
            openDaumPostcode();
            return;
        }
        if($("#addressSub").val() == ""){
            alert("주소를 입력해주세요.");
            $("#addressSub").focus();
            return;
        }
        if ($("#deliName").val() == "") {
            alert("받는분 성함을 입력해주세요.");
            $("#deliName").focus();
            return;
        }
        if($("#phone").val() == ""){
            alert("휴대폰 번호를 입력해주세요.");
            $("#phone").focus();
            return;
        }
        if($("#tamburinsId").val() == ""){
            alert("탬버린즈 아이디를 입력해주세요.");
            $("#tamburinsId").focus();
            return;
        }

        var sum = 0;
        $('.sprice').each(function(){
            sum += parseInt($(this).val());
        });

        if(sum>change){
            alert("잔여 총액을 초과하였습니다.");
            return;
        }
        $("#total_amt").val(sum);
        var total_amt = $("#total_amt").val();

        $.ajax({
            type: "GET",
            url: "_proc.php",
            data: { mode: "check_IdAdr", id:$("#tamburinsId").val(), adr:$("#address").val() },
        }).done(function (msg) {
            if(msg=='no id'){
                alert('탬버린즈 회원이 아닙니다.');
            } else if(msg=='no adr'){
                alert('사용할 수 없는 주소입니다.');
            } else {
//                 $("#payment-next").css("display","none");
//                 $("#payment-ing").css("display","block");
        		$.ajax({
                    type: "POST",
                    url: "_proc.php",
                    data: $("#insert_form").serialize(),
                }).done(function (msg) {
                    alert(msg);
                    location.href = "../my_info/index.php?page=tamburins";
//                     var win = window.open('', 'Tamburins',
//                             'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=540,height=700,left=100,top=100'
//                         );
//                         win.document.write('<iframe width=100%, height=650 src="' + msg +
//                             '" frameborder=0 allowfullscreen></iframe>');
                });
            }
        });
    }

    function cancel_order(){
        var paidx = $("#paidx").val();

        if(confirm("해당 신청을 취소하시겠습니까?")){
            $.ajax({
                type: "GET",
                url: "_proc.php",
                data: { mode: "cancel_order", paidx: paidx },
            }).done(function (msg) {
                alert(msg);
                location.reload();
            })
        }else{}

    }

    function test(){
    	IMP.init('imp81681216'); // 'iamport' 대신 부여받은 "가맹점 식별코드"를 사용
        IMP.request_pay({
            pg : 'inicis', // version 1.1.0부터 지원.
            pay_method : 'card',
            merchant_uid : 'merchant_' + new Date().getTime(),
            name : '주문명:결제테스트',
            amount : 14000,
            buyer_email : 'iamport@siot.do',
            buyer_name : '구매자이름',
            buyer_tel : '010-1234-5678',
            buyer_addr : '서울특별시 강남구 삼성동',
            buyer_postcode : '123-456',
            m_redirect_url : 'https://welfaretmp.innergm.com/page/tamburins/'
        }, function(rsp) {
            if ( rsp.success ) {
                var msg = '결제가 완료되었습니다.';
                msg += '고유ID : ' + rsp.imp_uid;
                msg += '상점 거래ID : ' + rsp.merchant_uid;
                msg += '결제 금액 : ' + rsp.paid_amount;
                msg += '카드 승인번호 : ' + rsp.apply_num;
            } else {
                var msg = '결제에 실패하였습니다.';
                msg += '에러내용 : ' + rsp.error_msg;
            }
            alert(msg);
        });
    }

    function payment_order(){
    	alert("결제는 수수료 협의 후 kcp 작업 예정입니다.");
    	var paidx = $("#paidx").val();
        $.ajax({
            type: "GET",
            url: "_proc.php",
            data: { mode: "payment_order", paidx: paidx },
        }).done(function (msg) {
            alert(msg);
            location.reload();
        });
    }
</script>
<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<style>


</style>

<?php

include_once rootDir . "layout/foot.php";
?>
