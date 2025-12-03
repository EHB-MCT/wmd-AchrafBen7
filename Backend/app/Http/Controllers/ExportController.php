<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsDashboardService;
use App\Support\SimplePdf;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(private AnalyticsDashboardService $analytics)
    {
    }

    public function kpisCsv(Request $request)
    {
        $range = (string) $request->query('range', '7d');
        $snapshot = $this->analytics->kpiSnapshot($range);

        $rows = [
            ['Section', 'Metric', 'Value'],
            ['Overview', 'Sessions', $snapshot['overview']['sessions']],
            ['Overview', 'Events', $snapshot['overview']['events']],
            ['Overview', 'Conversions', $snapshot['overview']['conversions']],
            ['Overview', 'Average duration', $snapshot['overview']['average_duration']],
        ];

        foreach ($snapshot['top_conversion_pages'] as $page) {
            $rows[] = ['Top conversion page', $page['screen'], $page['total']];
        }

        foreach ($snapshot['search']['top_queries'] as $query) {
            $rows[] = ['Search', $query['phrase'], $query['volume']];
        }

        $headers = ['Content-Type' => 'text/csv'];

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, 'nios-kpis.csv', $headers);
    }

    public function kpisPdf(Request $request)
    {
        $range = (string) $request->query('range', '7d');
        $snapshot = $this->analytics->kpiSnapshot($range);

        $lines = [
            'NiOS Analytics – KPI Export',
            'Période: ' . implode(' → ', $snapshot['current_period']),
            'Sessions: ' . $snapshot['overview']['sessions'],
            'Events: ' . $snapshot['overview']['events'],
            'Conversions: ' . $snapshot['overview']['conversions'],
            'Durée moyenne: ' . $snapshot['overview']['average_duration'],
            'Top conversions:',
        ];

        foreach ($snapshot['top_conversion_pages'] as $page) {
            $lines[] = sprintf('- %s: %d', $page['screen'], $page['total']);
        }

        $lines[] = 'Search performance:';

        foreach ($snapshot['search']['top_queries'] as $query) {
            $lines[] = sprintf('- %s (%d)', $query['phrase'], $query['volume']);
        }

        $pdf = SimplePdf::fromLines($lines);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="nios-kpis.pdf"',
        ]);
    }
}
