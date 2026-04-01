<!DOCTYPE html>
<html>
<head>
    <title>Receitas</title>
</head>
<body>

<h1>Lista de Receitas</h1>

<button onclick="document.getElementById('formReceita').style.display='block'">
    Adicionar Receita
</button>

<div id="formReceita" style="display:none; margin-top:20px;">
    <form method="POST" action="/receitas">
        @csrf

        <input type="text" name="nome" placeholder="Nome" required><br><br>

        <textarea name="descricao" placeholder="Descrição" required></textarea><br><br>

        <input type="date" name="data_registro" required><br><br>

        <input type="number" step="0.01" name="custo" placeholder="Custo" required><br><br>

        <select name="tipo_receita" required>
            <option value="">Selecione</option>
            <option value="doce">Doce</option>
            <option value="salgada">Salgada</option>
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
        <th>Ações</th>
    </tr>

    @foreach($receitas as $r)
    <tr>
        <td>{{ $r->nome }}</td>
        <td>{{ $r->descricao }}</td>
        <td>{{ $r->data_registro }}</td>
        <td>{{ $r->custo }}</td>
        <td>{{ $r->tipo_receita }}</td>
        <td>
            <button onclick="toggleEdit({{ $r->id }})">Editar</button>
            <form method="POST" action="/receitas/{{ $r->id }}" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit">Excluir</button>
            </form>
        </td>
    </tr>
    <tr id="edit-{{ $r->id }}" style="display:none;">
        <td colspan="7">
            <form method="POST" action="/receitas/{{ $r->id }}">
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

                <button type="submit">Salvar</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>

<script>
function toggleEdit(id) {
    let el = document.getElementById('edit-' + id);
    el.style.display = (el.style.display === 'none') ? 'table-row' : 'none';
}
</script>

</body>
</html>