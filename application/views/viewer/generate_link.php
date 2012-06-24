<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>HappyPF -Viewer</title>
    <style>
      .error {
        background: none repeat scroll 0 0 #FDDFDE;
        border: 1px solid #FBC9C8;
        color: #CD0A0A;
        text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.3);
        border-radius: 6px 6px 6px 6px;
        font-weight: bold;
        padding: 15px;
        width: 800px;
        margin-left: auto;
        margin-right: auto;
      }
      
      .error-ui {
        background: none repeat scroll 0 0 #FDDFDE;
        border: 1px solid #FBC9C8;
        color: #CD0A0A;
        padding: 4px;
      }
      
      body {
        background: none repeat scroll 0 0 #D3D8E8;
        font-family: "lucida grande,tahoma,verdana,arial,sans-serif";
        font-size: 76%;
        margin: auto;        
      }
      
      .light {
          color: #676767;
          font-weight: normal;
      }
      
      .dark {
          color: #000000;
          font-weight: bold;
      }
      
      .text-input {
          margin-bottom: 12px;
          margin-top: 6px;
      }

      
      .api-instructions {
          background-color: #EEEEEE;
          border: 1px solid #CCCCCC;
          margin-top: 2em;
          padding: 12px;
      }
      
      .api-header {
          font-weight: bold;
      }
      
      .api-body {
          margin-top: 12px;
      }
      
      .api-param {
          color: #676767;
          font: 10pt Courier,Monospace;
      }
      
      dl {
        margin-top: 12px;
      }
      
      dt {
        color: #676767;
        font: 10pt Courier,Monospace;
      }
      
      dd {
        
      }
      
      .param-def {
          margin-top: 12px;
      }
      
      .api-param {
          color: #676767;
          font: 10pt Courier,Monospace;
      }

    </style>    
  </head>
  <body>
    <noscript><div class="error">Your browser must support javascript.</div></noscript>	
    <div style="width:100%; background: none repeat scroll 0 0 #3B5998; color:#fff; height: 50px; border: 0px solid red; margin: 0; padding: 0;">
      <h1 style="padding-top: 10px; padding-left: 15%">Happy PDF - Viewer</h1>
    </div>
    
    <div style="width:800px; display: block; margin-left: auto; margin-right: auto;">
      <h3>
        Use Happy PDF Viewer to quickly view documents online without leaving your browser.
      </h3>
      <div class="dark">Enter a documetn URL below to generate a link to view it</div>
      <div class="light">PDF documents,</div>
      
      <form name="frmForm" id="frmForm" method="get" action="">
        <div class="text-input">
          <input id="url" type="text" size="75" tabindex="1" placeholder="http://website.com/pdf-document.pdf" />
          <button id="go" tabindex="2" style="margin-left: 9px;">view</button>
        </div>
      </form>
      
      <div id="invalid_url" class="error-ui" style="display:none">invalid url</div>

      <div class="api-instructions">
        <div class="api-header"> Technical Documentation - Instructions for building your own URLs</div>
        <div class="api-body">All viewer URls should use the path <span>http://happypdf.com/viewer</span> . This path accepts parameter:</div>
        <dl>
          <dt>url : </dt>
          <dd>
              The URL of the document to view. This should be URL-encoded.
          </dd>
        </dl>

        <div class="param-def">For example, if you wanted to view the PDF at the URL  
          <span class="api-param">http://website.com/pdf-document.pdf</span> , you would use the URL:  
          <span class="api-param">http://happypdf.com/viewer?url=http%3A%2F%2Fwebsite.com%2pdf-document.pdf</span>
        </div>
      </div>
    </div>
    
    <script type="text/javascript" src="<?php echo base_url() ?>public/js/jquery-1.7.2.min.js"></script>
    <script>
      function isValidURL(str) {
        var urlregex = new RegExp("^(http|https|ftp)\://([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$");
        return urlregex.test(str);
      };
      
      $(document).ready(function() {
        $('#frmForm').submit(function(e){
          e.preventDefault();
          
          
          $('#invalid_url').hide();
          
          var url = $('#url').val();
          
          if( url != '' && isValidURL( url ) === true ) {
           window.location = 'http://localhost/happy-pdf/viewer/?url=' + encodeURIComponent(url);
           
          } else {
            $('#invalid_url').show();
            $('#url').focus();
          }
          
        });        
      });
    </script>
  </body>
</html>