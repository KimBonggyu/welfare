<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js" ></script>
<script type="text/javascript" src="https://cdn.iamport.kr/js/iamport.payment-1.1.5.js"></script>

<form id="applying_form" name="applying_form" action="_proc.php" method="POST" autocomplete="off">
    <input type="hidden" name="paidx" id="paidx" value="<?=$check_data['PA_IDX']?>"/>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive sub-table-wrap">
                    <table class="table table-hover sub-table">
                        <thead>
                        <tr>
                            <th class="text-center">신청상품</th>
                            <th class="text-center">수량</th>
                            <th class="text-center">총액</th>
                            <th class="text-center">주소</th>
                            <th class="text-center">처리상태</th>
                        </tr>

                        </thead>
                        <tbody id="applies_list_box" class="info-table-btn--2">
                        </tbody>
                    </table>
                    <div class="sub-table-bottom">
                        <div class="sub-table-info">
                            <p>
                                * 구매 신청 시 재고 파악 후 구매 승인이 돼야 결제가 가능합니다.<br>
                                * 신청 정보에 대한 내용은 내 정보의 <a href="../my_info/index.php?page=tamburins">탬버린즈 상품 구매내역</a>에서 확인하실 수 있습니다. <br/><br/>
                                <span style="color: red;">* 신청, 결제가 진행 중인 경우 추가 구매신청을 할 수 없습니다.<br>
                                * 반려된 품목은 결제 내용에 포함하지 않습니다.</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="paging-wrap text-right">
                    <?if($check_data["PAYMENT_CMPL"]==1){ ?>
                    <button type="button" class="btn btn-default" onclick="payment_order()">결제하기</button>
                    <? } ?>
                    <button type="button" class="btn btn-blank" onclick="cancel_order()">취소하기</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">

$(document).ready(function () {
});

function load_applying(paidx) {

    $.ajax({
        type: "GET",
        url: "_proc.php",
        data: { mode: "load_applying", paidx:paidx},
        dataType: "json"
    }).done(function (msg) {
        $("#applies_list_box").empty();
        $("#applies_list_box").append(msg.rows);
    });
}


</script>