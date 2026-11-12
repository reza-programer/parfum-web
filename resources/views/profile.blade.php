<x-app-layout>
    <x-slot name="title">Profil</x-slot>
    <div class="page max-w-3xl pt-10 lg:pt-14">
        @include('partials.account-nav')

        <h1 class="heading-page mt-10">Profil</h1>

        <div class="mt-8 space-y-6">
            <div class="bg-paper-card p-6 sm:p-10">
                <livewire:profile.update-profile-information-form />
            </div>
            <div class="bg-paper-card p-6 sm:p-10">
                <livewire:profile.update-password-form />
            </div>
            <div class="bg-paper-card p-6 sm:p-10">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-app-layout>
