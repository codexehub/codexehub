@props(['head' => null])

<div class="admin-table-wrap">
    <table class="admin-table">
        @isset($head)
            <thead>
                {{ $head }}
            </thead>
        @endisset
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
