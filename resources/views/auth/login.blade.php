@extends('auth.layouts.app')

@section('content')

<style>
.otp-input {
  background-color: #213a5b00; /* Tailwind gray-800 */
  color: #ffffff;           /* White text */
  border: 1px solid #ffd92e; /* Tailwind gray-700 */
}

.otp-input:focus {
  outline: none;
  border-color: #ffd92e;      /* Emerald-500 border on focus */
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.5);
}
</style>

<div class="w-full h-screen flex flex-col bg-black items-center justify-center bg-cover bg-center">

    <video autoplay muted loop playsinline class="absolute top-0 left-0 w-full h-full object-cover">
        <source src="{{ asset('assets/videos/login-bg.mp4') }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="absolute top-0 left-0 bg-black/10 w-full h-full"></div>

    <div class="lg:w-4/12 xl:w-4/12 md:w-7/12 max-w-md bg-black/30  backdrop-blur-md rounded-2xl dark:border-neutral-700 w-full dark:shadow-none p-6 shadow-stone-300 dark:shadow-neutral-800">

        <div class="w-full flex p-6 items-center justify-center">
            <a href="{{url('/')}}">
            <img src="{{asset('assets/img/ceylon-bloom-logo-text.webp')}}" alt="" class="dark:hidden w-40">
            <img src="{{asset('assets/img/ceylon-bloom-logo-text.webp')}}" alt="" class=" w-40 hidden dark:block">
            </a>
        </div>

        <div class="w-full flex px-3 py-6 items-center flex-col justify-center">

                <form method="POST" action="{{ route('login') }}" class="w-full flex gap-4 flex-col py-3">
                    @csrf

                    @error('email')
                    <div class="w-full p-3 bg-red-50 rounded-sm">
                        <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p>
                    </div>
                    @enderror

                   <div class="relative w-full">
                        <input type="text" id="email" name="email" value="{{ old('email') }}" required autofocus class="block otp-input rounded-lg px-2.5 pb-2.5 pt-5 w-full text-sm text-zinc-300 bg-[#4a5b71] dark:bg-rose-700 border-0 border-b-2 border-stone-300 appearance-none dark:text-zinc-300 dark:border-stone-600 dark:focus:border-stone-700 focus:outline-none focus:ring-0 focus:border-stone-700 peer pr-10" placeholder=" " />
                        
                        <label for="email" class="absolute text-sm text-zinc-300 dark:text-zinc-300 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-zinc-300 peer-focus:dark:text-zinc-300 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Email</label>
                        
                        <!-- Font Awesome Email Icon -->
                        <i class="fa-solid fa-envelope absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-300 dark:text-zinc-300 peer-focus:text-zinc-300"></i>
                    </div>

                    <div class="relative w-full">
                        <input type="password" id="password" name="password"  required class="block rounded-lg px-2.5 otp-input pb-2.5 pt-5 w-full text-sm text-stone-900 bg-stone-50 dark:bg-rose-700 border-0 border-b-2 border-stone-300 appearance-none dark:text-zinc-300 dark:border-stone-600 dark:focus:border-stone-700 focus:outline-none focus:ring-0 focus:border-stone-700 peer pr-10" placeholder=" " />
                        
                        <label for="password" class="absolute text-sm text-zinc-300 dark:text-zinc-300 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-zinc-300 peer-focus:dark:text-zinc-300 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4">Password</label>
                        
                        <!-- Password Toggle Icon -->
                        <i id="togglePassword" class="fa-solid fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-300 dark:text-zinc-300 cursor-pointer"></i>
                    </div>

                    <div class="block mt-6">
                        <label for="remember_me" class="flex items-center">
                            <x-checkbox id="remember_me" name="remember" />
                            <span class="ms-2 text-sm text-amber-50 dark:text-amber-400">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        {{-- @if (Route::has('password.request'))
                            <a class="underline text-sm text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-500 dark:focus:ring-offset-stone-800" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif --}}
        

                        <button class=" dark:bg-cyan-600 mb-0 text-sm text-zinc-300 py-2 px-4 ml-2 rounded-full shadow-lg flex items-center gap-3 bg-gradient-to-tr from-amber-400 to-yellow-700">
                            {{ __('Log in') }}

                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
        </div>

    </div>

   
</div>

@endsection