<?php 


session_start();

# CONSTANTS
if ( !defined( 'ABSPATH' )) {
    define( 'ABSPATH', __DIR__ . '/' );
}

if ( !defined( 'RULETABLE' )) {
    define( 'RULETABLE', 'redirect_rule');
}

if ( !defined( 'COOKIETABLE' )) {
    define( 'COOKIETABLE', 'cookie_id');
}

# SET SERVER PROTOCOL
$protocol = $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';

# DB, user & pass
$db_name = 'u1087935_skpbz';
$username = 'u1087935_TsZeKa2eauMwDa4QpaMGgqYA956a7h';
// $username = 'root';
$password = '848HMbVhJKfKqZDyz3P4fS6cDB5qgP';
// $password = '';
$length = 5;
date_default_timezone_set('Asia/Jakarta');

# PHP Petname
require ABSPATH . 'vendor/autoload.php';

use LCherone\PHPPetname as Petname;

# DB CONNECTION
$db_connection = mysqli_connect('localhost', $username, $password);

if ( !$db_connection ) {
    echo 'Unable to connect';
} 

# CREATE TABLE & DATABASE IF DOESN'T EXISTS
$query = "create database if not exists $db_name";
$result =  $db_connection -> query($query);
$db_selection_status = mysqli_select_db($db_connection, $db_name);

$query_ruletable = "create table if not exists redirect_rule (
    id int(11) auto_increment primary key,
    creation_date varchar(100) default current_timestamp,
    short_url varchar(1000),
    original_url varchar(1000),
    hit int(11) default 0,
    cookie_id varchar(100),
    last_hit varchar(100)
    )";
$result = $db_connection -> query($query_ruletable);

$query_cookietable = "create table if not exists cookie_id (
    no int(11) auto_increment primary key,
    first_access varchar(1000) default current_timestamp,
    ip varchar(100),
    id varchar(1000),
    friendly_id varchar(1000),
    last_entry varchar(1000),
    entries int(11) default 0
    )";
$result = $db_connection -> query($query_cookietable);

# FUNCTIONS
function friendlyId() {
    return Petname::Generate(2) . "-". rand(10000,99999);
}

function currentDateTime() {
    return date('Y-m-d H:i:s');
}

function getData( $db_connection, $table_name, $key, $column_key, $column ) {
    $result = NULL;
    $query = $db_connection -> query("select * from $table_name where $column_key='$key'");
    while ($row = $query -> fetch_assoc() ) {
        $result = $row[$column];
    }
    return $result;
}

function insertData( $db_connection, $table_name, $columns, $values ) {
    $query = "insert into $table_name $columns values $values";
    if ( $db_connection -> query($query) === TRUE ) {
        $result = TRUE;
    } else {
        $result = FALSE;
    }
    
    return $result;
}

function updateData( $db_connection, $table_name, $key, $column_key, $input, $column_input ) {
    $query = "update $table_name set $column_input='$input' where $column_key='$key'";
    if ( $db_connection -> query($query) === TRUE ) {
        $result = TRUE;
    } else {
        $result = FALSE;
    }
    return $result;
}

function randomLowercaseChars( $length ) {
    $s = substr(str_shuffle(str_repeat("abcdefghjkmnpqrstuvwxyz", 10)), 0, $length);
    return $s;
}

function fixUrl( $url ) {
    $url = strpos($url, 'http') !== 0 ? "https://$url" : $url;
    return $url;
}

// function validateUrl( $url ) {
//     stream_context_set_default(
//         array('http' => array('method' => 'HEAD'))
//     );
//     if ( filter_var($url, FILTER_VALIDATE_URL) ) {
//         $headers = @get_headers($url);
//         if ( !$headers || strpos($headers[0], '404' ) ){
//             return FALSE;
//         } else {
//             return TRUE;
//         }
//     } else {
//         return FALSE;
//     }
// }

function validateUrl( $url ) {
    if ( filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED && FILTER_FLAG_HOST_REQUIRED) ) {
        return TRUE;
    } else {
        return FALSE;
        
    }
}

