<?php

namespace App\Http\Controllers;

use App\Mail\ReceitaNotificationMail;
use App\Models\Receita;
use App\Services\ReceitaPdfExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ReceitaController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $receitas = Receita::query()
            ->filter($filters)
            ->orderByDesc('data_registro')
            ->orderByDesc('id')
            ->get();

        return view('receitas', [
            'receitas' => $receitas,
            'filters' => $filters,
            'statusOptions' => Receita::statusOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $receita = Receita::create($this->validatedData($request));

        $this->sendNotification($receita, 'created');

        return redirect()
            ->route('receitas.index')
            ->with('success', 'Receita criada com sucesso.');
    }

    public function destroy($id)
    {
        $receita = Receita::findOrFail($id);

        $this->sendNotification($receita, 'deleted');

        $receita->delete();

        return redirect()
            ->route('receitas.index')
            ->with('success', 'Receita excluida com sucesso.');
    }

    public function update(Request $request, $id)
    {
        $receita = Receita::findOrFail($id);
        $receita->update($this->validatedData($request));

        return redirect()
            ->route('receitas.index')
            ->with('success', 'Receita atualizada com sucesso.');
    }

    public function exportPdf(Request $request, ReceitaPdfExporter $exporter)
    {
        $filters = $this->validatedFilters($request);

        $receitas = Receita::query()
            ->filter($filters)
            ->orderByDesc('data_registro')
            ->orderByDesc('id')
            ->get();

        $pdf = $exporter->render($receitas, $filters);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receitas.pdf"',
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'descricao' => ['nullable', 'string'],
            'data_registro' => ['required', 'date'],
            'custo' => ['required', 'numeric', 'min:0'],
            'tipo_receita' => ['required', Rule::in(['doce', 'salgada'])],
            'status' => ['required', Rule::in(array_keys(Receita::statusOptions()))],
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'data_inicial' => ['nullable', 'date'],
            'data_final' => ['nullable', 'date', 'after_or_equal:data_inicial'],
            'status' => ['nullable', Rule::in(array_keys(Receita::statusOptions()))],
        ]);
    }

    private function sendNotification(Receita $receita, string $action): void
    {
        $recipient = data_get(session('usuario'), 'email');

        if (! $recipient) {
            return;
        }

        Mail::to($recipient)->send(new ReceitaNotificationMail($receita, $action));
    }
}
