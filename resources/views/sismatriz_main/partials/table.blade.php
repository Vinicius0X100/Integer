<tbody class="border-top-0" id="usersTableBody">
    @forelse($users as $user)
        <tr>
            <td class="ps-4">
                <div class="form-check">
                    <input class="form-check-input user-checkbox" type="checkbox" name="selected[]" value="{{ $user->id }}">
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    @if($user->avatar && $user->avatar !== 'unknow_user.png')
                        <img src="https://central.sismatriz.online/storage/uploads/avatars/{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle me-3 border" style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                        <div class="avatar-initial rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                        <div class="small text-muted">ID: {{ $user->id }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="d-flex flex-column">
                    <span class="fw-medium">{{ $user->user }}</span>
                    <span class="small text-muted">{{ $user->email }}</span>
                </div>
            </td>
            <td>
                @php
                    $userRoleIds = $user->rule ? explode(',', $user->rule) : [];
                    $userRoleNames = [];
                    foreach($userRoleIds as $rid) {
                        if(isset($rolesMap[$rid])) {
                            $userRoleNames[] = $rolesMap[$rid];
                        }
                    }
                @endphp
                @if(count($userRoleNames) > 0)
                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill mb-1" title="{{ implode(', ', $userRoleNames) }}">
                        {{ count($userRoleNames) }} Cargo(s)
                    </span>
                    <div class="small text-muted text-truncate" style="max-width: 200px;">
                        {{ implode(', ', $userRoleNames) }}
                    </div>
                @else
                    <span class="text-muted small">-</span>
                @endif
            </td>
            <td>
                @if($user->paroquia && $user->paroquia->foto)
                    <img src="https://central.sismatriz.online/storage/uploads/paroquias/{{ $user->paroquia->foto }}" alt="{{ $user->paroquia->name }}" title="{{ $user->paroquia->name }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" data-bs-toggle="tooltip">
                @else
                    <span class="text-muted small">-</span>
                @endif
            </td>
            <td class="text-center">
                @if($user->is_pass_change == 1)
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Alterada</span>
                @else
                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Padrão</span>
                @endif
            </td>
            <td class="text-center">
                @if($user->status == 0)
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Ativo</span>
                @else
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inativo</span>
                @endif
            </td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <span class="small text-muted">{{ $user->formatted_last_login ?? 'Nunca acessou' }}</span>
                    @if(!empty($user->inactive_alert))
                        <i class="bi bi-exclamation-triangle-fill text-danger sismatriz-blink-danger"
                            data-bs-toggle="tooltip"
                            title="Sem acesso há {{ number_format((int) $user->inactive_days, 0, ',', '.') }} dias. Ideal inativar o usuário."></i>
                    @endif
                </div>
            </td>
            <td class="text-end pe-4">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="showUserDetails({{ $user->id }})" title="Detalhes">
                        <i class="bi bi-eye text-primary"></i>
                    </button>
                    <a href="{{ route('sismatriz-main.edit', $user->id) }}" class="btn btn-sm btn-light rounded-circle" title="Editar">
                        <i class="bi bi-pencil text-primary"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-light rounded-circle" onclick='confirmDelete({{ $user->id }}, @json($user->name))' title="Excluir">
                        <i class="bi bi-trash text-danger"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center py-5">
                <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                    <i class="bi bi-inbox fs-1 mb-3 opacity-50"></i>
                    <p class="mb-0">Nenhum usuário encontrado.</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
