<?php

$j = file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . 'settings.json' );
$data = json_decode($j, true);
$url = $data['client_endpoint'];
$domen = substr_replace($url,'',-6);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
   a {
    color: blue; /* Цвет ссылок */
    text-decoration: none; /* Убираем подчёркивание */
   }
   a:hover {
    border-bottom: 1px dashed blue; /* Добавляем синее пунктирное подчёркивание */
   }
  </style>
</head>
<body>
    <!-- <h1>View control!</h1> -->
<? if($foundDublicates) { ?>
    
    <h1 style="color: red;">Обнаружено дублирование отделов!</h1>
    <?
    $branch = $foundDublicates[0]['BRANCH'];
    $nameBranch = $departments["$branch"]['NAME'];
    ?>
    <h2>Подразделение - <?= $nameBranch?></h2>
    <? foreach ($foundDublicates as $value) { ?>
        <?  $newBranch = $departments[$value['BRANCH']]['NAME'];
            $manager;
            if (array_key_exists('UF_HEAD', $value) && $value['UF_HEAD'] != 0) {
                $manager = $users[$value['UF_HEAD']]['NAME']." ".$users[$value['UF_HEAD']]['LAST_NAME'];
            } else{
                $manager = "отсутствует";
            }
         if ($nameBranch != $newBranch) : 
             $nameBranch =  $newBranch;?>
            <h2>Подразделение - <?= $nameBranch?></h2>
        <? endif ?>
                
        <div> 
        <h3><a href=<?=$domen.'/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT='.$value['ID']?>
         target="_blank" rel="noopener noreferrer"><span style="color: OrangeRed"><?=$value['PATH']?></span></a> 
         (руководитель отдела -  <?
            if (array_key_exists('UF_HEAD', $value) && $value['UF_HEAD'] != 0) { ?>
                <a href=<?=$domen.'/company/personal/user/'.$value['UF_HEAD'].'/'?>
         target="_blank" rel="noopener noreferrer"><?=$manager?></a>
            <?} else echo $manager?>)</h3> <br />
         <? if (array_key_exists('UF_HEAD', $value) && $value['UF_HEAD'] != 0) :
                $manager = $users[$value['UF_HEAD']]['NAME']." ".$users[$value['UF_HEAD']]['LAST_NAME'];
                $userDep = $users[$value['UF_HEAD']]['UF_DEPARTMENT'];
                $userWork = $users[$value['UF_HEAD']]['WORK_POSITION'];
         ?>
         <b><?=$manager?></b> &nbsp; &mdash; &nbsp; <?=$userWork ?> <br />
         Отдел, в котором числится сотрудник:<br />
           <? foreach ($userDep as $dep) { ?>
            <a href=<?=$domen.'/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT='.$dep?>
            target="_blank" rel="noopener noreferrer"><?=$departments[$dep]['NAME']?></a> <span> ( <?=$departments[$dep]['PATH']?> ) </span> <br />
         <?}?> 
         <br />
         <? endif ?>
         
        </div>
    <? } ?>
    
    
    <?php } else {?>
        <h1 style="color: green;">Отлично! <br />
            Дублирование отделов не обнаружено.
        </h1>
        <?php } ?>

<!-- Отображаем отделы и сотрудников, которые являются руководителем, но не числятся в том отделе где они являются руководителем. -->

<? if($notResident) { ?>
    
    <h1 style="color: MediumVioletRed;">Обнаружены не состоящие в отделе руководители!</h1>

    <? foreach($notResident as $dep) { 
        $user = $users[$dep['UF_HEAD']];
        ?>
    <h3><a href=<?=$domen.'/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT='.$dep['ID']?>
         target="_blank" rel="noopener noreferrer"><span style="color: MediumVioletRed"><?=$dep['PATH']?></span></a> 
         <a href=<?=$domen.'/company/personal/user/'.$user['ID'].'/' ?>
         target="_blank" rel="noopener noreferrer"> &nbsp; &mdash; &nbsp; <?=$user['NAME']. ' '. $user['LAST_NAME'] ?></a></h3>
         Отдел, в котором числится сотрудник:<br />
           <? foreach ($user['UF_DEPARTMENT'] as $userDep) { ?>
            <a href=<?=$domen.'/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT='.$userDep?>
            target="_blank" rel="noopener noreferrer"><?=$departments[$userDep]['NAME']?></a> <span> ( <?=$departments[$userDep]['PATH']?> ) </span> <br />
         <?}?> 
         <? 
        } ?>

<? } ?>
</body>
</html>
