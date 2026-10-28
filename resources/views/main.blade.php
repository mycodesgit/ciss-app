@extends('layouts.master_main')

@section('title')
    CISS
@endsection

@section('body')
    <div class="row border rounded-5 p-3 bg-white shadow box-area">
        <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" style="background: #04401f;">
            {{-- <div id="particles-js"></div> --}}
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
                    <p>Select Transaction</p>

                </div>
                <div class="">
                    <div>
                        <a href="{{ route('portal.admission') }}" wire:navigate class="btn btn-success btn-lg w-100 fs-6 text-bolder mb-2">Admission</a>
                        <a href="{{ route('student.login') }}" wire:navigate class="btn btn-success btn-lg text-bold w-100 fs-6 mb-2">Student</a>
                        <a href="{{ route('faculty.login') }}" wire:navigate class="btn btn-success btn-lg text-bold w-100 fs-6 mb-2">Faculty</a>
                        <a href="#" class="btn btn-success btn-lg text-bold w-100 fs-6 mb-2 disabled">Request for Documents</a>
                    </div>
                </div>
            </div>
        </div>
        <span style="font-size: 9pt; text-align: center; margin-top: 10px;">Maintained and Managed by Management Information System Office (MISO).</span>
    </div>
@endsection
