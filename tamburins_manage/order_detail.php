<div class="modal modal--wide" id="tamburins-order-detail"
     tabindex="0" role="dialog" aria-hidden="false">
    <div class="modal-dialog ">
        <div class="modal-content-wrapper">
            <div class="modal-content modal-content--order-detail" id="modal-content ">
                <div class="modal-header clearfix text-left modal-header--inner">
                    <div class="h4">주문 상세 내용</div>
                    <button type="button" class="close" onclick="close_detail();">
                        ×
                    </button>
                </div>
                
                <div class="modal-body">

                    <div class="member-box" id="member-box">
                    </div>


                    <div class="modal-table-wrap">
                        <div class="modal-util modal-table-top">
                            <h5 class="paidx"></h5>
                            <div class="modal-btn-wrap">
                                <button type="button" class="btn btn-blank btn-sm order_checkbtn" onclick="updateOrder_arr('1');" disabled>
                                    선택항목 사용 승인
                                </button>
                                <button type="button" class="btn btn-blank btn-sm order_checkbtn" onclick="updateOrder_arr('8');" disabled>
                                    선택항목 반려
                                </button>
                                <button type="button" class="btn btn-blank btn-sm order_checkbtn" onclick="updateOrder_arr('0');" disabled>
                                    선택항목 처리 취소
                                </button>
                            </div>
                        </div>
                        <div class="">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center" width="29px" >
                                        <input type="checkbox" name="multi_order_check" value="T" class="order_check_idx" id="order_check_idx_th"
                                            onclick="if($(this).is(':checked')==true){$('.order_check_idx').prop('checked',true);$('.order_checkbtn').attr('disabled',false);}else{$('.order_check_idx').prop('checked',false);$('.order_checkbtn').attr('disabled',true);}"/>
                                    </th>
                                    <th class="text-center">상품 종류</th>
                                    <th class="text-center">상품 코드</th>
                                    <th class="text-center">수량</th>
                                    <th class="text-center">선택 반려</th>
                                </tr>
                                </thead>
                                <tbody id="order_detail_box">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>


<script>


</script>


<script>
    $(document).ready(function() {
        $('h5.paidx').text($('div.paidx').text());
    });
    function load_order(idx) {
        console.log(idx)
        $.ajax({
            type: "GET",
            url: "_proc.php",
            data: { mode: "load_order_detail", paidx:idx},
            dataType: "json"
        }).done(function (msg) {
            $("#member-box").empty();
            $("#order_detail_box").empty();
            $("#member-box").append(msg.member_rows);
            $("#order_detail_box").append(msg.order_rows);
            $('.paidx').text('주문번호 : ' + idx);
        });
    }
</script>

<style>

</style>