<?php

namespace App\Support;

class SimplePdf
{
    public static function fromLines(array $lines): string
    {
        $content = self::buildContent($lines);

        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 /MediaBox [0 0 612 792] >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >> endobj';
        $objects[] = '4 0 obj << /Length ' . strlen($content) . ' >> stream' . "\n" . $content . "\nendstream endobj";
        $objects[] = '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= 'xref' . "\n";
        $pdf .= '0 ' . count($offsets) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < count($offsets); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= 'trailer << /Size ' . count($offsets) . ' /Root 1 0 R >>' . "\n";
        $pdf .= 'startxref' . "\n" . $xrefPosition . "\n%%EOF";

        return $pdf;
    }

    protected static function buildContent(array $lines): string
    {
        $cursor = 760;
        $buffer = "BT\n/F1 12 Tf\n";

        foreach ($lines as $line) {
            $text = self::escape((string) $line);
            $buffer .= sprintf("1 0 0 1 50 %d Tm (%s) Tj\n", $cursor, $text);
            $cursor -= 16;
        }

        $buffer .= "ET";

        return $buffer;
    }

    protected static function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