function shortLinkHitIncrement( $db_connection, $table_name, $short_redirect_url) {
    # Get Data
    $column_key = 'short_url';
    $column = 'hit';
    $previous_hit = getData($db_connection, $table_name, $short_redirect_url, $column_key, $column);
    
    # Update data
    $current_hit = intval($previous_hit) + 1;
    $current_datetime = currentDateTime();
    $current_datetime_column = 'last_Hit';
    $result = updateData( $db_connection, $table_name, $short_redirect_url, $column_key, $current_hit, $column);
    $result = updateData( $db_connection, $table_name, $short_redirect_url, $column_key, $current_datetime, $current_datetime_column);
    return $result;
}

function entriesIncrement( $db_connection, $table_name, $id, $increment_value=1 ) {
    # Get Data
    $column_key = 'id';
    $column = 'entries';
    $previous_entry = getData($db_connection, $table_name, $id, $column_key, $column);
    
    # Update data
    $current_entries = intval($previous_entry) + $increment_value;
    $current_datetime = currentDateTime();
    $current_datetime_column = 'last_entry';
    $result = updateData( $db_connection, $table_name, $id, $column_key, $current_entries, $column);
    $result = updateData( $db_connection, $table_name, $id, $column_key, $current_datetime, $current_datetime_column);
    return $result;
}

function limChar($char, $lim, $direction=1) {
    if ( $direction > 0 ) {
        if (strlen($char) > $lim) {
            return substr($char, 0, $lim) . '...';
        } else {
            return $char;
        }
    } else if ( $direction < 0 ) {
        if (strlen($char) > $lim) {
            return '...'.substr($char, $lim*$direction);
        } else {
            return $char;
        }
    }
    
}

# NETWORK CONTROLLER
$url = parse_url($_SERVER['REQUEST_URI']);
$path = explode('/', $url['path']);
$short_redirect_url = $path[1];

for ( $i=0; $i < count($path); $i++ ) {
    $current_folder = $path[$i];
    $short_redirect_url = preg_match("/$current_folder/", ABSPATH) ? '' : $current_folder;
}

$key = $short_redirect_url;
$column_key = 'short_url';
$column = 'original_url';
$original_url = getData($db_connection, RULETABLE, $key, $column_key, $column);

if ( $original_url != NULL ) {
    $update_hit_result = shortLinkHitIncrement($db_connection, RULETABLE, $short_redirect_url);
    # Do something with $update_hit_result
    header("Location: $original_url");
    exit();
}

# CLEAR POST VARIABLES
if ( !empty($_POST['clear_status']) && $_POST['clear_status'] === '1' ) {
    unset($_POST);
}

# CREATE COOKIE (TEMPORARY ID)
$cookie_name_hashed = hash('sha256', 'id');

# Find pattern for cookie install section
# NEEDED: CHECK FOR COOKIE ID VALIDITY!
$id_e = explode('$', $url['path']);
if ( !empty($id_e[1]) ) {
    # Install cookie
    $cookie_value_hashed = $id_e[1];
    $cookie_value = getData($db_connection, COOKIETABLE, $cookie_value_hashed, 'id', 'friendly_id');
    $cookie_expiration = time() + 86400 * 365 * 5; # 5 years expiration
    setcookie($cookie_name_hashed, $cookie_value_hashed, $cookie_expiration, '/');

    # Remove last part of url and make it a redirect location
    $path_redirect = $path;
    unset($path_redirect[ count($path_redirect) - 1 ]);
    $header_location = $protocol.'://'.$_SERVER['HTTP_HOST'].join('/', $path_redirect);
    header("Location: $header_location");
    exit();

} else if ( !isset($_COOKIE[$cookie_name_hashed]) ) {
    # Set value
    $cookie_value = friendlyId();
    $cookie_value_hashed = hash('sha256', $cookie_value);
    $cookie_expiration = time() + 86400 * 365 * 5; # 5 years expiration
    setcookie($cookie_name_hashed, $cookie_value_hashed, $cookie_expiration, '/');
    
    # Store to db
    $ip = $_SERVER['REMOTE_ADDR'];
    $current_datetime = currentDateTime();
    $columns = "(ip, id, friendly_id, last_entry)";
    $values = "('$ip', '$cookie_value_hashed', '$cookie_value', '$current_datetime')";
    $result = insertData($db_connection, COOKIETABLE, $columns, $values);
} else if ( isset($_COOKIE[$cookie_name_hashed]) ) {
    # Fetch id and friendly name
    $cookie_value_hashed = $_COOKIE[$cookie_name_hashed];
    $cookie_value = getData($db_connection, COOKIETABLE, $cookie_value_hashed, 'id', 'friendly_id');
}

