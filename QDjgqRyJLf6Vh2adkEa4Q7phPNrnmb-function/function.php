<?php

require('connect.php');
function getData($tableName, $key, $columnKey, $column){
    include ('connect.php');
    $cases_query=$con->query("select * from $tableName where $columnKey='$key'");
    while($row=$cases_query->fetch_assoc()){
        $result=$row[$column];
    }
    return $result;
}
function getDataf2Key($tableName, $key, $columnKey,$key2, $columnKey2, $column){
    include ('connect.php');
    $cases_query=$con->query("select * from $tableName where $columnKey='$key' and $columnKey2='$key2'");
    while($row=$cases_query->fetch_assoc()){
        $result=$row[$column];
    }
    return $result;
}
function getFullColumnData($tableName,$columnName){
    $result=array();
    include('connect.php');
    $query=$con->query("select * from $tableName");
    while($row=$query->fetch_assoc()){
        array_push($result,$row[$columnName]);
    }
    return $result;
}
function updateData($tableName, $key, $columnKey, $input, $columnInput){
    include ('connect.php');
    $cases_query="update $tableName set $columnInput='$input' where $columnKey='$key'";
    if($con->query($cases_query)==TRUE){
        $result="data berhasil dimasukkan";
    } else {
        $result="Cek lagi ".$con->error;
    }
    $con->close();
    return $result;
}
function deleteData($tablename, $key, $columnKey){
    include ('connect.php');
    $sql="delete from ".$tablename." where ".$columnKey."='".$key."'";
    if($con->query($sql)===true){
        $result='data berhasil dihapus';
    }else{
        $result='Gagal : '.$con->error;
    }
    return $result;
}
function deleteArrayDataFromValue($array, $value){
    if(($key = array_search($value, $array) !== false)){
        unset($array[$key]);
    }
}
function dateSpellYear($date, $lang){
    $dateTime=new DateTime($date);
    $dateTransform=$dateTime;
    $dateTransform2=$dateTime;
    $dateTransform3=$dateTime;
    $bulan=$dateTransform->format('m');
    if($bulan<'10'){
        $bulan=$bulan[1];
    }
    $year=$dateTransform2->format('Y');
    $day=$dateTransform3->format('d');
    if($lang=='id'){
        switch ($bulan){
            case '1':
                $month_spell= "Januari";
                break;
            case '2':
                $month_spell= 'Februari';
                break;
            case '3':
                $month_spell= 'Maret';
                break;
            case '4':
                $month_spell= 'April';
                break;
            case '5':
                $month_spell= 'Mei';
                break;
            case '6':
                $month_spell= 'Juni';
                break;
            case '7':
                $month_spell= 'Juli';
                break;
            case '8':
                $month_spell= 'Agustus';
                break;
            case '9':
                $month_spell= 'September';
                break;
            case '10':
                $month_spell= 'Oktober';
                break;
            case '11':
                $month_spell= 'November';
                break;
            case '12':
                $month_spell= 'Desember';
                break;
            default:
                $month_spell= 'Tidak Terdaftar';
        }
    } else if($lang=='en'){
        switch ($bulan){
            case '1':
                $month_spell= 'January';
                break;
            case '2':
                $month_spell= 'February';
                break;
            case '3':
                $month_spell= 'March';
                break;
            case '4':
                $month_spell= 'April';
                break;
            case '5':
                $month_spell= 'May';
                break;
            case '6':
                $month_spell= 'June';
                break;
            case '7':
                $month_spell= 'July';
                break;
            case '8':
                $month_spell= 'August';
                break;
            case '9':
                $month_spell= 'September';
                break;
            case '10':
                $month_spell= 'October';
                break;
            case '11':
                $month_spell= 'November';
                break;
            case '12':
                $month_spell= 'December';
                break;
            default:
                $month_spell= 'Unregistered';
        }
    }
    if($lang=='id'){
        $result=$day." ".$month_spell." ".$year;
    } else if($lang=='en'){
        $result=$month_spell." ".$day." ".$year;
    }
    return $result;
}
function jurnalDataInsert($konten){
    include_once('connect.php');
    $randomid=time();
    $tanggal=date("Y-m-d");
    $sql="insert into jurnal (randomid, tanggal,konten) values ('$randomid', '$tanggal', '$konten')";
    if($con->query($sql)===TRUE){
        $result='data berhasil dimasukkan. ID : '.$randomid.' Konten : '.$konten;
    }else{
        $result='Error: '.$sql.'<br>'.$con->error;
    }
    $con->close();
    return $result;
}
function escapeAmpAt($input){
    $replace=str_replace("&","@", $input);
    return $replace;
}
function unescapeAtAmp($input){
    $replace=str_replace("@","&", $input);
    return $replace;
}
function escapeHTML($input){
    $replace=str_replace('&', '&amp;', $input);
    $replace=str_replace('"', '&quot;', $replace);
    $replace=str_replace("'", '&#039;', $replace);
    $replace=str_replace("<", '&lt;', $replace);
    $replace=str_replace(">", '&gt;', $replace);
    $replace=str_replace("#", '*', $replace);
    return $replace;
}
function unescapeHTML($input){
    $replace=str_replace('&amp;','&',  $input);
    $replace=str_replace('&quot;','"',  $replace);
    $replace=str_replace('&#039;',"'",  $replace);
    $replace=str_replace('&lt;',"<",  $replace);
    $replace=str_replace('&gt;',">",  $replace);
    $replace=str_replace('*',"#",  $replace);
    return $replace;
}
function arrayEncodeDelimiter($array){
    $result=implode("|",$array);
    return $result;
}
function arrayDecodeDelimiter($array){
    $result=explode("|",$array);
    return $result;
}
function dataInsert($tablename,$params, $values){
    include_once('connect.php');
    $unEscape=unescapeAtAmp($values);
    $sql="insert into $tablename $params values $unEscape";
    //echo $sql.'<br><br><br>';
    if($con->query($sql)===TRUE){
        $result='data berhasil dimasukkan';
    }else{
        $result='Error: '.$sql.'<br>'.$con->error;
    }
    $con->close();
    return $result;
}
function dataInsertDirectLink($tablename,$params, $values){
    include_once('connect.php');
    //$unEscape=unescapeAtAmp($values);
    $sql="insert into $tablename $params values $values";
    //echo $sql;
    //echo $con;
    //echo $sql.'<br><br><br>';
    
    //$con->query($sql);
    
    //echo "halo";
    
    if($con->query($sql)===TRUE){
        $result='data berhasil dimasukkan';
        echo $result;
    }else{
        $result='Error: '.$sql.'<br>'.$con->error;
        echo $result;
    }
    //$con->close();
    //echo $con;
    return $result;
}
function createTable($tablename, $tableparams){
    include_once('connect.php');
    $sql="create table $tablename ($tableparams)";
    if($con->query($sql)===TRUE){
        $result="Tabel $tablename berhasil dibuat";
    }else{
        $result='Error: '.$sql.'<br>'.$con->error;
    }
    //$con->close();
    return $result;
}
function redirectJR($booleanValue){
    if($booleanValue){
        Header('Location : https://jonathanraditya.com');
        exit;
    }
}
function redirectAcara($booleanValue){
    if($booleanValue){
        Header('Location : https://jonathanraditya.com/acara');
        exit;
    }
}
function currentDateTime(){
    $dateTime=date("Y-m-d H:i:s");
    return $dateTime;
}
function curlWebExec($webLink){
    //Create curl resource
    $ch=curl_init();
    //set url
    curl_setopt($ch, CURLOPT_URL, $webLink);
    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //Output contains the output string
    $output=curl_exec($ch);
    return $output;
    //close curl resource to free up system resource
    curl_close($ch);
}
function webEscapeSpaces($webLink){
    $url = str_replace(" ", '%20', $webLink);
    return $url;
}

