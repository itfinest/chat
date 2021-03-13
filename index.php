<?php 
session_start(); 
$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http");
$base_url .= "://".$_SERVER['HTTP_HOST'];
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chattingan Yuk!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>
    <link rel="stylesheet" href="style.css?v=<?= sha1(microtime(true)); ?>">
    <link rel="shortcut icon" href="filled-chat.png" type="image/x-icon">
</head>
<body>

    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    switch($page) {
        case 'login':
            include __DIR__.'/login.php'; 
            break;
        
        case 'register':
            include __DIR__.'/register.php'; 
            break;
        
        case 'home':
            if(empty($_SESSION['login'])) header("Location: ?page=login");
            include __DIR__.'/chat.php';
            break;
        
        case 'logout':
            session_destroy();
            header("location: ?page=login");
            break;

        default:
            if(empty($_SESSION['login'])) header("Location: ?page=login");
            include __DIR__.'/chat.php';
            break;

    }

    ?>
    <!-- <div style="position:absolute;bottom:0;width:100%;z-index:-999">
        <svg style="width:100%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#20c997" fill-opacity="1" d="M0,224L60,197.3C120,171,240,117,360,85.3C480,53,600,43,720,80C840,117,960,203,1080,213.3C1200,224,1320,160,1380,128L1440,96L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path></svg>
    </div> -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/3.0.5/socket.io.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