# SHORTEN LINK
$insert_data_result = NULL;
if ( !empty($_POST['input_url']) ) {
    $input_url = fixUrl($_POST['input_url']);
    $valid_input_url = validateUrl($input_url);
    if ( $valid_input_url ) {
        $key = $input_url;
        $column_key = 'original_url';
        $column = 'short_url';
        $current_datetime = currentDateTime();
        $current_datetime_column = 'creation_date';
        // $shortened_link = getData($db_connection, RULETABLE, $key, $column_key, $column);
        
        $query = $db_connection -> query("select short_url from redirect_rule where original_url='$input_url' and cookie_id='$cookie_value_hashed'");
        while ( $row = $query -> fetch_assoc() ) {
            $shortened_link = $row['short_url'];
        }
        
        if ( $shortened_link == NULL ) {
            
            # Verify if proposed short link haven't been used before
            $unique_link_found = FALSE;
            while ( !$unique_link_found ) {
                $shortened_link = randomLowercaseChars($length);
                
                $result = getData($db_connection, 'redirect_rule', $shortened_link, 'short_url', 'original_url');
                
                if ( $result == NULL ) {
                    $unique_link_found = TRUE;
                }
            }

            $columns = "($current_datetime_column, short_url, original_url, cookie_id)";
            $values = "('$current_datetime','$shortened_link', '$key', '$cookie_value_hashed')";
            $insert_data_result = insertData($db_connection, RULETABLE, $columns, $values);
           
            # Update cookie last_entry and entry count
            $result = entriesIncrement( $db_connection, COOKIETABLE, $cookie_value_hashed );
            
            # Redirect & pass variable value
            $_SESSION['insert-data-result'] = $insert_data_result;
            $_SESSION['shortened-link'] = $shortened_link;
            $_SESSION['input-url'] = $input_url;
            unset($_POST['input_url']);
            echo '<script>document.location="'.$protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'"</script>';
            exit();
        } else if ( $shortened_link != NULL ) {
            echo 'AAA';
            $insert_data_result = TRUE;
            $_SESSION['insert-data-result'] = $insert_data_result;
            $_SESSION['shortened-link'] = $shortened_link;
            $_SESSION['input-url'] = $input_url;
            unset($_POST['input_url']);
            echo '<script>document.location="'.$protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'"</script>';
            exit();
        }
    } else if ( !$valid_input_url ) {
        $insert_data_result = FALSE;
        $_SESSION['insert-data-result'] = $insert_data_result;
        unset($_POST['input_url']);
        echo '<script>document.location="'.$protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'"</script>';
        exit();
    }
} 

if ( isset($_SESSION['insert-data-result']) && !empty($_SESSION['insert-data-result']) ) {
    $insert_data_result = $_SESSION['insert-data-result'];
    unset($_SESSION['insert-data-result']);
    
}
if ( isset($_SESSION['shortened-link']) && !empty($_SESSION['shortened-link']) ) {
    $shortened_link = $_SESSION['shortened-link'];
    unset($_SESSION['shortened-link']);
}
if ( isset($_SESSION['input-url']) && !empty($_SESSION['input-url']) ) {
    $input_url = $_SESSION['input-url'];
    unset($_SESSION['input-url']);
}

