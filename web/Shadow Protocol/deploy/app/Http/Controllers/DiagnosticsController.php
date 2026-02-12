<?php

namespace App\Http\Controllers;

use App\Services\NodeValidator;
use App\Services\SystemDiagnostics;
use Illuminate\Http\Request;

class DiagnosticsController extends Controller
{
    public function __construct(
        private SystemDiagnostics $diagnostics,
        private NodeValidator $validator
    ) {}

    public function index(Request $request)
    {
        $systemStatus = $this->diagnostics->getSystemStatus();
        $nodeList = $this->diagnostics->getRegisteredNodes();

        return view('diagnostics.index', [
            'systemStatus' => $systemStatus,
            'nodeList' => $nodeList,
        ]);
    }
}