<script>
    var socket = io('https://untuks-socket.herokuapp.com/', {
        secure: true,
        transports: ['websocket', 'polling']
    });

    var base_url = '<?= $base_url; ?>';

    // var socket = io('http://localhost:2500/', {
    //     secure: true,
    //     transports: ['websocket', 'polling']
    // });

    <?php if(isset($_SESSION['login'])): ?>
    localStorage.removeItem('id_user')
    localStorage.removeItem('token_user')
    

    socket.on('connect', function() {
        localStorage.setItem('session', socket.id)
        socket.emit('new', localStorage.getItem('session'))
        $.post('proses.php?act=set_token', {
            token: localStorage.getItem('session')
        }, function(res) {
            
        })
    })

    load_session = () => {
        if(localStorage.getItem('id_user') === null) {
            $('.chat-text').hide();
            $('.chat-bg').hide();
            $('.chat-null').show();
        } else {
            $('.chat-text').show();
            $('.chat-bg').show();
            $('.chat-null').hide();
        }
    }

    load_kontak = () => {

        $.post('proses.php?act=kontak', function(data) {
            var json = JSON.parse(data);
            if(json.data.length === 0) {
                $('#data').html('Tidak ada data');
            } else {
                var tampilan = '';
                var no = 0;
                $.each(json.data, function(index, val) {
                    ++no;
                    let aktif = (localStorage.getItem('id_user') === val.id_user) ? 'active' : '';
                   tampilan += '<li data-token="'+val.token+'" data-kode="'+val.id_user+'" class="li'+val.id_user+' ' + aktif + '">';
                //    tampilan += val.status;
                   tampilan += '<img class="mr-3" style="max-width:30px;border-radius:50%" src="user.png" alt="Generic placeholder image">';
                   tampilan += '<a class="klik_kontak" data-token="'+val.token+'" data-kode="'+val.id_user+'" href="javascript:;">'+val.nama+''; 
                   tampilan += '<span style="float:right" class="badge badge-success">'+val.jumlah+'</span>';
                   tampilan += '</a>';
                   tampilan += '<p><small>'+((val.last === null) ? '' : val.last)+'</small></p>';
                   tampilan += '</li>';
                })

                $('.list__kontak').html(tampilan)

                $('li, .klik_kontak').on('click', function(e) {
                    e.preventDefault()
                    var click = $(this);
                    $('li').removeClass('active')
                    $('.li'+click.data('kode')).addClass('active');
                    localStorage.setItem("id_user", click.data('kode'))
                    localStorage.setItem("token_user", click.data('token'))
                    socket.emit('pesan', localStorage.getItem('token_user'));
                    load_chat(localStorage.getItem('id_user'), localStorage.getItem('token_user'));
                    load_kontak()
                })
            }
        })

        load_session()

    }

    load_chat = (id_user = 0, token = '', status = 0) => {
        $.post('proses.php?act=list_chat', {
            id_user: id_user,
            token: token
        }, function(data) {
            var json = JSON.parse(data);
            $('.chat-profil').html(json.profil)
            $('.list__chat').html(json.chat)
            $('.card').hover(function() {
                $('.chat_opsi_menus' + $(this).data('id')).fadeIn()
            }, function() {
                $('.chat_opsi_menus' + $(this).data('id')).fadeOut()
            })

            $('.hapus_pesan').on('click', function(e) {
                e.preventDefault()
                var klik = $(this)
                $.post('proses.php?act=hapus_pesan', {
                    kode: klik.data('kode')
                }, function(res) {
                    var json = JSON.parse(res)
                    if(json.status === "sukses") {
                        socket.emit('pesan', localStorage.getItem('session'), localStorage.getItem('token_user'))
                    } else {
                        toastr.error(json.pesan, 'Peringatan')
                    }
                })
            })
            if(status === 0) {
                $('.list__chat').scrollTop($('.list__chat')[0].scrollHeight);
            }
        })
        load_session() 
    }

    load_kontak();

    load_chat(localStorage.getItem('id_user'), localStorage.getItem('token_user'));

    $('.text__chat').on('keypress', function(e) {
        if($(this).val().trim() !== '') {
            if(e.keyCode === 13) {
                if($('.text__chat').val().trim() !== '') {
                    $.ajax({
                        url: 'proses.php?act=kirim',
                        type: 'post',
                        data: {
                            teks: $('.text__chat').val().trim(),
                            kepada: localStorage.getItem('id_user')
                        },
                        dataType: 'json',
                        success:function(res) {
                            if(res.status === "sukses") {
                                $('.text__chat').val('')
                                socket.emit('load')
                                socket.emit('pesan', localStorage.getItem('session'), localStorage.getItem('token_user'))
                            } else {
                                toastr.error(res.pesan, "Peringatan");
                            }
                        }
                    })
                }
            }
        }
    })

    $('.btn-send').on('click', function(e) {
        e.preventDefault()

        if($('.text__chat').val().trim() !== '') {

            $.ajax({
                url: 'proses.php?act=kirim',
                type: 'post',
                data: {
                    teks: $('.text__chat').val().trim(),
                    kepada: localStorage.getItem('id_user')
                },
                dataType: 'json',
                success:function(res) {
                    if(res.status === "sukses") {
                        $('.text__chat').val('')
                        socket.emit('pesan', localStorage.getItem('session'), localStorage.getItem('token_user'))
                        socket.emit('load')
                    } else {
                        toastr.error(res.pesan, "Peringatan");
                    }
                }
            })

        }
    })

    $('.btn-logout').on('click', function(e) {
        e.preventDefault();
        $.post('proses.php?act=logout', function(res) {
            window.location.href = base_url
        })
    })

    socket.on('pesanku', function(data) {
        load_chat(localStorage.getItem('id_user'), localStorage.getItem('token_user'));
        load_kontak()
    })

    socket.on('load', function() {
        load_kontak()
    })

    socket.on('user_logout', function(data) {
        $.post('proses.php?act=unset_token', {
            token: data
        }, function(res) {
            
        })
    })
    
    load_session()

    <?php else: ?>

    $('form#register').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);

        $.ajax({
            url: 'proses.php?act=register',
            type: 'post',
            data: form.serializeArray(),
            dataType: 'json',
            beforeSend:function() {
                $('.btn').attr('disabled', true)
            },  
            success:function(res) {
                if(res.status === "sukses") {
                    socket.emit('data_masuk', '');
                    toastr.success(res.pesan, "Informasi");
                    setTimeout(function() {
                        window.location.href = "?page=login";
                    },1200);
                } else {
                    $('.btn').removeAttr('disabled')
                    toastr.error(res.pesan, "Peringatan");
                }
            }
        })
    })

    $('form#login').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);

        $.ajax({
            url: 'proses.php?act=login',
            type: 'post',
            data: form.serializeArray(),
            dataType: 'json',
            beforeSend: function () {
                $('.btn').attr('disabled', true)
            },  
            success:function(res) {
                if(res.status === "sukses") {
                    socket.emit('data_masuk', '');
                    toastr.success(res.pesan, "Informasi");
                    setTimeout(function() {
                        window.location.href = base_url;
                    },1200);
                } else {
                    $('.btn').removeAttr('disabled')
                    toastr.error(res.pesan, "Peringatan");
                }
            }
        })
    })

    <?php endif; ?>

</script>

</body>
</html>