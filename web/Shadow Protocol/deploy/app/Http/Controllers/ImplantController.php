<?php

namespace App\Http\Controllers;

use App\Services\ImplantRegistry;
use App\Services\NodeValidator;
use Illuminate\Http\Request;

class ImplantController extends Controller
{
    public function __construct(
        private ImplantRegistry $registry,
        private NodeValidator $validator
    ) {}

    public function show(string $id)
    {
        $implant = $this->registry->findImplant($id);

        if (!$implant) {
            abort(404);
        }

        $telemetry = $this->registry->getTelemetryHistory($id);
        $integrity = $this->registry->verifyIntegrity($id);

        return view('implant.show', [
            'implant' => $implant,
            'telemetry' => $telemetry,
            'integrity' => $integrity,
        ]);
    }
}
