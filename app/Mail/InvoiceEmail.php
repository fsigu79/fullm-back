<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use PDF;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $xmlContent;
    protected $pdfView;
    protected $company;
    protected $invoice;

    /**
     * Create a new message instance.
     *
     * @param string $xmlContent
     * @param string $pdfView
     * @return void
     */
    public function __construct($xmlContent,  $company,  $invoice,  $pdfView)
    {
        $this->xmlContent = $xmlContent;
        $this->company = $company;
        $this->invoice = $invoice;
        $this->pdfView = $pdfView;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        try {
            // $encryption = $this->company->email_ssl == 1 ? 'ssl' : 'tls';
            // $transport = new Swift_SmtpTransport($this->company->email_host, $this->company->email_port, $encryption);

            // // Configurar las credenciales de autenticación
            // $transport->setUsername($this->company->email_user);
            // $transport->setPassword($this->company->decriptPassword($this->company->email_password));

            // // Crear un objeto Mailer
            // $mailer = new Swift_Mailer($transport);

            // // Configurar el objeto Mailer predeterminado en Laravel
            // Mail::setSwiftMailer($mailer);

            // // Crear el mensaje HTML
            // $htmlContent = $this->company->email_message; //'<p>Hola {{CLIENTE}}, adjunto encontrarás el archivo XML y el archivo PDF.</p>';
            // $htmlContent = str_replace('{{CLIENTE}}', $this->invoice->cliente, $htmlContent);
            // $htmlContent = str_replace('{{FACTURA}}', $this->invoice->serie . "-" . str_pad((int)$this->invoice->numero, 9, '0', STR_PAD_LEFT), $htmlContent);
            // $htmlContent = str_replace('{{AUTORIZACION}}', $this->invoice->autorizacion, $htmlContent);
            // $htmlContent = str_replace('{{RAZONSOCIAL}}', $this->company->social_name, $htmlContent);
            // $htmlContent = str_replace('{{COMPANIA}}', $this->company->name, $htmlContent);

            // $data = [
            //     'invoice' => $this->invoice,
            //     'company' => $this->company
            // ];

            // $pdfContent = PDF::loadView($this->pdfView, $data)->output();
            // $xmlContent = $this->xmlContent;
            // $invoice = $this->invoice;
            // $company = $this->company;

            // // Enviar el correo electrónico
            // Mail::send([], [], function ($message) use ($htmlContent, $xmlContent, $pdfContent, $invoice, $company) {
            //     $message->to($invoice->email)
            //         ->subject($company->email_subject)
            //         ->setBody($htmlContent, 'text/html')
            //         ->attach(new Swift_Attachment($xmlContent, 'invoice.xml', 'application/xml'))
            //         ->attach(new Swift_Attachment($pdfContent, 'invoice.pdf', 'application/pdf'));
            // });

            $data = [
                'invoice' => $this->invoice,
                'company' => $this->company
            ];

            $company = $this->company;
            $invoice = $this->invoice;

            $pdfContent = PDF::loadView($this->pdfView, $data)->output();
            $xmlContent = $this->xmlContent;

            $htmlContent = $this->company->email_message;
            $htmlContent = str_replace('{{CLIENTE}}', $this->invoice->cliente, $htmlContent);
            $htmlContent = str_replace('{{FACTURA}}', $this->invoice->serie . "-" . str_pad((int)$this->invoice->numero, 9, '0', STR_PAD_LEFT), $htmlContent);
            $htmlContent = str_replace('{{AUTORIZACION}}', $this->invoice->autorizacion, $htmlContent);
            $htmlContent = str_replace('{{RAZONSOCIAL}}', $this->company->social_name, $htmlContent);
            $htmlContent = str_replace('{{COMPANIA}}', $this->company->name, $htmlContent);

            Mail::send([], [], function (Message $message) use ($invoice, $company, $pdfContent, $xmlContent, $htmlContent) {
                $message->to($invoice->email, $invoice->cliente)
                    ->subject($company->email_subject)
                    ->attachData($pdfContent, $invoice->autorizacion.'.pdf', ['mime' => 'application/pdf'])
                    ->attachData($xmlContent, $invoice->autorizacion.'.xml', ['mime' => 'application/xml'])
                    ->setBody($htmlContent, 'text/html');;
            });

            return true;
        } catch (\Swift_TransportException $e) {
            // Capturar errores relacionados con el transporte SMTP (por ejemplo, problemas de conexión)
            throw new \Exception("Error connecting SMTP: " . $e->getMessage());
        } catch (\Exception $e) {
            // Capturar cualquier otra excepción
            throw new \Exception("Error: " . $e->getMessage());
        }
    }
}
