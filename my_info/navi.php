<style>
    .navbar-wrapper .navbar-brand {
        display: none;
    }
</style>
<?php
// 이전 페이지 확인
$prevPage = $_SERVER["HTTP_REFERER"];
$strTok =explode('/' , $prevPage);
$prevTxt = $strTok[count($strTok)-2];
if($prevTxt == "vacation" && $msg=="apply"){
    $page = $prevTxt ;
}
?>

<ul class="nav nav--myinfo">
  <li class="nav-item nav-item--info">
    <a class="nav-link" href="javascript:void(0);" onclick="open_link('info');">내 정보 조회</a>
  </li>
  <li class="nav-item nav-item--vacation">
    <a class="nav-link" href="javascript:void(0);" onclick="open_link('vacation');">휴가 신청내역</a>
  </li>
  <li class="nav-item nav-item--product">
    <a class="nav-link" href="javascript:void(0);" onclick="open_link('product');">제품복지 신청내역</a>
  </li>
    <? if($_SESSION['PGM_IDX']=='101647' || $_SESSION['PGM_IDX']=='101722'){ ?>
        <li class="nav-item nav-item--tamburins">
            <a class="nav-link" href="javascript:void(0);" onclick="open_link('tamburins');">탬버린즈 상품 구매내역</a>
        </li>
    <? } ?>
  <li class="nav-item nav-item--airticket">
    <a class="nav-link" href="javascript:void(0);" onclick="open_link('airticket');">항공권 신청내역</a>
  </li>
  <li class="nav-item nav-item--house">
    <a class="nav-link" href="javascript:void(0);" onclick="open_link('house');">북촌채 신청내역</a>
  </li>
</ul>
<?php
include_once "msg_modal.php";
?>
<script>
    $(document).ready(function () {
        
        var page = "<?=$page?>";
        if(page==""){
            page='info';
            $('.nav-item--info').addClass('active');
        }
        open_link(page);
        var msg = "<?=$msg?>";
        if(msg=="apply"){
            open_msg();
        }else if (msg=="apply_r"){
        	location.href="index.php?page=vacation";
        }

    });
    function open_link(flag) {
        $.ajax({
            type: "POST",
            url: "ajax/my_"+flag+"_detail.php"
        }).done(function (msg) {
            $("#myinfo").empty();
            $("#myinfo").append(msg);
            $('.nav-item').removeClass('active');
            $('.nav-item--'+flag+'').addClass('active');
        });
    }
</script>
