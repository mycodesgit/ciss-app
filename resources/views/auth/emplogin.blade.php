@extends('layouts.master_main')

@section('title')
    CISS - Employee Login
@endsection

@section('body')
    <div class="row border rounded-5 p-3 bg-white shadow box-area">
        <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" style="background: #04401f;">
            <div class="featured-image mb-3">
                <center><img src="{{ asset('uilibs/images/cpsulogov4.webp') }}" class="img-fluid" id="" style="width: 100px; padding-top: 0px;"></center>
                <p class="text-white text-center" style="font-family: 'Courier New', Courier, monospace; font-weight: 600; font-size: 1.5em !important">CISS</p>
            </div>
            <small class="text-white text-wrap text-center" style="width: 17rem;font-family: 'Courier New', Courier, monospace;">CPSU Integrated System Solution</br></small>
            {{-- <center><img src="{{ asset('uilibs/images/cpsulogov4.webp') }}" class="img-fluid" id="cpsulogoleftsideImage" style="width: 80%; padding-top: 0px;"></center> --}}
        </div>

        <div class="col-md-6 right-box">
            <div class="row align-items-center">
                <div class="header-text mb-4 text-center">
                    <img src="{{ asset('uilibs/images/cpsulogov4.webp') }}" style="width:100px; margin-top: -250px" id="cpsulogoImage">
                    <h2>Hi, Cenphilian</h2>
                    <p>Sign in to start session</p>
                </div>
                <div>
                    <form action="#" method="post">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control form-control-lg bg-light fs-6" placeholder="Email" id="empEmailInput" autofocus>
                        </div>
                        <div class="input-group mb-1">
                            <input type="password" name="password" class="form-control form-control-lg bg-light fs-6" placeholder="Password" id="empPassInput">
                        </div>
                        <div class="input-group mb-4 d-flex justify-content-between">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="formCheck" onclick="document.getElementById('empPassInput').type = this.checked ? 'text' : 'password'">
                                <label for="formCheck" class="form-check-label text-secondary"><small>Show Password</small></label>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <button class="btn btn-lg btn-success w-100 fs-6">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <span style="font-size: 9pt; text-align: center; margin-top: 10px;">Maintained and Managed by Management Information System Office (MISO).</span>
    </div>
@endsection
