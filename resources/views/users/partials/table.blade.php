<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
        <thead class="bg-light">
            <tr>
                <th class="px-4 py-3 border-bottom" style="width: 40px;">
                    <input type="checkbox" class="form-check-input" id="checkAll">
                </th>
                <th scope="col" class="ps-4 py-3 text-uppercase text-muted small fw-bold border-bottom">
                    <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'nome', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center">
                        Usuário <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                    </a>
                </th>
                <th scope="col" class="py-3 text-uppercase text-muted small fw-bold border-bottom">
                    <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'papel', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center">
                        Papel <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                    </a>
                </th>
                <th scope="col" class="py-3 text-uppercase text-muted small fw-bold border-bottom">
                    <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'status', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center">
                        Status <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                    </a>
                </th>
                <th scope="col" class="py-3 text-uppercase text-muted small fw-bold border-bottom">
                    <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'ultimo_login_em', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted d-flex align-items-center">
                        Último Login <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.7rem;"></i>
                    </a>
                </th>
                <th scope="col" class="text-end pe-4 py-3 text-uppercase text-muted small fw-bold border-bottom">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td class="px-4 py-3">
                        <input type="checkbox" name="selected[]" value="{{ $user->id }}" class="form-check-input row-checkbox">
                    </td>
                    <td class="ps-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white me-3 shadow-sm" style="width: 42px; height: 42px; min-width: 42px; font-size: 1.1rem; background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="" class="rounded-circle w-100 h-100 object-fit-cover">
                                @else
                                    {{ strtoupper(substr($user->nome ?? $user->email, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <div class="fw-bold text-body">{{ $user->nome . ' ' . $user->sobrenome }}</div>
                                <div class="text-muted small">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill fw-medium px-3 py-2">
                            {{ ucfirst($user->papel) }}
                        </span>
                    </td>
                    <td class="py-3">
                        @if($user->status == 1)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-medium px-3 py-2">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Ativo
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill fw-medium px-3 py-2">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Inativo
                            </span>
                        @endif
                    </td>
                    <td class="py-3">
                        <div class="small text-muted">
                            @if($user->ultimo_login_em)
                                <div class="fw-medium text-body">{{ $user->ultimo_login_em->format('d/m/Y') }}</div>
                                <div>{{ $user->ultimo_login_em->format('H:i') }}</div>
                            @else
                                <span class="text-muted opacity-50">-</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end pe-4 py-3">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm rounded-circle me-1 border-0 shadow-sm" onclick="openDetailsModal({{ $user->id }})" title="Ver Detalhes" style="width: 32px; height: 32px;">
                                <i class="bi bi-eye text-primary"></i>
                            </button>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-light btn-sm rounded-circle me-1 border-0 shadow-sm d-flex align-items-center justify-content-center" title="Editar" style="width: 32px; height: 32px;">
                                <i class="bi bi-pencil text-secondary"></i>
                            </a>
                            <button type="button" class="btn btn-light btn-sm rounded-circle border-0 shadow-sm" onclick="openDeleteModal({{ $user->id }}, '{{ $user->nome_exibicao ?? $user->email }}')" title="Excluir" style="width: 32px; height: 32px;">
                                <i class="bi bi-trash text-danger"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="text-muted d-flex flex-column align-items-center">
                            <i class="bi bi-search fs-1 mb-3 opacity-25"></i>
                            <p class="mb-0">Nenhum usuário encontrado para a pesquisa.</p>
                            <a href="{{ route('users.index') }}" class="btn btn-link text-decoration-none mt-2">Limpar Filtros</a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center border-top p-3">
    {{ $users->links() }}
</div>
