<x-app-layout>
    <x-slot name="title">Profil</x-slot>
    <div class="page max-w-3xl pt-4 sm:pt-6">
        @include('partials.account-nav')

        <h1 class="heading-page mt-5">Profil</h1>

        <div class="mt-3 space-y-4">
            <div class="card p-4 sm:p-6">
                <livewire:profile.update-profile-information-form />
            </div>
            <div class="card p-4 sm:p-6">
                <livewire:profile.update-password-form />
            </div>
            <div class="card p-4 sm:p-6">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-app-layout>
