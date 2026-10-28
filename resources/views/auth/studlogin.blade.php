@extends('layouts.master_main')

@section('title')
    CISS - Student Login
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
                    @php
                        date_default_timezone_set('Asia/Manila');

                        $now = now();
                        $openingDate = \Carbon\Carbon::create(2026, 5, 25, 0, 0, 0, 'Asia/Manila');
                        $startTime = now()->setHour(8)->setMinute(0)->setSecond(0);
                        $endTime = now()->setHour(17)->setMinute(0)->setSecond(0);
                    @endphp
                </div>
                <div class="">
                    <form action="#" method="post">
                        @csrf
                        <div class="input-group mb-2">
                            <input type="text" name="studid" class="form-control form-control-lg bg-light fs-6" placeholder="Student ID number" id="studentIdInput" oninput="formatInput(this); this.value = this.value.toUpperCase()" autofocus>
                        </div>
                        <div class="input-group mb-1">
                            <input type="password" name="password" class="form-control form-control-lg bg-light fs-6" placeholder="Password" id="studentPassInput">
                        </div>
                        <div class="input-group mb-3 d-flex justify-content-between">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="formCheck" onclick="document.getElementById('studentPassInput').type = this.checked ? 'text' : 'password'">
                                <label for="formCheck" class="form-check-label text-secondary"><small>Show Password</small></label>
                            </div>
                            @if($now->lt($openingDate) || !$now->isWeekday() || $now->lt($startTime) || $now->gte($endTime))
                            @else
                                <div class="forgot">
                                    <small><a href="#" class="">Forgot Password?</a></small>
                                </div>
                            @endif
                        </div>
                        <div class="input-group mb-3">
                            <button class="btn btn-lg btn-success w-100 fs-6">Login</button>
                        </div>
                        {{-- <div class="modal-content">
                            <div class="modal-header text-center justify-content-center mb-3">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-triangle text-warning"></i> Temporarily Unavailable
                                </h5>
                            </div>

                            <div class="modal-body text-center bg-light p-4 rounded-3 mb-2">

                                <p style="font-size: 11pt; font-weight: 500;">
                                    The student login portal is currently undergoing system maintenance. We appreciate your patience while we work to improve the system.

                                    Please return to the home page and try again soon.
                                </p>
                            </div>
                        </div> --}}
                        <div class="text-center"><a href="{{ route('home') }}" wire:navigate class="text-center text-dark" style="text-decoration: none">Back to main page</a></div>
                    </form>
                </div>
            </div>
        </div>
        <span style="font-size: 9pt; text-align: center; margin-top: 10px;">Maintained and Managed by Management Information System Office (MISO).</span>
    </div>
@endsection

@push('scripts')
    <script>
        function formatInput(input) {
            let cleaned = input.value.replace(/[^A-Za-z0-9]/g, '');

            if (cleaned.length > 0) {
                let formatted = cleaned.substring(0, 4) + '-' + cleaned.substring(4, 8) + '-' + cleaned.substring(8, 9);
                input.value = formatted;
            } else {
                input.value = '';
            }
        }

        function handleDelete(event) {
            if (event.key === 'Backspace') {
                let input = event.target;
                let value = input.value;
                input.value = value.substring(0, value.length - 1);
                formatInput(input);
            }
        }
    </script>
@endpush
