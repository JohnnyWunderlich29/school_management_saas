@extends('layouts.app')

@section('content')
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @php
        $loginBackgroundImages = [
            asset('images/16ca909e-a262-462a-a3f4-cfe8ee3288d0.jpg'),
            asset('images/image.jpg (1).jpg'),
            asset('images/image.jpg.jpg'),
        ];
        $loginBg = $loginBackgroundImages[array_rand($loginBackgroundImages)];
    @endphp

    <div class="absolute top-0 left-0 bottom-0 leading-5 h-full w-full overflow-hidden bg-center bg-cover"
        style="background-image: url('{{ $loginBg }}');">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    </div>

    <div class="relative min-h-screen sm:flex sm:flex-row justify-center bg-transparent rounded-3xl gap-10">
        <div class="flex-col flex self-center lg:px-14 sm:max-w-4xl xl:max-w-md z-10">
            <div class="self-start hidden lg:flex flex-col text-gray-300">
                <h1 class="my-3 font-semibold text-4xl">Seja bem-vindo</h1>
                <p class="pr-3 text-sm opacity-75">Bem-vindo à Jornada do Saber – Entre e Transforme o Futuro!</p>
            </div>
        </div>

        <div class="flex justify-center self-center z-10">
            <div class="p-12 bg-white mx-auto rounded-3xl w-96">
                <div class="mb-7">
                    <h3 class="font-semibold text-2xl text-gray-800">Login</h3>

                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="w-full text-sm px-4 py-3 bg-gray-200 focus:bg-gray-100 border border-gray-200 rounded-lg focus:outline-none focus:border-purple-400"
                            placeholder="Email" value="{{ old('email') }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative" x-data="{ show: true }">
                        <input id="password" name="password" :type="show ? 'password' : 'text'"
                            autocomplete="current-password" required placeholder="Password"
                            class="text-sm text-gray-800 px-4 py-3 rounded-lg w-full bg-gray-200 focus:bg-gray-100 border border-gray-200 focus:outline-none focus:border-purple-400">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="flex items-center absolute inset-y-0 right-0 mr-3 text-sm leading-5">
                            <svg @click="show = !show" :class="{ 'hidden': !show, 'block': show }" class="h-4 text-purple-700"
                                fill="none" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 576 512">
                                <path fill="currentColor"
                                    d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z">
                                </path>
                            </svg>

                            <svg @click="show = !show" :class="{ 'block': !show, 'hidden': show }" class="h-4 text-purple-700"
                                fill="none" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 640 512">
                                <path fill="currentColor"
                                    d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z">
                                </path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-sm ml-auto">
                            <a href="#" class="text-purple-700 hover:text-purple-600">
                                Esqueceu sua senha?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full flex justify-center bg-purple-800 hover:bg-purple-700 text-gray-100 p-3 rounded-lg tracking-wide font-semibold cursor-pointer transition ease-in duration-500">
                            Entrar
                        </button>
                    </div>

                    <div class="flex items-center justify-center space-x-2 my-5">
                        <p class="text-gray-400 mb-1">Não tem uma conta?</p>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('register.escola') }}"
                                class="text-sm text-purple-700 hover:text-purple-700">Registrar Escola</a>
                        </div>
                    </div>

                    <div class="flex justify-center gap-5 w-full">
                        <button type="button"
                            class="w-full flex items-center justify-center mb-6 md:mb-0 border border-gray-300 hover:border-gray-900 hover:bg-gray-900 text-sm text-gray-500 p-3 rounded-lg tracking-wide font-medium cursor-pointer transition ease-in duration-500">
                            <svg class="w-4 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#EA4335"
                                    d="M5.266 9.765A7.077 7.077 0 0 1 12 4.909c1.69 0 3.218.6 4.418 1.582L19.91 3C17.782 1.145 15.055 0 12 0 7.27 0 3.198 2.698 1.24 6.65l4.026 3.115Z" />
                                <path fill="#34A853"
                                    d="M16.04 18.013c-1.09.703-2.474 1.078-4.04 1.078a7.077 7.077 0 0 1-6.723-4.823l-4.04 3.067A11.965 11.965 0 0 0 12 24c2.933 0 5.735-1.043 7.834-3l-3.793-2.987Z" />
                                <path fill="#4A90E2"
                                    d="M19.834 21c2.195-2.048 3.62-5.096 3.62-9 0-.71-.109-1.473-.272-2.182H12v4.637h6.436c-.317 1.559-1.17 2.766-2.395 3.558L19.834 21Z" />
                                <path fill="#FBBC05"
                                    d="M5.277 14.268A7.12 7.12 0 0 1 4.909 12c0-.782.125-1.533.357-2.235L1.24 6.65A11.934 11.934 0 0 0 0 12c0 1.92.445 3.73 1.237 5.335l4.04-3.067Z" />
                            </svg>
                            <span>Google</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
