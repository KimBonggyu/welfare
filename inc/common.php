<?php
session_start();
//임시 세션은 소문자
//DB 세션은 대문자
//
$Host = "gentlemonster-prod.cdl3turd6bdd.ap-northeast-2.rds.amazonaws.com"; // Mysql 데이타 베이스의 host 위치
$User = "gentle"; // DB id
$Password = "gentle12#";
$Db = "gentle";
$Port = "3306";

////////////////////////////////////////////////
//	DB 접속
////////////////////////////////////////////////
// $Conn = mysqli_connect($Host, $User, $Password, $Db, $Port) or die("데이터베이스 연결에 실패하였습니다.");
$Conn = mysqli_connect($Host, $User, $Password, $Db, $Port);

mysqli_select_db($Conn, $Db);
mysqli_query($Conn,"set names utf8");

$Host2 = "snoopby-aurora-cluster.cluster-cdl3turd6bdd.ap-northeast-2.rds.amazonaws.com"; // Mysql 데이타 베이스의 host 위치
$User2 = "group_wear"; // DB id
$Password2 = "group_wear12#";
$Db2 = "global";

////////////////////////////////////////////////
//	DB 접속
////////////////////////////////////////////////
$Conn2 = mysqli_connect($Host2, $User2, $Password2) or die("데이터베이스 연결에 실패하였습니다.");
mysqli_select_db($Conn2,$Db2);

mysqli_query($Conn2,"set session character_set_connection=utf8;");
mysqli_query($Conn2,"set session character_set_results=utf8;");
mysqli_query($Conn2,"set session character_set_client=utf8;");


$Host3 = "eyesgw-database.cdl3turd6bdd.ap-northeast-2.rds.amazonaws.com"; // Mysql 데이타 베이스의 host 위치
$User3 = "naongw"; // DB id
$Password3 = "1Ne2RG2w*Pr@o";
$Db3 = "innergm_gw_production";
$Port3 = "3306";

// ////////////////////////////////////////////////
// //	DB 접속
// ////////////////////////////////////////////////
$Conn3 = mysqli_connect($Host3, $User3, $Password3,$Db3,$Port3) or die("데이터베이스 연결에 실패하였습니다.");
mysqli_select_db($Conn3,$Db3);
mysqli_query($Conn3,"set names utf8");


$Host4 = "snoopby-aurora-cluster.cluster-cdl3turd6bdd.ap-northeast-2.rds.amazonaws.com"; // Mysql 데이타 베이스의 host 위치
$User4 = "welfare"; // DB id
$Password4 = "welfare12$";
$Db4 = "gm_intranet";


$Conn4 = mysqli_connect($Host4, $User4, $Password4,$Db4) or die("데이터베이스 연결에 실패하였습니다.");
mysqli_select_db($Conn4,$Db4);
mysqli_query($Conn4,"set names utf8");

$Host5 = "snoopby-aurora-cluster.cluster-cdl3turd6bdd.ap-northeast-2.rds.amazonaws.com"; // Mysql 데이타 베이스의 host 위치
$User5 = "statistics"; // DB id
$Password5 = "statistics12#";
$Db5 = "statistics_2017";

////////////////////////////////////////////////
//	DB 접속
////////////////////////////////////////////////
$Conn5 = mysqli_connect($Host5, $User5, $Password5) or die("데이터베이스 연결에 실패하였습니다.");
mysqli_select_db($Conn5,$Db5);
mysqli_query($Conn5,"set names utf8");



$Host6 = "snoopby-aurora-cluster.cluster-cdl3turd6bdd.ap-northeast-2.rds.amazonaws.com"; // Mysql 데이타 베이스의 host 위치
$User6 = "view_db"; // DB id
$Password6 = "view_db12$";
$Db6 = "gm_intranet";


$Conn6 = mysqli_connect($Host6, $User6, $Password6,$Db6) or die("데이터베이스 연결에 실패하였습니다.");
mysqli_select_db($Conn6,$Db6);
mysqli_query($Conn6,"set names utf8");


$Host7 = "tamburins-mysql.cdl3turd6bdd.ap-northeast-2.rds.amazonaws.com"; // Mysql 데이타 베이스의 host 위치
$User7 = "tam_system"; // DB id
$Password7 = "tam_system12#";
$Db7 = "Master_Data";
$port7="3307";

// $Conn = mysqli_connect($Host, $User, $Password) or die("데이터베이스 연결에 실패하였습니다.");
$Conn7 = mysqli_connect($Host7, $User7, $Password7,$Db7,$port7) or die("데이터베이스 연결에 실패하였습니다.");

mysqli_select_db($Conn7,$Db7);
mysqli_set_charset($Conn7,"utf8");



//http://www.webservicex.net/CurrencyConvertor.asmx 기준은 이쪽에서 보고 참고
$exchange=array(
	0=>"USD||KRW"
	,1=>"CNY||KRW"
	,2=>"HKD||KRW"
	,3=>"GBP||KRW"
);

include "base.php";