# EDIT SHORT URL
if ( !empty($_POST['original-url']) && !empty($_POST['short-link-after']) ) {
    $original_url = $_POST['original-url'];
    $short_link_after = $_POST['short-link-after'];
    // $result = updateData( $db_connection, RULETABLE, $original_url, 'original_url', $short_link_after, 'short_url');
    
    # Verify if proposed short link haven't been used before
    $unique_link_found = FALSE;
    while ( !$unique_link_found ) {
        $result = getData($db_connection, 'redirect_rule', $short_link_after, 'short_url', 'original_url');
        
        if ( $result == NULL ) {
            $unique_link_found = TRUE;
            $result = $db_connection -> query("update redirect_rule set short_url='$short_link_after' where original_url='$original_url' and cookie_id='$cookie_value_hashed'");
        } else if ( $result != NULL ) {
            $short_link_after = $short_link_after.rand(100,999);
        }
    }
    
    unset($_POST['original-url'], $_POST['short-link-after']);
    echo '<script>document.location="'.$protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'"</script>';
    exit();
}

# DELETE SHORT URL
if ( !empty($_POST['short-link-delete']) ) {
    $short_link_to_delete = $_POST['short-link-delete'];
    $result = $db_connection -> query("delete from redirect_rule where short_url='$short_link_to_delete' and cookie_id='$cookie_value_hashed'");
    
    # Update entries count (this one works)
    $result = entriesIncrement($db_connection, COOKIETABLE, $cookie_value_hashed, $increment_value=-1);
    
    unset($_POST['short-link-delete']);
    echo '<script>document.location="'.$protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'"</script>';
    exit();
}

# Get all entries with the same cookie_id
$result = array();
$query = $db_connection -> query("select * from redirect_rule where cookie_id='$cookie_value_hashed'");

# Get url entries for particular id
$id_entries = getData($db_connection, COOKIETABLE, $cookie_value_hashed, 'id', 'entries');

// $query2 = $db_connection -> query("select short_url from redirect_rule where original_url='http://www.google.com' and cookie_id='36a107f97722262b28d5ae36973a8875290a7066bd9795e958dd655d8274c9db'");
// while ( $row2 = $query2 -> fetch_assoc() ) {
//     $result2 = $row2['short_url'];
// }
// echo $result2;
// print_r($result2);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shorten your messy link in seconds! | Skip</title>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap');

        :root {
            --dark-blue:#3193CA;
            --light-blue:#41BBFF;
            --black:#000000;
            --dark-grey:#898989;
            --light-grey:#ABABAB;
            --white:white;
        }
        body {
            font-family: 'Quicksand', sans-serif;
            text-align: center;
            max-width: 500px;
            margin: auto;
            font-weight: normal;
            padding:1.5em;
        }
        h1, h2, h3, h4, h5, h6 {font-weight:bold}
        .dark-blue {color: var(--dark-blue)}
        .light-blue {color: var(--light-blue)}
        .dark-grey {color: var(--dark-grey)}
        .light-grey {color: var(--light-grey)}
        .u {text-decoration: underline;}
        .b {font-weight:bold}
        .display-inline {display:inline}
        .display-hidden {display:none}
        .display-block {display:block}
        .margin-lr-center {margin-left:auto; margin-right:auto}

        a:link {color:var(--black)}
        a:visited {color:var(--black)}
        a:hover {color:var(--dark-blue)}
        a:active {color:var(--light-blue)}

        .a-remove-u {text-decoration:none}
        .a-dark-blue:link {color:var(--dark-blue)}
        .a-dark-blue:visited {color:var(--dark-blue)}
        .a-dark-blue:hover {color:var(--light-blue)}
    

        .font-xs {font-size:0.25em}
        .font-s {font-size:0.5em}
        .font-m {font-size:0.75em}
        .font-l {font-size:1em}
        .font-xl {font-size:1.25em}
        .font-xxl {font-size:1.5em}

        .font-light {font-weight:lighter}
        .font-normal {font-weight:normal}

        .padding-tb-quarter {padding-top:0.25em; padding-bottom:0.25em}
        .padding-tb-half {padding-top:0.5em; padding-bottom:0.5em}
        .padding-tb-tquarter {padding-top:0.75em; padding-bottom:0.75em}
        .padding-tb-one {padding-top:1em; padding-bottom:1em}

        .margin-0 {margin:0 !important}

        .border-dark-blue {
            border:2px solid var(--dark-blue);
            border-radius: 0.75em;
            padding: 1em;
            margin: 1em 0;
        }

        button {transition-duration: 0.3s}
        .button-hollow-blue {
            border: 0.1em solid var(--dark-blue);
            border-radius: 0.6em;
            background-color: var(--white);
            color: var(--dark-blue);
            padding: 0.3em 0.9em;
            font-weight:bold;
            margin: 1em auto 1em auto;
        }
        .button-hollow-blue:hover {
            background-color: var(--dark-blue);
            color: var(--white);
        }
        .button-solid-grey {
            border: 0.1em solid var(--dark-grey);
            border-radius: 0.75em;
            background-color: var(--white);
            color: var(--dark-grey);
            padding: 0.4em 0.9em;
            font-weight:bold;
            margin: 1em auto 1em auto;
        }
        .button-solid-grey:hover {
            background-color: var(--dark-grey);
            color: var(--white);
        }

        .input-blue {
            transition: width 0.4s ease-in-out;
            border: 0.1em solid var(--dark-blue);
            border-radius: 0.75em;
            padding: 0.4em 0.9em;
            width: 60%;
            text-align: center;
            margin: 1em auto 1em auto;
        }
        .input-blue:hover {
            border-color: var(--light-blue);
            width: 80%;
        }
        .input-blue:focus {
            border-color: var(--light-blue);
            width: 80%;
        }

        .input-edit-blue {
            transition: width 0.4s ease-in-out;
            border:0px solid var(--white);
            border-bottom: 0.1em solid var(--dark-blue);
            padding: 0.2em 0;
            width: 8em;
            text-align: left;
        }
        .input-edit-blue:hover {
            border-color: var(--light-blue);
        }
        .input-edit-blue:focus {
            border-color: var(--light-blue);
        }
        
    </style>
