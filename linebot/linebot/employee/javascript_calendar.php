<?require_once('../require/header.php');?>
<script type="text/javascript">
    var today = new Date();
    var year = today.getFullYear();
    var month = today.getMonth();
    var day = today.getDay();
    month += 1;

    //현재 달의 일수를 구한다.(윤년포함)
    function days(year, month){
        switch(month){
            case 1: case 3: case 5: case 7: case 8: case 10: case 12:
            return 31;
            case 4: case 6: case 9: case 11:
            return 30;
            case 2:
            if((year%400)==0||(year%4)==0&&(year%100)!=0){
                return 29;
            }else{
                return 28;
            }
        }
    }
    //이전 달로 되돌리는 함수
    function prevmonth(){
        var ymda = document.getElementById("prev");
        var yg = document.getElementById("Ymd");
        //한 달 내림
        month -= 1;
        if(month < 1){
            month = 12;
            year -= 1;
        }
        if(year < 2010){
            alert("情報がありません。");
            month += 1;
            if(month > 12){
                month = 1;
                year += 1;
            }
        }
        var ymda = year + "年" + (month)+"月";
        //초기화 함수
        present();
    }
    //다음 달로 가는 함수
    function nextmonth(){
        var ymda = document.getElementById("next");
        var yg = document.getElementById("Ymd");
        //한 달 올림
        month += 1;
        if(month > 12){
            month = 1;
            year += 1;
        }
        var ymda = year + "年" + month + "月";
        //초기화 함수
        present();
    }
    //초기화
    function present(){
        var start = new Date(year, month-1 ,1);
        var ymda = document.getElementById("Ymd");
        var tab = document.getElementById("tab");
        //모르는 변수 선언
        var row = null;
        var cnt = 0;
        //연월
        var ym = year + "年" + (month) + "月";
        ymda.innerHTML = ym;
        //테이블 행의 길이가 2개면 1개를 제거함
        while(tab.rows.length > 2){
            tab.deleteRow(tab.rows.length -1);
        }
        row = tab.insertRow();
        //달력 시작 일 구함
        for(var j = 0; j < start.getDay(); j++){
            cell = row.insertCell();
            cnt += 1;
        }
        //달력의 일 수 만큼
        var k;
        for(var i = 0; i < days(year, month); i++){
            cell = row.insertCell();
            cell.innerHTML = i+1+"<?="日"?>";
            cnt += 1;
            if(cnt%7 == 0){
                row = tab.insertRow();
            }
            if(i==0){
                document.write(i+1+":改行");
                var test1 = i+1;

            }
            var test2 = new Array();
            var test3 = new Array();
            if(cnt%7 == 0){
                document.write(i+1+":改行");
                document.write(i+2+":改行");
                k += 1;
                test2[k] = i+1;
                k += 1;
                test3[k] = i+2;
            }
            if(i+1 == days(year, month)){
                document.write(i+1+"最後");
                var test4 = i+1+"最後";
            }
        }
    }
</script>
<style media="screen">
    #tab {
        border: solid 1px;
        margin:auto;
    }
</style>
<?php
    $test = "<script>var test4='111'; document.write(test4);</script>";
    echo $test;
?>
<table id="tab">
    <tr>
        <td id="prev"><label onclick="prevmonth()"><</label></td>
        <td id="Ymd" colspan="5"></td>
        <td id="next"><label onclick="nextmonth()">></label> </td>
    </tr>
    <tr>
        <td>日</td>
        <td>月</td>
        <td>火</td>
        <td>水</td>
        <td>木</td>
        <td>金</td>
        <td>土</td>
    </tr>
    </table>

        <script type="text/javascript">
            present();
        </script>
<?require_once('require/footer.php');?>
