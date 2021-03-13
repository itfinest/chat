<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$pass = "";
$data = "chat";

$db = mysqli_connect($host,$user,$pass,$data);

$act = isset($_GET['act']) ? $_GET['act'] : '';

function tanggalIndo($str)
{
    $arrbulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    list($tahun,$bulan,$tanggal) = explode('-', $str);
    return ($tanggal . ' ' . $arrbulan[(int)$bulan-1] . ' ' . $tahun);
}

switch($act) {

    case 'register':

        $nama = trim($_POST['nama']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $maxid = mysqli_query($db, "select max(id_user) id_user from users");
        $result = mysqli_fetch_array($maxid);
        if($result) {
            $id_user = $result['id_user']+1;
        } else {
            $id_user = 1;
        }

        $sql = "insert into users(id_user,nama,username,password)
                values('$id_user','$nama','$username','$password');";
        $simpan = mysqli_query($db, $sql);

        if($simpan) {
            die(json_encode([
                "status" => "sukses",
                "pesan" => "Register berhasil!"
            ]));
        } else {
            die(json_encode([
                "status" => "gagal",
                "pesan" => "Register gagal!"
            ]));
        }

        break;

    case 'login':

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $sql = mysqli_query($db, "select * from users where username = '$username' and password = '$password'");
        $result = mysqli_fetch_array($sql);

        if($result) {

            $_SESSION['login'] = true;
            $_SESSION['id_user'] = intval($result['id_user']);
            $_SESSION['nama'] = $result['nama'];

            die(json_encode([
                "status" => "sukses",
                "pesan" => "Login berhasil!"
            ]));
        } else {
            die(json_encode([
                "status" => "gagal",
                "pesan" => "Login gagal!"
            ]));
        }
        
        break;
    
    case 'set_token':

        $token = addslashes( trim($_POST['token']) );
        $id = $_SESSION['id_user'];

        $sql = "update users set token = '$token' where id_user = '$id'";
        mysqli_query($db, $sql);

        break;
    
    case 'unset_token':

        $token = addslashes( trim($_POST['token']) );
        $sql = "update users set token = '' where token = '$token'";
        mysqli_query($db, $sql);

        break;
    
    case 'kontak':

        $id = intval($_SESSION['id_user']);
        $sql = "select u.*, 
                time_format(last_update, '%H:%i') jam
                from users u join users_contact uc
                on u.id_user=uc.id_user_from 
                where u.id_user not in ($id);";
        $query = mysqli_query($db, $sql);

        $data = [];
        while($row = mysqli_fetch_assoc($query)) {

            $read = mysqli_fetch_assoc(mysqli_query($db, "select count(*) jumlah from chat where id_user_to = '".$id."' 
                    and id_user_from in(".$row['id_user'].")
                    and dibaca = 0"));
            if(!empty($_SESSION['login'])) {
                $sql = "select c.pesan from chat c 
                        where (c.id_user_from = '".$row['id_user']."' and id_user_to = '$id')
                        or (c.id_user_from = '".$id."' and id_user_to = '".$row['id_user']."')
                        order by c.id_chat desc limit 1";
            }
            $last = mysqli_fetch_assoc(mysqli_query($db, $sql));

            $rows = [];
            $row['id_user'] = $row['id_user'];
            $row['username'] = $row['username'];
            $row['nama'] = $row['nama'];
            $row['jumlah'] = ($read['jumlah'] == 0) ? '' : $read['jumlah'];
            $row['last'] = strip_tags($last['pesan']);
            $row['token'] = $row['token'];
            $row['jam'] = $row['jam'];
            $row['status'] = (!empty($row['token'])) ? 
                            '<i style="color:green;font-size:.4rem;top:-1rem;right:-.25rem;position:relative;" class="fa fa-circle"></i>'
                            : '';
            $data[] = $row;
        }

        die(json_encode([
            "data" => $data
        ]));

        break;

    case 'list_chat':

        $id_user = intval($_SESSION['id_user']);
        $idto = intval($_POST['id_user']);
        $id = intval($_SESSION['id_user']);
        $token = addslashes(trim($_POST['token']));
        $sql = "select c.*, u.nama, time_format(datetime, '%H:%i') jam, date_format(datetime, '%Y-%m-%d') tanggal 
                from chat c join users u on c.id_user_from=u.id_user 
                where (c.id_user_from = '$id_user' and c.id_user_to = '$idto')
                or (c.id_user_from = '$idto' and c.id_user_to = '$id_user')
                order by id_chat asc;";
        $query = mysqli_query($db, $sql);

        $html = '';
        $data = [];
        $tanggal = '';

        $kontak = mysqli_fetch_assoc( mysqli_query($db, "select * from users where id_user = '$idto'") );

        $profil = '<div class="chat-profile-user">
                        <img src="user.png" style="width:44px;border-radius:50%;" alt="">
                        <div class="chat-username">'.$kontak['nama'].'</div>
                        <div style="float:right;text-align:right">
                            <div class="dropdown">
                                <a style="cursor:pointer" id="dropdownMenuLink" data-display="static" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                        <path fill="currentColor" d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path>
                                    </svg>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item btn-clear" href="javascript:;">Bersihkan chat</a>
                                </div>
                            </div>
                        </div>
                    </div>';

        $no = 0;
        while($row = mysqli_fetch_assoc($query)) {

            ++$no;
            
            if($row['tanggal'] != $tanggal) {
                $html .= '<div class="tanggal mb-3"><span>'.tanggalIndo($row['tanggal']).'</span></div>';
            }

            $dibaca = "";
            if($row['dibaca'] == 0) {
                $dibaca = '<i class="fa fa-check"></i>';
            } else {
                $dibaca = '<i class="fa fa-check" style="color:green"></i>';
            }

            if($_SESSION['login'] == true && $_SESSION['id_user'] == $row['id_user_from']) {

                if($row['is_visible_from'] <> 1) {
                    $html .= '<div data-id="'.($no.''.$row['id_chat']).'" class="card mb-3 messages" style="min-width:15%;max-width:55%;float:right;clear:both;">
                                <div class="card-body" style="padding:.8rem">
                                    <div class="media">    
                                        <div class="media-body">
                                            <div data-id="'.($no.''.$row['id_chat']).'" class="chat_opsi_menu chat_opsi_menus'.($no.''.$row['id_chat']).'">
                                            <div class="btn-group">
                                                <a role="button" id="dropdownMenuLink'.($no.''.$row['id_chat']).'" data-display="static" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-chevron-down"></i></a>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink'.($no.''.$row['id_chat']).'">
                                                    <a class="dropdown-item hapus_pesan" data-kode="'.$row['id_user_from'].'#'.$row['id_user_to'].'#'.$row['id_chat'].'" href="javascript:;">Hapus pesan</a>
                                                </div>
                                            </div>
                                            </div>
                                            <p>'.(($row['is_hapus'] == 0 ) ? $row['pesan'] : '<i style="color:#777">Pesan telah dihapus</i>').'</p>
                                            <small class="mt-2" style="text-align:right;float:right;font-size:.7rem;color:#999">
                                            '.$row['jam'].'
                                            '.$dibaca.'
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                }
            } else {
                if($row['is_visible_to'] <> 1) {
                    $html .= '<div data-id="'.($no.''.$row['id_chat']).'" class="card mb-3 messages" style="min-width:15%;max-width:55%;float:left;clear:both;">
                                <div class="card-body" style="padding:.8rem">
                                    <div class="media">
                                        <div class="media-body">
                                            <div data-id="'.($no.''.$row['id_chat']).'" class="chat_opsi_menu chat_opsi_menus'.($no.''.$row['id_chat']).'">
                                            <div class="btn-group">
                                                <a role="button" id="dropdownMenuLink'.($no.''.$row['id_chat']).'" data-display="static" data-toggle="dropdown" 
                                                aria-haspopup="true" aria-expanded="false"><i class="fa fa-chevron-down"></i></a>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink'.($no.''.$row['id_chat']).'">
                                                    <a class="dropdown-item hapus_pesan" data-kode="'.$row['id_user_from'].'#'.$row['id_user_to'].'#'.$row['id_chat'].'" href="javascript:;">Hapus pesan</a>
                                                </div>
                                            </div>
                                            </div>
                                            <p>'.(($row['is_hapus'] == 0 ) ? $row['pesan'] : '<i style="color:#777">Pesan telah dihapus</i>').'</p>
                                            <small class="mt-2" style="text-align:right;float:right;font-size:.7rem;color:#999">
                                            '.$row['jam'].'
                                            '.$dibaca.'
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                }
            }
           
            $tanggal = $row['tanggal'];
        }


        $update = mysqli_query($db, "update chat set dibaca = 1 where id_user_from = '$idto'");
        // $update = mysqli_query($db, "update chat set dibaca = 1 where id_user_from = '$id'");

        // if(empty($html)) $html .= '<div class="text-center"><strong class="text-center">Tidak ada pesan</strong></div>';

        die(json_encode([
            "profil" => $profil,
            "chat" => $html
        ]));

        break;
    
    case 'kirim': 

        $id_user = intval($_SESSION['id_user']);
        $pesan = addslashes(trim($_POST['teks']));
        $kepada = intval($_POST['kepada']);

        $data = [];
        if(!empty($pesan)) {
            foreach(explode(' ', $pesan) as $key => $value) {
                $data[$key] = $value;
                if(filter_var($value, FILTER_VALIDATE_URL)) {
                    $data[$key] = '<a href="'.$value.'" target="_blank">'.$value.'</a>';
                }

                if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $data[$key] = '<a href="mailto:'.$value.'">'.$value.'</a>';
                }
            }
        }

        $pesan = implode(" ", $data);

        $sql = "insert into chat(id_user_from,id_user_to,pesan)
                values('$id_user','$kepada','$pesan');";
        $insert = mysqli_query($db, $sql);
        if($insert) {
            die(json_encode([
                "status" => "sukses",
            ]));
        } else {
            die(json_encode([
                "status" => "gagal",
                "pesan" => "Ada kesalahan!"
            ]));
        }

        break;
    
    case 'hapus_pesan': 
        $kode = trim($_POST['kode']);
        $idfrom = 0;
        $idto = 0;
        $idchat = 0;
        if(!empty($kode)) {
            list($idfrom,$idto,$idchat) = explode('#', $kode);
        }

        $status = mysqli_fetch_assoc(mysqli_query($db, "select is_hapus,is_visible_from,is_visible_to from chat where id_chat = '$idchat' and
                id_user_from = '$idfrom' and id_user_to = '$idto'"));

        if($status['is_hapus'] == 1) {
            if(!empty($_SESSION['login'])) {
                if($_SESSION['id_user'] == $idfrom) {
                    $hapus = mysqli_query($db, "update chat set is_visible_from = 1 where id_chat = '$idchat' 
                            and id_user_from = '$idfrom' and id_user_to = '$idto'");
                } elseif ($_SESSION['id_user'] == $idto) {
                    $hapus = mysqli_query($db, "update chat set is_visible_to = 1 where id_chat = '$idchat' 
                            and id_user_from = '$idfrom' and id_user_to = '$idto'");
                }
            }
        } else {

            if(!empty($_SESSION['login'])) {
                if($_SESSION['id_user'] == $idfrom) {
                    $hapus = mysqli_query($db, "update chat set is_hapus = 1 where id_chat = '$idchat' 
                    and id_user_from = '$idfrom' and id_user_to = '$idto'");
                } elseif ($_SESSION['id_user'] == $idto) {
                    $hapus = mysqli_query($db, "update chat set is_visible_to = 1 where id_chat = '$idchat' 
                            and id_user_from = '$idfrom' and id_user_to = '$idto'");
                }
            }
        }

        if($hapus) {
            die(json_encode([
                "status" => "sukses",
            ]));
        } else {
            die(json_encode([
                "status" => "gagal",
                "pesan" => "Ada kesalahan!"
            ]));
        }
        break;

    case 'logout':
        mysqli_query($db, "update users set token = '' where id_user = '".$_SESSION['id_user']."'");
        session_destroy();
        die(true);
        break;

}