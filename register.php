<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 col-md-6 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="text-center mb-3 mt-3"><strong>Register</strong></h1>
                    <form method="POST" id="register">
                    <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="text" name="nama" id="nama" placeholder="Nama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" placeholder="Username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" placeholder="Password" class="form-control" required>
                        </div>
                        <div class="form-group text-center">
                            <button class="btn btn-primary">
                                Register
                            </button>
                            <br/><br/>
                            Sudah punya akun?
                            <br/><br/>
                            <a href="?page=login" class="btn btn-warning">
                                Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>