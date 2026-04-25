<?php

namespace App\Services;

use App\Models\Receita;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReceitaPdfExporter
{
    public function render(Collection $receitas, array $filters = []): string
    {
        $lines = [
            'Relatorio de Receitas',
            'Gerado em: ' . now()->format('d/m/Y H:i'),
            'Filtros: ' . $this->formatFilters($filters),
            str_repeat('-', 90),
        ];

        foreach ($receitas as $receita) {
            /** @var Receita $receita */
            $lines[] = sprintf(
                '%s | %s | %s | R$ %s | %s | %s',
                optional($receita->data_registro)->format('d/m/Y'),
                $receita->nome,
                $receita->descricao ?: '-',
                number_format((float) $receita->custo, 2, ',', '.'),
                ucfirst($receita->tipo_receita),
                ucfirst($receita->status),
            );
        }

        if ($receitas->isEmpty()) {
            $lines[] = 'Nenhum lancamento encontrado para os filtros informados.';
        }

        return $this->buildPdf($lines);
    }

    private function formatFilters(array $filters): string
    {
        $parts = [];

        if (filled($filters['data_inicial'] ?? null)) {
            $parts[] = 'Data inicial: ' . date('d/m/Y', strtotime($filters['data_inicial']));
        }

        if (filled($filters['data_final'] ?? null)) {
            $parts[] = 'Data final: ' . date('d/m/Y', strtotime($filters['data_final']));
        }

        if (filled($filters['status'] ?? null)) {
            $parts[] = 'Status: ' . Str::headline($filters['status']);
        }

        return $parts === [] ? 'Sem filtros' : implode(' | ', $parts);
    }

    private function buildPdf(array $lines): string
    {
        $wrappedLines = [];

        foreach ($lines as $line) {
            foreach ($this->wrapLine($line, 90) as $wrappedLine) {
                $wrappedLines[] = $wrappedLine;
            }
        }

        $pages = array_chunk($wrappedLines, 45);
        $pageCount = count($pages);

        $fontObjectId = 3 + ($pageCount * 2);
        $objects = [];

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $kids = [];
        foreach (range(0, $pageCount - 1) as $index) {
            $pageObjectId = 3 + ($index * 2);
            $kids[] = $pageObjectId . ' 0 R';
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $pageCount . ' >>';

        foreach ($pages as $index => $pageLines) {
            $pageObjectId = 3 + ($index * 2);
            $contentObjectId = $pageObjectId + 1;

            $content = implode("\n", [
                'BT',
                '/F1 11 Tf',
                '14 TL',
                '1 0 0 1 40 800 Tm',
                ...array_map(
                    fn (string $line) => '(' . $this->escapePdfString($line) . ') Tj T*',
                    $pageLines
                ),
                'ET',
            ]);

            $objects[$pageObjectId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 ' . $fontObjectId . ' 0 R >> >> /Contents ' . $contentObjectId . ' 0 R >>';
            $objects[$contentObjectId] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        }

        $objects[$fontObjectId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= '0 ' . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_keys($objects) as $id) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }

        $pdf .= "trailer\n";
        $pdf .= '<< /Size ' . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n";
        $pdf .= $xrefPosition . "\n";
        $pdf .= "%%EOF";

        return $pdf;
    }

    private function wrapLine(string $line, int $length): array
    {
        $wrapped = wordwrap($line, $length, "\n", true);

        return explode("\n", $wrapped);
    }

    private function escapePdfString(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', $value);
        $value = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $value) ?: $value;

        return addcslashes($value, '\\()');
    }
}
