<!DOCTYPE html>
<html>
<head>
    <title>Receitas</title>
</head>
<body>

<h1>Lista de Receitas TOP</h1>

@if(session('success'))
    <p style="color:green;">{{ session('success') }}</p>
@endif

<form method="GET" action="{{ route('receitas.index') }}" style="margin-bottom:20px;">
    <label>
        Data inicial
        <input type="date" name="data_inicial" value="{{ $filters['data_inicial'] ?? '' }}">
    </label>

    <label>
        Data final
        <input type="date" name="data_final" value="{{ $filters['data_final'] ?? '' }}">
    </label>

    <label>
        Status
        <select name="status">
            <option value="">Todos</option>
            @foreach($statusOptions as $value => $label)
                <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </label>

    <button type="submit">Filtrar</button>
    <a href="{{ route('receitas.index') }}">Limpar</a>
    <a href="{{ route('receitas.export-pdf', request()->query()) }}">Exportar PDF</a>
</form>

<button onclick="document.getElementById('formReceita').style.display='block'">
    Adicionar Receita
</button>

<div id="formReceita" style="display:none; margin-top:20px;">
    <form method="POST" action="{{ route('receitas.store') }}">
        @csrf

        <input type="text" name="nome" placeholder="Nome" value="{{ old('nome') }}" required><br><br>

        <textarea name="descricao" placeholder="Descricao">{{ old('descricao') }}</textarea><br><br>

        <input type="date" name="data_registro" value="{{ old('data_registro') }}" required><br><br>

        <input type="number" step="0.01" name="custo" placeholder="Custo" value="{{ old('custo') }}" required><br><br>

        <select name="tipo_receita" required>
            <option value="">Selecione</option>
            <option value="doce" {{ old('tipo_receita') === 'doce' ? 'selected' : '' }}>Doce</option>
            <option value="salgada" {{ old('tipo_receita') === 'salgada' ? 'selected' : '' }}>Salgada</option>
        </select><br><br>

        <select name="status" required>
            <option value="">Selecione o status</option>
            @foreach($statusOptions as $value => $label)
                <option value="{{ $value }}" {{ old('status', 'ATIVO') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select><br><br>

        <button type="submit">Salvar</button>
    </form>
</div>

<hr>

<table border="1">
    <tr>
        <th>Nome</th>
        <th>Descrição</th>
        <th>Data</th>
        <th>Custo</th>
        <th>Tipo</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>

    @forelse($receitas as $r)
    <tr>
        <td>{{ $r->nome }}</td>
        <td>{{ $r->descricao }}</td>
        <td>{{ optional($r->data_registro)->format('Y-m-d') }}</td>
        <td>{{ number_format((float) $r->custo, 2, ',', '.') }}</td>
        <td>{{ $r->tipo_receita }}</td>
        <td>{{ $r->status }}</td>
        <td>
            <button onclick="toggleEdit({{ $r->id }})">Editar</button>
            <form method="POST" action="{{ route('receitas.destroy', $r->id) }}" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit">Excluir</button>
            </form>
        </td>
    </tr>
    <tr id="edit-{{ $r->id }}" style="display:none;">
        <td colspan="8">
            <form method="POST" action="{{ route('receitas.update', $r->id) }}">
                @csrf
                @method('PUT')

                <input type="text" name="nome" value="{{ $r->nome }}" required>

                <input type="text" name="descricao" value="{{ $r->descricao }}" required>

                <input type="date" name="data_registro" value="{{ $r->data_registro }}" required>

                <input type="number" step="0.01" name="custo" value="{{ $r->custo }}" required>

                <select name="tipo_receita" required>
                    <option value="doce" {{ $r->tipo_receita == 'doce' ? 'selected' : '' }}>Doce</option>
                    <option value="salgada" {{ $r->tipo_receita == 'salgada' ? 'selected' : '' }}>Salgada</option>
                </select>

                <select name="status" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ $r->status === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <button type="submit">Salvar</button>
            </form>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="8">Nenhuma receita encontrada.</td>
    </tr>
    @endforelse
</table>

<script>
function toggleEdit(id) {
    let el = document.getElementById('edit-' + id);
    el.style.display = (el.style.display === 'none') ? 'table-row' : 'none';
}
</script>

</body>
</html>
