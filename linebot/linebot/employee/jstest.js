window.onload = function(){
//lineUserRelation
var index = 0;
$('#employee_select').change(function(){
  index = $('#employee_select option:selected').val();
  $.ajax({
         type: 'POST',
         url: "../employee/employee.php",
         data: {select:index},
         success: function(result) {
           var values = JSON.parse(result);
           $('#select_name').text(values['name']);
           $('#select_company').text(values['company_name']);
           $('#select_department').text(values['department_name']);
           $('#select_store').text(values['store_name']);
           $('#select_employment').text(values['employment_type']);
           $('#select_join').text(values['join']);
          (values['manager'] == 0)? $('#select_manager').text('なし') : $('#select_manager').text('あり');
        },
         error: function () {
            console.log('Ajax error');
        }
  });
});

//Employee Search & Management
$('#manager_status').val($('.company_select option:selected').val()); //회사 매니저권한
var cn = new Array();
//옵션의 모든 클래스명주기
$('.affiliation').children('option').each(function(i,e){
  if ($.inArray($(this).attr('class'),cn) == -1) { //cn배열안에 값이 없다면 저장
    cn[i] = $(this).attr('class');
  }

  var index = $('.company_select option:selected').val(); //선택된옵션밸류
  $('.affiliation'+index).css('display','block'); //선택된 회사의 부서만 보이게
    for (var i = 0; i < cn.length; i++) {
        if (cn[i] != 'affiliation'+index) {  //선택된 부서와 다른 class명일 때 숨김
          $('.'+cn[i]).css('display','none');
        }
    }
});

//회사옵션선택
$('.company_select').change(function(){
  var index = $('.company_select option:selected').val(); //선택된옵션밸류
  $('.affiliation'+index).css('display','block'); //선택된 회사의 부서만 보이게
  $('.affiliation_default').prop('selected',true);
    for (var i = 0; i < cn.length; i++) {
        if (cn[i] != 'affiliation'+index) {  //선택된 부서와 다른 class명일 때 숨김
          $('.'+cn[i]).css('display','none');
        }
    }
    $('#manager_status').val(index);
    $('.affiliation_default').prop('selected',true);
});

//이름옵션선택
var cn2 = new Array();
//옵션의 모든 클래스명주기
$('.employee_name').children('option').each(function(i,e){
  if ($.inArray($(this).attr('class'),cn2) == -1) { //cn배열안에 값이 없다면 저장
    cn2[i] = $(this).attr('class');
  }
  var index2 = $('.affiliation_select option:selected').val(); //선택된옵션밸류
  $('.employee_name'+index2).css('display','block'); //선택된 회사의 부서만 보이게
    for (var i = 0; i < cn2.length; i++) {
        if (cn2[i] != 'employee_name'+index2) {  //선택된 부서와 다른 class명일 때 숨김
          $('.'+cn2[i]).css('display','none');
        }
    }
});
$('.affiliation_select').change(function(){
  var index2 = $('.affiliation_select option:selected').val(); //선택된옵션밸류
  $('.employee_name'+index2).css('display','block'); //선택된 회사의 부서만 보이게
  $('.employee_name_default').prop('selected',true);
    for (var i = 0; i < cn2.length; i++) {
        if (cn2[i] != 'employee_name'+index2) {  //선택된 부서와 다른 class명일 때 숨김
          $('.'+cn2[i]).css('display','none');
        }
    }
    // $('#manager_status').val(index);
    // $('.employee_name_default').prop('selected',true);
});


//Company Management
var classNum = 0;
function addInput(type){
  if (type == 'department_cnt') {
    classNum = $('#d_plus').data('cnt');
    classNum++;
    $('#department').append(
        $('<input/>',
        { name: 'company[department][insert][]',
          class: 'department'+classNum
     }));
     $('#d_plus').data('cnt',classNum);
  }else {
    classNum = $('#s_plus').data('cnt');
    classNum++;
    $('#store').append(
        $('<input/>',
        { name: 'company[store][insert][]',
          class: 'store'+classNum
     }));
     $('#s_plus').data('cnt',classNum);
  }
}

function delInput(type){
  if (type == 'department_cnt') {
    classNum = $('#d_plus').data('cnt');
    if(classNum >= 0){
            $('.department'+classNum).remove();
            classNum--;
            $('#d_plus').data('cnt',classNum);
    }
  }else {
    classNum = $('#s_plus').data('cnt');
    if(classNum >= 0){
            $('.store'+classNum).remove();
            classNum--;
            $('#s_plus').data('cnt',classNum);
        }
  }
}

//DatePicker
$(function(){
    $('.datePicker').datepicker({
      showOn: 'both',
      prevText: '前月',
      nextText: '来月',
      monthNames: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
      dayNamesMin: ['月','火','水','木','金','土','日'],
      dateFormat: "yy/mm/dd"
    });
});

var currentYear = (new Date()).getFullYear();
$('.monthPicker').MonthPicker({
  MonthFormat:'yy/mm',
  StartYear: currentYear
});

//datetimePicker
  $(function(){
      $('.datetimePicker').datetimepicker({
        changeMonth:true,
        changeYear:true,
        showMonthAfterYear:true,
        showOn: 'both',
        prevText: '前月',
        nextText: '来月',
        monthNamesShort: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
        dayNamesMin: ['月','火','水','木','金','土','日'],
        dateFormat: "yy/mm/dd",
        // timepicker設定
        timeFormat:'HH:mm:ss',
        controlType:'select',
        oneLine:true
      });
  });


//Incomplete schedule
$(".name").each(function incomplete(){
  var rows = $(".name:contains('"+ $(this).text() +"')"); //class nameを持ってるtrのtext値取得
  if (rows.length > 1) {
    rows.eq(0).attr("rowspan", rows.length);//tr中で一番目trにrowspan追加
    rows.not(":eq(0)").remove();//他のtrは削除
  }
});

var mNum=0;
$('.incomplete_modify').click(function(){
  var id = $(this).data('id');
  var type = $('#type'+id).data('type');
  var datetime = $('#datetime'+id).text();

  if (mNum==0) {
    $('#modify_select'+id).val(type);
    $('#modify_datetime'+id).val(datetime);
    $('#modify_form'+id).slideDown();
    $(this).val('閉じる');
    mNum++;
  }else {
    $('#modify_form'+id).slideUp();
    $(this).val('修正');
    mNum--;
  }
});


$('.incomplete_add').change(function(){
  var id = $(this).val();
  var type = $('#type'+id).data('type');
  var datetime = $('#datetime'+id).text();
  if($('.incomplete_add').is(':checked')){
    $('#modify_select'+id).val(0);
    $('#modify_datetime'+id).val('');
    $('#modify_id'+id).val('');
  }else {
    $('#modify_select'+id).val(type);
    $('#modify_datetime'+id).val(datetime);
    $('#modify_id'+id).val(id);
  }
});

function delete_check() {
  if (confirm('消しますか？')) {
    return true;
  }else {
    return false;
  }
}

//Company Search
function delecteCompany(id,num){
  var company = $('#company_name'+num).text();
  if (confirm(company+'を消しますか？')) {
    $.ajax({
      type: 'POST',
      url:'../company/company.php',
      data: {delcompany:id},
      success: function(result){
        $('#company'+num).remove();
      },
      error: function(){
        alert("処理中エラーが発生しました。");
      }
    });
  }
}

//Employee Search
function delecteEmployee(id,num){
  var employee = $('#employee_name'+num).text();
  if (confirm(employee+'を消しますか？')) {
    $.ajax({
      type: 'POST',
      url:'../employee/employee.php',
      data: {delemployee:id},
      success: function(result){
        $('#employee'+num).remove();
        alert(result);
      },
      error: function(){
        alert("処理中エラーが発生しました。");
      }
    });
  }
}

//Schedule Search
  function delecteSchedule(id,num){
    $.ajax({
      type: 'POST',
      url:'../employee/employee.php',
      data: {delSchedule:id},
      success: function(result){
        $('#schedule'+num).remove();
        alert(result);
      },
      error: function(){
        alert("処理中エラーが発生しました。");
      }
    });
  }
}
