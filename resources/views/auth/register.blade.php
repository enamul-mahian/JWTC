@extends('frontend.layout.frontMaster')
@section('title', 'Job Seeker Registration')
@section('content')
<section class="jg-auth"><div class="jg-auth-card"><span class="jg-pill">For job seekers</span><h1>Create Your Account</h1><p>Jobs are visible to everyone. Enter your registration code to create a job seeker account.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />

            <x-text-input
                id="name"
                class="block mt-1 w-full"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>


        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />

            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="email"
            />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>


        <!-- Phone -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone')" />

            <x-text-input
                id="phone"
                class="block mt-1 w-full"
                type="text"
                name="phone"
                :value="old('phone')"
                required
                autocomplete="tel"
            />

            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>


        @if (config('security.registration_otp.enabled'))
            <div class="mt-4">
                <x-input-label for="job_category_id" value="Preferred Job Category" />
                <select id="job_category_id" name="job_category_id" class="block mt-1 w-full border rounded"
                    required>
                    <option value="">Select a category</option>
                    @foreach ($jobCategories as $category)
                        <option value="{{ $category->id }}" @selected(old('job_category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('job_category_id')" class="mt-2" />
            </div>
        @endif

        <!-- Referral Code -->
        <div class="mt-4">
            <x-input-label for="referral_code" :value="__('Solicitor / Registration Code')" />

            <x-text-input
                id="referral_code"
                class="block mt-1 w-full"
                type="text"
                name="referral_code"
                :value="old('referral_code')"
                required
                autocomplete="off"
            />

            <x-input-error :messages="$errors->get('referral_code')" class="mt-2" />

            <p class="mt-1 text-sm text-gray-500">
                Enter the Job Seeker referral code provided to you.
            </p>
        </div>


        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>


        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>


        @include('shared.hcaptcha')

        <div class="flex items-center justify-end mt-4">

            <a
                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md"
                href="{{ route('jobseeker.login') }}"
            >
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Create Job Seeker Account') }}
            </x-primary-button>

        </div>

    </form>

</div></section>
@endsection