function randomNameGenerator(){
    $namedata=['Winter Chinook','Topaz Panda','Ivory Insect','Preying Beetle','Maroon-Striped Hartebeest','Ocean-Tailed Mayfly','Neanderthal-Starfish','Eland-Caterpillar','Cheetah-Shark','Chicken-Stingray','Stellers Platypus','Short-Eared Zebu','Bedlington Birman','Elegant Kookaburra','Straw-Eyed Cow','Ginger-Nosed Coral','Oedemera-Anemone Fish','Indri-Aardvark','Cuttlefish-Ladybird','Binturong-Elk','Topaz Donkey','Steel blue Bunny','Tasmanian Tetra','Swamp Platypus','Redwood-Dotted Ferret','Timberwolf-Eyed Kid','Capybara-Bullfrog','Spanish Flag-Yak','Ibex-Ladybird','Ibex-Guppy','Chestnut Antelope','Crab-Eating Porcupine','Sandy Earwig','Muddy Anole','Pastel-Scaled Gerbil','Scarlet-Scaled Doe','Affenpinscher-Akita','Human-Anteater','Kookaburra-Rabbit','Hartebeest-Crow','Wine Camel','Boykin Tayra','Flat-Tailed Eel','Soldier Frog','Cream-Bellied Reptile','Citron-Eyed Woodlouse','Deer-Molly','Ladybird-Oedemera','Trunkfish-Civet','Cayman-Parakeet','Spectacled Cuttlefish','Alpine Cardinal','Yellow Nightingale','Saint Buffalo','Bright-Bellied Kitten','Ochre-Eared Bear','Sponge-Wrasse','Mandrill-Finch','Labradoodle-Eagle','Locust-Gazelle','ntler Mole','Mantis Markhor','Crowned Gila monster','Grizzly Caribou','Stormcloud-Eyed Finch','Beige-Eyed Cat','Lizard-Anteater','Dog-Tropicbird','Ox-Rhinoceros','Hornet-Booby','Cherry Pronghorn','Chinstrap Monkey','Collared Akbash','Water Frigatebird','Umber-Furred Elephant','Snow-Striped Muskrat','Anteater-Tang','Dachshund-Gila monster','Armadillo-Gila monster','Anteater-Burmese','Ebony Bull Terrier','Cairn Ape','Blizzard Pekingese','Hooting Parrot','Sunset-Tailed Shrimp','Violet-Blue-Dotted Lion','Pug-Gopher','Lemming-Hedgehog','Saola-Raven','Bird-Orca','Storm Egret','Herbivorous Lechwe','Charcoal Triggerfish','Brass Dolphin','Cyan-Eared Grasshopper','Tulip-Dotted Gar','Chimpanzee-Kookaburra','Kakapo-Dolphin','Horse-Mantis','Abyssinian-Bobcat','Orchid Mule','Dwarf Guinea Pig','Painted Slug','Wisteria Emu','Titanium-Scaled Starling','Quartz-Scaled Turtle','Hippopotamus-Somali','Dolphin-Stoat','Springbok-Egret','Giraffe-Crab','White Ostrich','Exalted Parakeet','Livid Darter','Tasmanian Panther','Tangelo-Tailed Buffalo','Ultramarine-Striped Argali','Wombat-Goat','Ground hog-Hedgehog','Toad-Lion','Tarsier-Eagle','Bichon Himalayan','Ivory Shrew','Dwarf Lovebird','Basenji Moth','Sea blue-Dotted Quail','Wheat-Eyed Wallaby','Lizard-Labradoodle','Katydid-Llama','Molly-Booby','Cassowary-Komodo','Ebony Boa','Flat-Tailed Bull','Hairy Gharial','Xanadu Snail','Viridian-Furred Lion','Pistachio-Dotted Capuchin','Ant-Guinea pig','Nightingale-Kiwi','Duck-Bee','Nightingale-Uakari'];
    $random_key=array_rand($namedata);
    return $namedata[$random_key];
}

function L7PrCawbEEuntukito_insertdata($values){
    include_once('connect.php');
    $tablename='L7PrCawbEE_untukito';
    $params="(uniqueid, nama, pesan, publicstatuscode)";
    $sql="insert into $tablename $params values $concatenate";
    if($con->query($sql)===TRUE){
        $result='data berhasil dimasukkan';
    }else{
        $result='Error: '.$sql.'<br>'.$con->error;
    }
    $con->close();
    return $result;
    
}
















?>