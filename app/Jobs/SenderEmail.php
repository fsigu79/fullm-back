<?php

namespace App\Jobs;

use App\Mail\InvoiceEmail;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SenderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;
    protected $view;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoice, $view)
    {
        $this->invoice = $invoice;
        $this->view = $view;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $company = Company::find(1);
        $email = new InvoiceEmail($this->invoice->xml, $company, $this->invoice, $this->view);
        $email->build();
    }
}
