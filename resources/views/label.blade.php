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
      .table td,.table th{padding:.1rem;vertical-align:top;border-top:1px solid}
      .collapse:not(.show){display:none}
      .collapsing{position:relative;height:0;overflow:hidden;transition:height .35s ease}
      .media{display:-ms-flexbox;display:flex;-ms-flex-align:start;align-items:flex-start}
      .media-body{-ms-flex:1;flex:1}
      .align-baseline{vertical-align:baseline!important}
      .align-top{vertical-align:top!important}
      .align-middle{vertical-align:middle!important}
      .align-bottom{vertical-align:bottom!important}
      .align-text-bottom{vertical-align:text-bottom!important}
      .align-text-top{vertical-align:text-top!important}
      .bg-white{background-color:#fff!important}
      .bg-transparent{background-color:transparent!important}
      .border{border:1px solid #dee2e6!important}
      .border-top{border-top:1px solid #dee2e6!important}
      .border-right{border-right:1px solid #dee2e6!important}
      .border-bottom{border-bottom:1px solid #dee2e6!important}
      .border-left{border-left:1px solid #dee2e6!important}
      .border-0{border:0!important}
      .border-top-0{border-top:0!important}
      .border-right-0{border-right:0!important}
      .border-bottom-0{border-bottom:0!important}
      .border-left-0{border-left:0!important}
      .rounded-sm{border-radius:.2rem!important}
      .rounded{border-radius:.25rem!important}
      .rounded-top{border-top-left-radius:.25rem!important;border-top-right-radius:.25rem!important}
      .rounded-right{border-top-right-radius:.25rem!important;border-bottom-right-radius:.25rem!important}
      .rounded-bottom{border-bottom-right-radius:.25rem!important;border-bottom-left-radius:.25rem!important}
      .rounded-left{border-top-left-radius:.25rem!important;border-bottom-left-radius:.25rem!important}
      .rounded-lg{border-radius:.3rem!important}
      .rounded-circle{border-radius:50%!important}
      .rounded-pill{border-radius:50rem!important}
      .rounded-0{border-radius:0!important}
      .clearfix::after{display:block;clear:both;content:""}
      .d-none{display:none!important}
      .d-inline{display:inline!important}
      .d-inline-block{display:inline-block!important}
      .d-block{display:block!important}
      .d-table{display:table!important}
      .d-table-row{display:table-row!important}
      .d-table-cell{display:table-cell!important}
      .d-flex{display:-ms-flexbox!important;display:flex!important}
      .d-inline-flex{display:-ms-inline-flexbox!important;display:inline-flex!important}
      .flex-row{-ms-flex-direction:row!important;flex-direction:row!important}
      .flex-column{-ms-flex-direction:column!important;flex-direction:column!important}
      .flex-row-reverse{-ms-flex-direction:row-reverse!important;flex-direction:row-reverse!important}
      .flex-column-reverse{-ms-flex-direction:column-reverse!important;flex-direction:column-reverse!important}
      .flex-wrap{-ms-flex-wrap:wrap!important;flex-wrap:wrap!important}
      .flex-nowrap{-ms-flex-wrap:nowrap!important;flex-wrap:nowrap!important}
      .flex-wrap-reverse{-ms-flex-wrap:wrap-reverse!important;flex-wrap:wrap-reverse!important}
      .flex-fill{-ms-flex:1 1 auto!important;flex:1 1 auto!important}
      .flex-grow-0{-ms-flex-positive:0!important;flex-grow:0!important}
      .flex-grow-1{-ms-flex-positive:1!important;flex-grow:1!important}
      .flex-shrink-0{-ms-flex-negative:0!important;flex-shrink:0!important}
      .flex-shrink-1{-ms-flex-negative:1!important;flex-shrink:1!important}
      .justify-content-start{-ms-flex-pack:start!important;justify-content:flex-start!important}
      .justify-content-end{-ms-flex-pack:end!important;justify-content:flex-end!important}
      .justify-content-center{-ms-flex-pack:center!important;justify-content:center!important}
      .justify-content-between{-ms-flex-pack:justify!important;justify-content:space-between!important}
      .justify-content-around{-ms-flex-pack:distribute!important;justify-content:space-around!important}
      .align-items-start{-ms-flex-align:start!important;align-items:flex-start!important}
      .align-items-end{-ms-flex-align:end!important;align-items:flex-end!important}
      .align-items-center{-ms-flex-align:center!important;align-items:center!important}
      .align-items-baseline{-ms-flex-align:baseline!important;align-items:baseline!important}
      .align-items-stretch{-ms-flex-align:stretch!important;align-items:stretch!important}
      .align-content-start{-ms-flex-line-pack:start!important;align-content:flex-start!important}
      .align-content-end{-ms-flex-line-pack:end!important;align-content:flex-end!important}
      .align-content-center{-ms-flex-line-pack:center!important;align-content:center!important}
      .align-content-between{-ms-flex-line-pack:justify!important;align-content:space-between!important}
      .align-content-around{-ms-flex-line-pack:distribute!important;align-content:space-around!important}
      .align-content-stretch{-ms-flex-line-pack:stretch!important;align-content:stretch!important}
      .align-self-auto{-ms-flex-item-align:auto!important;align-self:auto!important}
      .align-self-start{-ms-flex-item-align:start!important;align-self:flex-start!important}
      .align-self-end{-ms-flex-item-align:end!important;align-self:flex-end!important}
      .align-self-center{-ms-flex-item-align:center!important;align-self:center!important}
      .align-self-baseline{-ms-flex-item-align:baseline!important;align-self:baseline!important}
      .align-self-stretch{-ms-flex-item-align:stretch!important;align-self:stretch!important}
      .float-left{float:left!important}
      .float-right{float:right!important}
      .float-none{float:none!important}
      .overflow-auto{overflow:auto!important}
      .overflow-hidden{overflow:hidden!important}
      .position-static{position:static!important}
      .position-relative{position:relative!important}
      .position-absolute{position:absolute!important}
      .position-fixed{position:fixed!important}
      .position-sticky{position:-webkit-sticky!important;position:sticky!important}
      .fixed-top{position:fixed;top:0;right:0;left:0;z-index:1030}
      .fixed-bottom{position:fixed;right:0;bottom:0;left:0;z-index:1030}
      .w-25{width:25%!important}
      .w-50{width:50%!important}
      .w-75{width:75%!important}
      .w-100{width:100%!important}
      .w-auto{width:auto!important}
      .h-25{height:25%!important}
      .h-50{height:50%!important}
      .h-75{height:75%!important}
      .h-100{height:100%!important}
      .h-auto{height:auto!important}
      .mw-100{max-width:100%!important}
      .mh-100{max-height:100%!important}
      .min-vw-100{min-width:100vw!important}
      .min-vh-100{min-height:100vh!important}
      .vw-100{width:100vw!important}
      .vh-100{height:100vh!important}
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
      .p-0{padding:0!important}
      .pt-0,.py-0{padding-top:0!important}
      .pr-0,.px-0{padding-right:0!important}
      .pb-0,.py-0{padding-bottom:0!important}
      .pl-0,.px-0{padding-left:0!important}
      .p-1{padding:.25rem!important}
      .pt-1,.py-1{padding-top:.25rem!important}
      .pr-1,.px-1{padding-right:.25rem!important}
      .pb-1,.py-1{padding-bottom:.25rem!important}
      .pl-1,.px-1{padding-left:.25rem!important}
      .p-2{padding:.5rem!important}
      .pt-2,.py-2{padding-top:.5rem!important}
      .pr-2,.px-2{padding-right:.5rem!important}
      .pb-2,.py-2{padding-bottom:.5rem!important}
      .pl-2,.px-2{padding-left:.5rem!important}
      .p-3{padding:1rem!important}
      .pt-3,.py-3{padding-top:1rem!important}
      .pr-3,.px-3{padding-right:1rem!important}
      .pb-3,.py-3{padding-bottom:1rem!important}
      .pl-3,.px-3{padding-left:1rem!important}
      .p-4{padding:1.5rem!important}
      .pt-4,.py-4{padding-top:1.5rem!important}
      .pr-4,.px-4{padding-right:1.5rem!important}
      .pb-4,.py-4{padding-bottom:1.5rem!important}
      .pl-4,.px-4{padding-left:1.5rem!important}
      .p-5{padding:3rem!important}
      .pt-5,.py-5{padding-top:3rem!important}
      .pr-5,.px-5{padding-right:3rem!important}
      .pb-5,.py-5{padding-bottom:3rem!important}
      .pl-5,.px-5{padding-left:3rem!important}
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
        margin-top: 0.5cm;
        margin-left: 1cm;
        margin-right: 1cm;
        margin-bottom: 0.5cm;
      }

      #logo {
        vertical-align: middle;
        margin: 0px 80px;
      }

      /* table > tbody > tr {
        font-size: 12px;
      } */

      .header-column, .content-column {
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
      }

      div.break-page {
        page-break-inside:avoid;
        page-break-after:always;
      }

      div.break-page:last-child {
        page-break-inside:avoid;
        page-break-after:avoid;
      }
    </style>
  </head>

  <body>
    @foreach($order as $prod)
      <div class="row text-center" style="font-size: 11px;">
        <b>JCEV</b>
      </div>

      <div class="row text-center" style="font-size: 11px;">
       <b>{{ $prod->producto}}</b>
      </div>

      <div class="row text-center">
          @if($prod->codigo != null)
            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($prod->codigo, 'C39')}}" alt="barcode"  height="50" width="300"/>
          @endif
      </div>

      <div class="row text-center" style="font-size: 12px;">
        <b>{{ $prod->codigo}}</b>
      </div>


      <div class="break-page"></div>
    @endforeach
  </body>
</html>