</head>
<body>
    <div>

    </div>
    <h1><span class='dark-blue u'>Shorten</span><span class='dark-grey'> your messy link </span><span class='dark-blue u'>in seconds!</span></h1>
    <form action="<?php echo $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']?>" method="post">
        <label for="input_url">Enter your messed url here<br></label>
        <?php 
            if ( !empty($insert_data_result) ) {
                echo "<input type='text' id='input_url' class='input-blue font-l' name='input_url' placeholder='$input_url'>";

                $display = $insert_data_result == NULL ? 'display-hidden' : 'display-inline';
                $shortened_link_redirect = !empty($shortened_link) ? $shortened_link : '';
                $redirect = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$shortened_link_redirect;
                $redirect_with_protocol = $protocol."://".$redirect;

                echo "
                    <div class='b dark-blue'>done!</div>
                    <div>Simplified url: <a class='b u' href='$redirect_with_protocol' target='_blank'>$redirect</a></div>
                    <input type'text' name='clear_status' value='1' hidden>
                    <button type='submit' class='button-solid-grey font-xl'>Clear</button>
                    <button type='button' class='$display button-hollow-blue font-xl' onclick=\"copyText('$redirect_with_protocol', 'clipboard-copy')\">Copy url</button>";
            }
            else if ( empty($insert_data_result) ) {
                echo "<input type='text' id='input_url' class='input-blue font-l' name='input_url' placeholder='https://www.figma.com/blog/figma-now-has-guides/'>";
                echo "<input type'text' name='clear_status' value='0' hidden>";
                echo "<input type='submit' class='button-hollow-blue display-block font-xl' value='Simplify!'>";
            }
        ?>
        


    </form>
    <div id='clipboard-copy'></div>
    
    <div style='display:<?php echo $id_entries > 0 ? 'block' : 'none' ?>'>
        <h2>History</h2>
        <div>
            Friendly id: <b><?php echo $cookie_value ?></b>
            <div>Retain history by pasting link below on any device</div>
            <button class='button-hollow-blue font-m' onclick="copyText('<?php echo $protocol.':/\/'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?><?php echo hash('sha256',rand(100000,999999999)).'$'.$cookie_value_hashed; ?>' ,'clipboard-copy')">copy history link</button>
        </div>

        <!-- New layout of history -->
        <?php 

        while ( $row = $query -> fetch_assoc() ) {
            $creation_date = $row['creation_date'];
            $last_hit = $row['last_hit'];
            $original_url = $row['original_url'];
            $short_url = $row['short_url'];
            $short_url_with_domain = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] . $short_url;
            $short_url_with_protocol = "$protocol://$short_url_with_domain";
            $hit = $row['hit'];
        
        ?>
        <div class='border-dark-blue'>
            <div class='font-m b'><?php echo limChar($original_url, 35) ?></div>
            <div class='padding-tb-quarter'>
                <a id='short-link-redirect-<?php echo $short_url ?>' href="<?php echo $short_url_with_protocol ?>" class='font-xl b a-dark-blue a-remove-u' target='_blank'>
                    <?php echo limChar($short_url_with_domain, 20, -1) ?>
                </a>
                <form id='short-link-<?php echo $short_url ?>' action="./" method='post' style='display:none'>
                    <input type="hidden" name='original-url' value='<?php echo $original_url ?>'>
                    <input class='input-edit-blue font-xl dark-blue b' type='text' name='short-link-after' value='<?php echo $short_url ?>'>
                    <button class='button-hollow-blue font-m margin-0' type='submit'>change</button>
                </form>
            </div>
            <div>
                <button class='button-hollow-blue font-m' onclick="copyText('<?php echo $short_url_with_protocol ?>', 'clipboard-copy')">copy</button>
                <form id='short-link-delete-<?php echo $short_url ?>' class='display-inline' action='./' method='post'>
                    <input type="hidden" name='short-link-delete' value='<?php echo $short_url ?>'>
                    <button class='button-hollow-blue font-m' type='submit'>delete</button>
                </form>
                <button class='button-hollow-blue font-m' id='short-link-edit-toggle-button-<?php echo $short_url ?>' onclick="hideUnhideComponent('short-link-<?php echo $short_url ?>'); changeInnerText('short-link-edit-toggle-button-<?php echo $short_url ?>', 'edit', 'undo edit'); changeInnerText('short-link-redirect-<?php echo $short_url ?>', '<?php echo limChar($short_url_with_domain, 20, -1) ?>','<?php echo limchar("$_SERVER[HTTP_HOST] $_SERVER[REQUEST_URI]", 20, -1) ?>')">edit</button>
            </div>
            <div class='font-m dark-grey'>created: <?php echo $creation_date ?> GMT+7</div>
            <div class='font-m dark-grey'>last hit: <?php echo $last_hit ?> GMT+7</div>
            <div class='font-m dark-grey'>total hit: <?php echo $hit ?></div>
        </div>

        <?php } ?>
        
    </div>
    
    <!-- A URL Shortener Pilot Project from @jonathanvalerian and @rafaelatantya ©2022 -->
    <script>
        function copyText(text, information_id) {
            navigator.clipboard.writeText(text);
            
            var information = document.getElementById(information_id);
            information.innerHTML = 'Copied to clipboard!';
        }
        
        function hideUnhideComponent(id) {
            var component = document.getElementById(id);
            if (component.style.display == 'none') {
                component.style.display = 'inline';
            } else if (component.style.display != 'none') {
                component.style.display = 'none';
            }
        }
        
        function changeInnerText(id, innerText1, innerText2) {
            var component = document.getElementById(id);
            if (component.innerHTML == innerText1) {
                component.innerHTML = innerText2;
            } else if (component.innerHTML == innerText2) {
                component.innerHTML = innerText1;
            } else {
                component.innerHTML = innerText2;
            }
        }
        
        
    </script>
</body>
</html>


<?php 
# Clear post variable to prevent resubmission
unset($_POST);
?>


