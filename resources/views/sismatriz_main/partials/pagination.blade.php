@if($users->hasPages())
    {{ $users->appends(request()->query())->links() }}
@endif
