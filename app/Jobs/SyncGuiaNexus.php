<?php

namespace App\Jobs;

use App\Models\GuiaRemisionDetalleDwh;
use App\Models\GuiaRemisionDwh;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncGuiaNexus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $input;

    public function __construct(array $input)
    {
        $this->input = $input;
    }

    public function handle(): void
    {
        $input = $this->input;

        $guiaw = GuiaRemisionDwh::where('numero', $input['numero'])
            ->where('documento', $input['documento'])
            ->where('serie', $input['serie'])
            ->where('empresa', 'FULLMOTOS')
            ->first();

        //add empresa FULLMOTOS
        $input['empresa'] = 'FULLMOTOS';

        if (!$guiaw) {
            $guiaw = new GuiaRemisionDwh($input);
            $guiaw->numero = $input['numero'];
            $guiaw->save();
        } else {
            $guiaw->update($input);
        }

        $guiaw->detalle()->delete();

        foreach ($input['detalle'] as $detalle) {
            $detalleObj = new GuiaRemisionDetalleDwh($detalle);
            $detalleObj->guiar_id = $guiaw->id;
            $detalleObj->documento = $guiaw->documento;
            $detalleObj->numero = $guiaw->numero;
            $detalleObj->save();
        }
    }
}
