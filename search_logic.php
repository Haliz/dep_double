<?php
require_once (__DIR__.'/crest.php');

require_once(__DIR__.'/step_request.php');


// pr('Контроль работы') ;


function getList($metod, $param = []) 
{
    $total = stepRequest($metod)["total"];
    $listCount = (int) ($total / 50 + 1);
    $set = array();
    $currentSheet = 0;
    $res = array();
    for($i=0; $i<$listCount; $i++) {
        $param1 = ['start'=>($i*50)];
        $param = array_merge($param, $param1);
        $set["list_".$i] = "$metod?".http_build_query($param); // TODO Здесь нужен $param Сделано.(проверить массив)
        $currentSheet ++;
        if(count($set) == 50 || $currentSheet == $listCount) {
         array_push($res,
         stepRequest('batch',
              array(
                'halt' => 0,
                'cmd'=> $set
                )
              )
          );
          $set = array();
        }
    }
    $rsl=array();
    foreach ($res[0]['result']['result'] as $get) {
        $rsl = array_merge($rsl, $get);
      }
    return $rsl;
}


function pr($o)
{
    $bt =  debug_backtrace();
    $bt = $bt[0];
    $dRoot = $_SERVER["DOCUMENT_ROOT"];
    $dRoot = str_replace("/","\\",$dRoot);
    $bt["file"] = str_replace($dRoot,"",$bt["file"]);
    $dRoot = str_replace("\\","/",$dRoot);
    $bt["file"] = str_replace($dRoot,"",$bt["file"]);
    ?>
    <div style='font-size:9pt; color:#000; background:#fff; border:1px dashed #000;'>
    <div style='padding:3px 5px; background:#99CCFF; font-weight:bold;'>File: <?=$bt["file"]?> [<?=$bt["line"]?>]</div>
    <pre style='padding:10px; color: black'><?print_r($o)?></pre>
    </div>
    <?
}

function byID($items)
{
  $strItems = array();
  foreach($items as $item) {
    $strItems[$item['ID']] = $item;
  }
  return $strItems;
}

function defineBranch($department, $departments) 
{      
    if ($department['PARENT']>1) {
      $department = defineBranch ($departments[$department['PARENT']], $departments);
    }  
  return $department;
}

function writeBranch($departments) 
{
  foreach($departments as $department) {
    if(@$department['PARENT']>1) {
        $res = defineBranch($department, $departments) ;
        $department['BRANCH'] = $res['ID'];
        $departments[$department['ID']] = $department;
    }
  }
 
return $departments;
}

function writePath($departments) 
{
   foreach($departments as $department) {
    
    $path = $department['NAME'];
    
    if(@$department['PARENT']>1) {
      $parentID = $department['PARENT'];
        while($parentID>1) {
        $path = $departments[$parentID]['NAME'].' / '. $path;
        $parentID = $departments[$parentID] ['PARENT'];
      }
        $department['PATH'] = $path;
        $departments[$department['ID']] = $department;
    } else {
      $department['PATH'] = $department['NAME'];
      $departments[$department['ID']] = $department;
    }
  }
 return $departments;
}

function searchDublicate($departments)
{
  $rsl = array();
  foreach($departments as $department) {
    foreach($departments as $checkDepartment) {
      if($department['NAME'] == $checkDepartment['NAME'] 
      && @$department['BRANCH'] == @$checkDepartment['BRANCH']
      && $department['ID'] != $checkDepartment['ID']) {
        $rsl[$department['ID']] = $department;
      }
    }
  }
  usort($rsl, function ($a, $b) {
    return $a['PATH'] <=> $b['PATH'];
  });
  return $rsl;
}
// Возвращает департамент где руководитель не резидент тдела.
function searchDepManagerNotResident($departments,$users)
{
  $rsl = array();
  foreach($departments as $department) {
    if (array_key_exists('UF_HEAD', $department) && $department['UF_HEAD'] != 0) {
      $user=$users[$department['UF_HEAD']];
      // pr($user);
      $check = false;
      foreach ($user['UF_DEPARTMENT'] as $value) {
        
        if ($department['ID'] == $value) $check = true;
      }
      if($check == false) $rsl[$department['ID']] = $department;
    }
  }
  return $rsl;
}


$users = getList('user.get', ['USER_TYPE' => 'employee', 'ACTIVE' => 'true']);
$users = byID($users);
// pr($users);

$departments = getList('department.get', ['sort' => 'ID', 'order' => 'ASC' ]);
$departments = byID($departments);
$departments = writeBranch($departments);
$departments = writePath($departments);
//  pr($departments);
$foundDublicates = searchDublicate($departments);
// pr($foundDublicates);

$notResident = searchDepManagerNotResident($departments,$users);
// pr($notResident);



