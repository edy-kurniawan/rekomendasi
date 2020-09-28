<?php
$stop = false;
$centroid = array();
$groups = array();

$rows = $db->get_results("SELECT id_user FROM tb_user ORDER BY id_user LIMIT $jumlah");

$no = 1;
foreach($rows as $row){
    $centroid["M$no"] = $row->id_user;
    $no++;
}

function get_pusat_centroid($centroid = array(), $data = array()){
    $arr = array();
    foreach($centroid as $key => $val){
        $arr[$key] = $data[$val];
    }
    return $arr;
}

function get_jarak($row_data, $row_pusat_centroid){
    $result = 0;
    foreach($row_data as $key => $val){
        $result += pow($val - $row_pusat_centroid[$key], 2);
    }
    return sqrt($result);
}

function get_jarak_centroid($pusat_centroid = array(), $data = array()){
    $arr = array();

    foreach($data as $key => $val){
        foreach($pusat_centroid as $k => $v){
            $arr[$key][$k] = get_jarak($data[$key], $pusat_centroid[$k]);
        }
    }
    return $arr;
}

function get_keanggotaan($jarak_centroid = array()){
    $arr = array();
    foreach($jarak_centroid as $key => $val){
        $arr_min = array_keys($val, min($val));  
        if(count($arr_min)>1)      
            $arr_min = array_reverse($arr_min);
        $arr[$key] = $arr_min[0];
    }
    return $arr;
}


function get_pusat_centroid_baru($data, $keanggotaan){
    $arr = array();
    foreach($data as $key => $val){
        foreach($val as $k => $v){
            $arr[$keanggotaan[$key]][$k][]= $v;    
        }        
    }
    $pembagi = count($data);
    $result = array();
    foreach($arr as $key => $val){
        foreach($val as $k => $v){
            $result[$key][$k] = array_sum($v) / count($v);    
        }
    }
    return $result;
}
$pusat_centroid = get_pusat_centroid($centroid, $data); 
?>
<div id="hasil" class="panel panel-default">
    <div class="panel-heading">Hasil Perhitungan</div>
    <div class="panel-body">        
        <p>
            <button class="btn btn-primary" data-toggle="collapse" href="#perhitungan"><span class="glyphicon glyphicon-search"></span> Perhitungan</button>
        </p>
        <div id="perhitungan" class="collapse">                        
            <?php               
            $iterasi = 1;
            while(!$stop && $iterasi <= $maksimum):        
                $jarak_centroid = get_jarak_centroid($pusat_centroid, $data);     
                $keanggotaan = get_keanggotaan($jarak_centroid);                                                                                                             
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Iterasi <?=$iterasi?></h3>
                </div>
                <div class="panel-body">Pusat centroid</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead><tr>
                            <th>Nama</th>
                            <?php                         
                            foreach($KRITERIA as $key => $val):?>
                            <th><?=$val['nama']?></th>
                            <?php endforeach?>
                        </tr></thead>
                        <?php foreach($pusat_centroid as $key => $val):?>
                        <tr>
                            <td><?=$key?></td>                            
                            <?php foreach($val as $k => $v):?>
                            <td><?=round($v, 4)?></td>
                            <?php endforeach?>
                        </tr>
                        <?php endforeach?>
                    </table>
                </div>
                <div class="panel-body">Jarak Terhadap Pusat centroid</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead><tr>
                            <th>Nama</th>
                            <?php                         
                            foreach($KRITERIA as $key => $val):?>
                            <th><?=$val['nama']?></th>
                            <?php endforeach?>
                            <?php foreach($pusat_centroid as $key => $val):?>
                            <th><?=$key?></th>
                            <?php endforeach?>
                            <th>Group</th>
                        </tr></thead>
                        <?php foreach($jarak_centroid as $key => $val):?>
                        <tr>
                            <td><?=$ALTERNATIF[$key]['kode']?></td>
                            <?php foreach($data[$key] as $k => $v):?>
                            <td><?=$v?></td>
                            <?php endforeach?>
                            <?php foreach($val as $k => $v):?>
                            <td><?=round($v, 4)?></td>
                            <?php endforeach?>
                            <td><?=$keanggotaan[$key]?></td>
                        </tr>                    
                        <?php endforeach?>
                    </table>
                </div>
                
                <div class="panel-body">
                <?php
                if($iterasi==$maksimum && $groups != $keanggotaan){
                    $stop = true;
                    $ket = "Karena iterasi ($iterasi) sudah mencapai maksimum iterasi, maka iterasi dihentikan.";
                } else if($groups == $keanggotaan){
                    $stop = true; 
                    $ket = "Karena group baru (".implode(',', $keanggotaan).") = group sebelumnya (".implode(',', $groups)."), maka iterasi dihentikan.";   
                } else {
                    $iterasi++;
                    $ket = "Karena group baru (".implode(',', $keanggotaan).") <> group sebelumnya (".implode(',', $groups)."), maka iterasi dilanjutkan.";                    
                    $pusat_centroid = get_pusat_centroid_baru($data, $keanggotaan);                    
                    $groups = $keanggotaan;
                }    
                ?>
                <?=$ket?>
                </div>
            </div>
            <?php endwhile?>                
        </div>                                       
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">    
            <thead><tr>
                <td>Kode</td>
                <td>Nama</td>
                <td>centroid</td>
            </tr></thead>                
            <?php foreach($ALTERNATIF as $key => $val):?>
            <tr>
                <td><?=$val['kode']?></td>
                <td><?=$val['nama']?></td> 
                <td><?=$keanggotaan[$key]?></td>                        
            </tr>
            <?php endforeach?>
        </table>
    </div>
</div>