$sum_delivery_code=array(
"AF"=>	"AFGHANISTAN"
,"AL"=>	"ALBANIA"
,"DZ"=>	"ALGERIA"
,"AS"=>	"AMERICAN SAMOA"
,"AD"=>	"ANDORRA"
,"AO"=>	"ANGOLA"
,"AR"=>	"ARGENTINA"
,"AM"=>	"ARMENIA"
,"AU"=>	"AUSTRALIA"
,"AT"=>	"AUSTRIA"
,"AZ"=>	"AZERBAIJAN"
,"BS"=>	"BAHAMAS"
,"BH"=>	"BAHRAIN"
,"BD"=>	"BANGLADESH"
,"BB"=>	"BARBADOS"
,"BY"=>	"BELARUS"
,"BE"=>	"BELGIUM"
,"BJ"=>	"BENIN"
,"BM"=>	"BERMUDA"
,"BT"=>	"BHUTAN"
,"BO"=>	"BOLIVIA"
,"BA"=>	"BOSNIA AND HERZEGOVINA"
,"BW"=>	"BOTSWANA"
,"BR"=>	"BRAZIL"
,"BN"=>	"BRUNEI"
,"BG"=>	"BULGARIA"
,"BF"=>	"BURKINA FASO"
,"BI"=>	"BURUNDI"
,"KH"=>	"CAMBODIA"
,"CM"=>	"CAMEROON"
,"CA"=>	"CANADA"
,"IC"=>	"CANARY ISLANDS, THE"
,"CV"=>	"CAPE VERDE"
,"KY"=>	"CAYMAN ISLANDS"
,"CF"=>	"CENTRAL AFRICAN REPUBLIC"
,"TD"=>	"CHAD"
,"CL"=>	"CHILE"
,"CN"=>	"CHINA, PEOPLE'S REPUBLIC"
,"CO"=>	"COLOMBIA"
,"KM"=>	"COMOROS"
,"CD"=>	"CONGO, THE DEMOCRATIC REPUBLIC OF"
,"CK"=>	"COOK ISLANDS"
,"CR"=>	"COSTA RICA"
,"IC"=>	"COTE D'IVOIRE"
,"HR"=>	"CROATIA"
,"CU"=>	"CUBA"
,"XC"=>	"CURACAO"
,"CY"=>	"CYPRUS"
,"CZ"=>	"CZECH REPUBLIC, THE"
,"DK"=>	"DENMARK"
,"DJ"=>	"DJIBOUTI"
,"DO"=>	"DOMINICAN REPUBLIC"
,"TL"=>	"EAST TIMOR"
,"EC"=>	"ECUADOR"
,"EG"=>	"EGYPT"
,"SV"=>	"EL SALVADOR"
,"ER"=>	"ERITREA"
,"EE"=>	"ESTONIA"
,"ET"=>	"ETHIOPIA"
,"FK"=>	"FALKLAND ISLANDS"
,"FO"=>	"FAROE ISLANDS"
,"FJ"=>	"FIJI"
,"FI"=>	"FINLAND"
,"FR"=>	"FRANCE"
,"GF"=>	"FRENCH GUIANA"
,"GA"=>	"GABON"
,"GM"=>	"GAMBIA"
,"GE"=>	"GEORGIA"
,"DE"=>	"GERMANY"
,"GH"=>	"GHANA"
,"GI"=>	"GIBRALTAR"
,"GR"=>	"GREECE"
,"GL"=>	"GREENLAND"
,"GP"=>	"GUADELOUPE"
,"GU"=>	"GUAM"
,"GT"=>	"GUATEMALA"
,"GG"=>	"GUERNSEY"
,"GN"=>	"GUINEA REPUBLIC"
,"GW"=>	"GUINEA-BISSAU"
,"GQ"=>	"GUINEA-EQUATORIAL"
,"HT"=>	"HAITI"
,"HN"=>	"HONDURAS"
,"HK"=>	"HONG KONG"
,"HU"=>	"HUNGARY"
,"IS"=>	"ICELAND"
,"IN"=>	"INDIA"
,"ID"=>	"INDONESIA"
,"IR"=>	"IRAN (ISLAMIC REPUBLIC OF)"
,"IQ"=>	"IRAQ"
,"IE"=>	"IRELAND, REPUBLIC OF"
,"IL"=>	"ISRAEL"
,"IT"=>	"ITALY"
,"JM"=>	"JAMAICA"
,"JP"=>	"JAPAN"
,"JE"=>	"JERSEY"
,"JO"=>	"JORDAN"
,"KZ"=>	"KAZAKHSTAN"
,"KE"=>	"KENYA"
,"KI"=>	"KIRIBATI"
,"KV"=>	"KOSOVO"
,"KW"=>	"KUWAIT"
,"KG"=>	"KYRGYZSTAN"
,"LA"=>	"LAO PEOPLE'S DEMOCRATIC REPUBLIC"
,"LV"=>	"LATVIA"
,"LB"=>	"LEBANON"
,"LS"=>	"LESOTHO"
,"LR"=>	"LIBERIA"
,"LY"=>	"LIBYA"
,"LI"=>	"LIECHTENSTEIN"
,"LT"=>	"LITHUANIA"
,"LU"=>	"LUXEMBOURG"
,"MO"=>	"MACAU"
,"MK"=>	"MACEDONIA, REPUBLIC OF"
,"MG"=>	"MADAGASCAR"
,"MW"=>	"MALAWI"
,"MY"=>	"MALAYSIA"
,"MV"=>	"MALDIVES"
,"ML"=>	"MALI"
,"MT"=>	"MALTA"
,"MH"=>	"MARSHALL ISLANDS"
,"MQ"=>	"MARTINIQUE"
,"MR"=>	"MAURITANIA"
,"MU"=>	"MAURITIUS"
,"YT"=>	"MAYOTTE"
,"MX"=>	"MEXICO"
,"FM"=>	"MICRONESIA, FEDERATED STATES OF"
,"MD"=>	"MOLDOVA, REPUBLIC OF"
,"MC"=>	"MONACO"
,"MN"=>	"MONGOLIA"
,"ME"=>	"MONTENEGRO, REPUBLIC OF"
,"MA"=>	"MOROCCO"
,"MZ"=>	"MOZAMBIQUE"
,"MM"=>	"MYANMAR"
,"NA"=>	"NAMIBIA"
,"NR"=>	"NAURU, REPUBLIC OF"
,"NP"=>	"NEPAL"
,"NL"=>	"NETHERLANDS, THE"
,"NC"=>	"NEW CALEDONIA"
,"NZ"=>	"NEW ZEALAND"
,"NI"=>	"NICARAGUA"
,"NE"=>	"NIGER"
,"NG"=>	"NIGERIA"
,"NU"=>	"NIUE"
,"NO"=>	"NORWAY"
,"OM"=>	"OMAN"
,"PK"=>	"PAKISTAN"
,"PW"=>	"PALAU"
,"PA"=>	"PANAMA"
,"PG"=>	"PAPUA NEW GUINEA"
,"PY"=>	"PARAGUAY"
,"PE"=>	"PERU"
,"PH"=>	"PHILIPPINES, THE"
,"PL"=>	"POLAND"
,"PT"=>	"PORTUGAL"
,"PR"=>	"PUERTO RICO"
,"QA"=>	"QATAR"
,"RE"=>	"REUNION, ISLAND OF"
,"RO"=>	"ROMANIA"
,"RW"=>	"RWANDA"
,"RU"=>"RUSSIAN FEDERATION"
,"MP"=>	"SAIPAN"
,"WS"=>	"SAMOA"
,"ST"=>	"SAO TOME AND PRINCIPE"
,"SA"=>	"SAUDI ARABIA"
,"SN"=>	"SENEGAL"
,"RS"=>	"SERBIA, REPUBLIC OF"
,"SC"=>	"SEYCHELLES"
,"SL"=>	"SIERRA LEONE"
,"SG"=>	"SINGAPORE"
,"SK"=>	"SLOVAKIA"
,"SI"=>	"SLOVENIA"
,"SB"=>	"SOLOMON ISLANDS"
,"SO"=>	"SOMALIA"
,"XS"=>	"SOMALILAND, REP OF (NORTH SOMALIA)"
,"ZA"=>	"SOUTH AFRICA"
,"ES"=>	"SPAIN"
,"LK"=>	"SRI LANKA"
,"XM"=>	"ST. MAARTEN"
,"SD"=>	"SUDAN"
,"SR"=>	"SURINAME"
,"SZ"=>	"SWAZILAND"
,"SE"=>	"SWEDEN"
,"CH"=>	"SWITZERLAND"
,"SY"=>	"SYRIA"
,"PF"=>	"TAHITI"
,"TW"=>	"TAIWAN"
,"TJ"=>	"TAJIKISTAN"
,"TZ"=>	"TANZANIA"
,"TH"=>	"THAILAND"
,"TG"=>	"TOGO"
,"TO"=>	"TONGA"
,"TT"=>	"TRINIDAD AND TOBAGO"
,"TN"=>	"TUNISIA"
,"TR"=>	"TURKEY"
,"TV"=>	"TUVALU"
,"UG"=>	"UGANDA"
,"KR"=> "SOUTH KOREA"
,"UA"=>	"UKRAINE"
,"AE"=>	"UNITED ARAB EMIRATES"
,"GB"=>	"UNITED KINGDOM (ENGLAND)"
,"US"=>	"UNITED STATES OF AMERICA"
,"UY"=>	"URUGUAY"
,"UZ"=>	"UZBEKISTAN"
,"VU"=>	"VANUATU"
,"VE"=>	"VENEZUELA"
,"VN"=>	"VIETNAM"
,"VG"=>	"VIRGIN ISLANDS (BRITISH)"
,"VI"=>	"VIRGIN ISLANDS (U.S.)"
,"YE"=>	"YEMEN, REPUBLIC OF"
,"ZM"=>	"ZAMBIA"
,"ZW"=>	"ZIMBABWE"
);


?>