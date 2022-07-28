<html>
  <head>    
    <style>
      /* Start Boostrap */
      html{
        font-family:sans-serif;
        line-height:1.15;
        -webkit-text-size-adjust:100%;
        -webkit-tap-highlight-color:transparent
      }
      article,aside,figcaption,figure,footer,header,hgroup,main,nav,section{display:block}
      body{
        margin:0;
        font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
        font-size:1rem;
        font-weight:400;
        line-height:1.5;
        color:#212529;
        text-align:left;
        background-color:#fff
      }
      hr{box-sizing:content-box;height:0;overflow:visible}
      h1,h2,h3,h4,h5,h6{margin-top:0;margin-bottom:.5rem}
      p{margin-top:0;margin-bottom:1rem}
      blockquote{margin:0 0 1rem}
      b,strong{font-weight:bolder}
      small{font-size:80%}
      img{vertical-align:middle;border-style:none}
      svg{overflow:hidden;vertical-align:middle}
      table{border-collapse:collapse}
      caption{
        padding-top:.75rem;
        padding-bottom:.75rem;
        color:#6c757d;
        text-align:left;
        caption-side:bottom
      }
      th{text-align:inherit}
      label{display:inline-block;margin-bottom:.5rem}
      fieldset{min-width:0;padding:0;margin:0;border:0}
      legend{display:block;width:100%;max-width:100%;padding:0;margin-bottom:.5rem;font-size:1.5rem;line-height:inherit;color:inherit;white-space:normal}
      template{display:none}[hidden]{display:none!important}
      .h1,.h2,.h3,.h4,.h5,.h6,h1,h2,h3,h4,h5,h6{margin-bottom:.5rem;font-weight:500;line-height:1.2}
      .h1,h1{font-size:2.5rem}
      .h2,h2{font-size:2rem}
      .h3,h3{font-size:1.75rem}
      .h4,h4{font-size:1.5rem}
      .h5,h5{font-size:1.25rem}
      .h6,h6{font-size:1rem}
      .lead{font-size:1.25rem;font-weight:300}
      hr{margin-top:1rem;margin-bottom:1rem;border:0;border-top:1px solid rgba(0,0,0,.1)}
      .img-fluid{max-width:100%;height:auto}
      .img-thumbnail{padding:.25rem;background-color:#fff;border:1px solid #dee2e6;border-radius:.25rem;max-width:100%;height:auto}
      .figure{display:inline-block}
      .figure-img{margin-bottom:.5rem;line-height:1}
      .figure-caption{font-size:90%;color:#6c757d}code{font-size:87.5%;color:#e83e8c;word-wrap:break-word}
      a>code{color:inherit}
      .container{width:100%;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto}
      .container-fluid,.container-lg,.container-md,.container-sm,.container-xl{width:100%;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto}
      .row{display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap;margin-right:-15px;margin-left:-15px}
      .col,.col-1,.col-10,.col-11,.col-12,.col-2,.col-3,.col-4,.col-5,.col-6,.col-7,.col-8,.col-9,.col-auto,.col-lg,.col-lg-1,.col-lg-10,.col-lg-11,.col-lg-12,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-5,.col-lg-6,.col-lg-7,.col-lg-8,.col-lg-9,.col-lg-auto,.col-md,.col-md-1,.col-md-10,.col-md-11,.col-md-12,.col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7,.col-md-8,.col-md-9,.col-md-auto,.col-sm,.col-sm-1,.col-sm-10,.col-sm-11,.col-sm-12,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-5,.col-sm-6,.col-sm-7,.col-sm-8,.col-sm-9,.col-sm-auto,.col-xl,.col-xl-1,.col-xl-10,.col-xl-11,.col-xl-12,.col-xl-2,.col-xl-3,.col-xl-4,.col-xl-5,.col-xl-6,.col-xl-7,.col-xl-8,.col-xl-9,.col-xl-auto{position:relative;width:100%;padding-right:15px;padding-left:15px}
      .col{-ms-flex-preferred-size:0;flex-basis:0;-ms-flex-positive:1;flex-grow:1;min-width:0;max-width:100%}
      .row-cols-1>*{-ms-flex:0 0 100%;flex:0 0 100%;max-width:100%}
      .row-cols-2>*{-ms-flex:0 0 50%;flex:0 0 50%;max-width:50%}
      .row-cols-3>*{-ms-flex:0 0 33.333333%;flex:0 0 33.333333%;max-width:33.333333%}
      .row-cols-4>*{-ms-flex:0 0 25%;flex:0 0 25%;max-width:25%}
      .row-cols-5>*{-ms-flex:0 0 20%;flex:0 0 20%;max-width:20%}
      .row-cols-6>*{-ms-flex:0 0 16.666667%;flex:0 0 16.666667%;max-width:16.666667%}
      .col-auto{-ms-flex:0 0 auto;flex:0 0 auto;width:auto;max-width:100%}
      .col-1{-ms-flex:0 0 8.333333%;flex:0 0 8.333333%;max-width:8.333333%}
      .col-2{-ms-flex:0 0 16.666667%;flex:0 0 16.666667%;max-width:16.666667%}
      .col-3{-ms-flex:0 0 25%;flex:0 0 25%;max-width:25%}
      .col-4{-ms-flex:0 0 33.333333%;flex:0 0 33.333333%;max-width:33.333333%}
      .col-5{-ms-flex:0 0 41.666667%;flex:0 0 41.666667%;max-width:41.666667%}
      .col-6{-ms-flex:0 0 50%;flex:0 0 50%;max-width:50%}
      .col-7{-ms-flex:0 0 58.333333%;flex:0 0 58.333333%;max-width:58.333333%}
      .col-8{-ms-flex:0 0 66.666667%;flex:0 0 66.666667%;max-width:66.666667%}
      .col-9{-ms-flex:0 0 75%;flex:0 0 75%;max-width:75%}
      .col-10{-ms-flex:0 0 83.333333%;flex:0 0 83.333333%;max-width:83.333333%}
      .col-11{-ms-flex:0 0 91.666667%;flex:0 0 91.666667%;max-width:91.666667%}
      .col-12{-ms-flex:0 0 100%;flex:0 0 100%;max-width:100%}
      .table{width:100%;}
      .table td,.table th{padding:.1rem; padding-left:.75rem;vertical-align:top;border-top:1px solid #dee2e6}
      .m-0{margin:0!important}
      .mt-0,.my-0{margin-top:0!important}
      .mr-0,.mx-0{margin-right:0!important}
      .mb-0,.my-0{margin-bottom:0!important}
      .ml-0,.mx-0{margin-left:0!important}
      .m-1{margin:.25rem!important}
      .mt-1,.my-1{margin-top:.25rem!important}
      .mr-1,.mx-1{margin-right:.25rem!important}
      .mb-1,.my-1{margin-bottom:.25rem!important}
      .ml-1,.mx-1{margin-left:.25rem!important}
      .m-2{margin:.5rem!important}
      .mt-2,.my-2{margin-top:.5rem!important}
      .mr-2,.mx-2{margin-right:.5rem!important}
      .mb-2,.my-2{margin-bottom:.5rem!important}
      .ml-2,.mx-2{margin-left:.5rem!important}
      .m-3{margin:1rem!important}
      .mt-3,.my-3{margin-top:1rem!important}
      .mr-3,.mx-3{margin-right:1rem!important}
      .mb-3,.my-3{margin-bottom:1rem!important}
      .ml-3,.mx-3{margin-left:1rem!important}
      .m-4{margin:1.5rem!important}
      .mt-4,.my-4{margin-top:1.5rem!important}
      .mr-4,.mx-4{margin-right:1.5rem!important}
      .mb-4,.my-4{margin-bottom:1.5rem!important}
      .ml-4,.mx-4{margin-left:1.5rem!important}
      .m-5{margin:3rem!important}
      .mt-5,.my-5{margin-top:3rem!important}
      .mr-5,.mx-5{margin-right:3rem!important}
      .mb-5,.my-5{margin-bottom:3rem!important}
      .ml-5,.mx-5{margin-left:3rem!important}
      .m-n1{margin:-.25rem!important}
      .mt-n1,.my-n1{margin-top:-.25rem!important}
      .mr-n1,.mx-n1{margin-right:-.25rem!important}
      .mb-n1,.my-n1{margin-bottom:-.25rem!important}
      .ml-n1,.mx-n1{margin-left:-.25rem!important}
      .m-n2{margin:-.5rem!important}
      .mt-n2,.my-n2{margin-top:-.5rem!important}
      .mr-n2,.mx-n2{margin-right:-.5rem!important}
      .mb-n2,.my-n2{margin-bottom:-.5rem!important}
      .ml-n2,.mx-n2{margin-left:-.5rem!important}
      .m-n3{margin:-1rem!important}
      .mt-n3,.my-n3{margin-top:-1rem!important}
      .mr-n3,.mx-n3{margin-right:-1rem!important}
      .mb-n3,.my-n3{margin-bottom:-1rem!important}
      .ml-n3,.mx-n3{margin-left:-1rem!important}
      .m-n4{margin:-1.5rem!important}
      .mt-n4,.my-n4{margin-top:-1.5rem!important}
      .mr-n4,.mx-n4{margin-right:-1.5rem!important}
      .mb-n4,.my-n4{margin-bottom:-1.5rem!important}
      .ml-n4,.mx-n4{margin-left:-1.5rem!important}
      .m-n5{margin:-3rem!important}
      .mt-n5,.my-n5{margin-top:-3rem!important}
      .mr-n5,.mx-n5{margin-right:-3rem!important}
      .mb-n5,.my-n5{margin-bottom:-3rem!important}
      .ml-n5,.mx-n5{margin-left:-3rem!important}
      .m-auto{margin:auto!important}
      .mt-auto,.my-auto{margin-top:auto!important}
      .mr-auto,.mx-auto{margin-right:auto!important}
      .mb-auto,.my-auto{margin-bottom:auto!important}
      .ml-auto,.mx-auto{margin-left:auto!important}
      .text-left{text-align:left!important}
      .text-right{text-align:right!important}
      .text-center{text-align:center!important}
      /* End Boostrap */

      @page {
        margin: 0cm 0cm;
      }

      body {
        margin-top: 2.5cm;
        margin-left: 1.5cm;
        margin-right: 1.5cm;
        margin-bottom: 1.5cm;
      }

      header {
        position: fixed;
        top: 30px;
        left: 0px;
        right: 0px;
        height: 50px;
      }

      #logo {
        vertical-align: middle;
        margin: 0px 80px;
      }

      table > thead > tr {
        font-size: 12px;
      }

      table > tbody > tr {
        font-size: 12px;
      }

      .header-column, .content-column {
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
      }

      .observation {
        border: 1px solid #dee2e6;
        font-size: 12px;
        margin: 0px !important;
        padding: 10px !important;
        text-align: left; 
      }

      label {
        font-size: 12px;
      }
    </style>
  </head>

  <body>
    <header>
      <div class="row">
        <img
          id="logo"
          loading="lazy"
          src="https://ecuadordirect.wpengine.com/wp-content/uploads/2019/08/logo-ecuador-direct-roses-1.png"
          width="75px"
          alt="Ecuador Direct Roses"
          title="Ecuador Direct Roses"
        />

        <div class="text-center">
          Ecuador Direct Roses
        </div>
      </div>
    </header>

    <main>
      <div class="row mb-3">
        <div class="col-12">
        <table class="table" style="table-layout: auto;">
            <tbody>
              <!--
              <tr>
                
                <td style="border-top: 0;"> <b>Número de orden:</b> </td>
                <td style="border-top: 0;"> <b>{{str_pad($order[0]->id, 5, "0", STR_PAD_LEFT);}}</b> </td>
              </tr>-->
              <tr>
                <!--<td class="header-column">Cliente</td>
                <td class="content-column">{{ $order[0]->client->name }}</td>-->
                <td class="header-column">Codigo EDR</td>
                <td class="content-column">{{ $order[0]->client->edr_code }}</td>

                <td style="border: 0; padding: .75rem;"></td>
                <td class="header-column">Fecha de orden</td>
                <td class="content-column">{{ $order[0]->created_at }}</td>
              </tr>
              <tr>
              <td class="header-column">Proveedor</td>
                <td class="content-column">{{ $order[0]->provider->name }}</td>
                <td style="border: 0; margin: 0;"></td>
                <td class="header-column">Día de entrega en carguera</td>
                <td class="content-column">{{ $order[0]->delivery_date }}</td>
              </tr>
              <tr>
              <td class="header-column">Agencia</td>
                <td class="content-column">{{ $order[0]->agency->name }}</td>
                <td style="border: 0; margin: 0;"></td>
                <td class="header-column">Día de vuelo</td>
                <td class="content-column">{{ $order[0]->flight_date }}</td>
              </tr>
              <tr>
              <td class="header-column">Cuarto frío</td>
                <td class="content-column">{{ $order[0]->cold_room->name }}</td>
                <td style="border: 0; margin: 0;"></td>
                <td class="header-column">Vendedor</td>
                <td class="content-column">{{ $order[0]->user->name }} {{ $order[0]->user->surname }}</td>
              </tr>
              <tr>
              <td class="header-column">AWB</td>
                <td class="content-column">{{ $order[0]->awb }}</td>
                <td style="border: 0; margin: 0;"></td>
                <td class="header-column">Estado</td>
                <td class="content-column">
                  @if($order[0]->status == 'R')
                    Registrado
                  @endif

                  @if($order[0]->status == 'C')
                    Confirmado
                  @endif

                  @if($order[0]->status == 'F')
                    Facturado
                  @endif

                  @if($order[0]->status == 'D')
                    Despachado
                  @endif
                  @if($order[0]->status == 'O')
                    Orden Fija
                  @endif
                </td>
              </tr>
             
            </tbody>
          </table>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <label><b>Observación</b></label>
          <p class="observation">
            {{ $order[0]->observation !== null ? $order[0]->observation : 'Sin observaciones'}}
          </p>
        </div>
      </div>

      <div class="row mt-4">
        <div class="col-12">
          <table class="table" style="table-layout: auto">
            <thead class="text-center">
              <!-- <tr>
                <th>Variedad</th>
                <th>Longitud</th>
                <th>No. de cajas</th>
                <th>Tipo de caja</th>
                <th>Precio</th>
                <th>Tallos</th>
                <th>Total</th>
              </tr> -->
            </thead>
            <tbody>
              @foreach($order[0]->orderBoxes as $order_box)
              <tr>
                <th class="header-column">Item</th>
                <th colspan="3" class="header-column">Tipo de caja</th>
                <th colspan="2" class="header-column">Número de cajas</th>
              </tr>
              <tr>
                <td class="text-center header-column">{{ $loop->index + 1 }}</td>
                <td colspan="3" class="text-center content-column">{{ $order_box->box->name }}</td>
                <td colspan="2" class="text-center content-column">{{ $order_box->box_number }}</td>
              </tr>
              <tr>
                <th class="header-column">Variedad</th>
                <th class="header-column">Longitud</th>
                <th class="header-column">Observación</th>
                <th class="header-column">Precio</th>
                <th class="header-column">Tallos</th>
                <th class="header-column">Total</th>
              </tr>
              @foreach($order_box->details as $detail)
              <tr>
                <td class="text-center content-column">{{ $detail->product->description }}</td>
                <td class="text-center content-column">{{ $detail->longitude }}</td>
                <td class="text-center content-column">{{ $detail->observation != null ? $detail->observation : 'Sin observación'  }}</td>
                <td class="text-center content-column">{{ number_format($detail->price, 2) }}</td>
                <td class="text-center content-column">{{ $detail->stems }}</td>
                <td class="text-center content-column"> $ {{ number_format($detail->total, 2) }}</td>
              </tr>
              @endforeach
              <tr>
                <td colspan="3"></td>
                <td class="header-column"><b>Total Item {{ $loop->index + 1 }}</b></td>
                <td class="text-center content-column">{{ $order_box->details->sum('stems') }}</td>
                <td class="text-center content-column">$ {{ number_format($order_box->details->sum('total'), 2) }}</td>
              </tr>
              <tr> 
                <td colspan="6" style="border: none; padding: .75rem"></td>
              </tr>
              @endforeach
              <tr>
                <td colspan="3" style="border-top: none;"></td>
                <td class="header-column"><b>Totales</b></td>
                <td class="text-center content-column">{{ $order[0]->total_stems }}</td>
                <td class="text-center content-column">$ {{ number_format($order[0]->total, 2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </body>
</html>
