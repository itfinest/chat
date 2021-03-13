<div class="header"></div>
<main>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9 col-md-12 mx-auto">
                <div class="chat-wrapper">
                    <div class="row">
                        <div class="col-md-3 padding-0">
                            <div class="chat-user">
                                <img src="user.png" style="width:15%;border-radius:50%;" alt="">
                                <div class="chat-username"><?= @$_SESSION['nama']; ?></div>
                                <div class="chat-opsi">
                                    <div class="dropdown">
                                        <a style="cursor:pointer" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                                <path fill="currentColor" d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path>
                                            </svg>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item btn-add" href="javascript:;">Tambah Kontak</a>
                                            <a class="dropdown-item btn-logout" href="javascript:;">Keluar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-list">
                                <ul class="list__kontak"></ul>
                            </div>
                        </div>
                        <div class="col-md-9" style="padding-left:0 !important;">
                            <div class="chat-null">
                                <div class="logo" style="text-align:center"><img src="filled-chat.png" alt=""></div>
                                <div style="text-align:center"><h1>Lets Chat!</h1></div>
                            </div>
                            <div class="chat-display">
                                <div class="chat-profil"></div>
                                <div class="chat-bg list__chat"></div>
                            </div>
                            <div class="input-group chat-text">
                                <input type="text" class="form-control text__chat" placeholder="Write something..." aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-send" type="button"><i class="fa fa-paper-plane"></i></button>
                                </div>
                            </div
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
