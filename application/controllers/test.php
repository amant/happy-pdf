<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {
	
	public function index()
	{   
        // https://docs.google.com/viewer?url=http%3A%2F%2Fwww.stluciadance.com%2Fprospectus_file%2Fsample.pdf
        
        chmod('./public/output/pdf/4fc376753495b-*.pdf', 0777);
                
//        $output = array();
//        $return = 0;
//        exec('pdftk http://localhost/happy-pdf/a.pdf dumpdata 2>&1', $output, $return);
//        
//        print_r($output);
//        print_r($return);
	}
    
    public function gs()
    {
        
                $image_extension = '.png';
		$sdevice         = 'png16m';
        $output_resolution = '288';
        $file = basename($pdf_file, '.pdf');

        for($n = 1; $n <= $pages; $n++)
        {
            $input_filename = $this->output_dir . $file . "-$n.pdf";
            $output_filename = $this->output_dir . $file . "-$n" . $image_extension;

            $gs = $this->config->item('gs', 'bin');
            $command = "$gs -dSAFER -dBATCH -dNOPAUSE -dQUIET -dFirstPage=1 -dLastPage=1 -r$output_resolution -sDEVICE=$sdevice -sOutputFile=$output_filename $input_filename 2>&1";
            //$command = "$convert -units PixelsPerInch -density $density -quality $quality -resize $resolution $input_filename $output_filename 2>&1";
            $output  = array();
            $return  = 0;
            exec($command, $output, $return);

            chmod($output_filename, 0777);

            $this->_create_thumbnails($output_filename);
        }
        
    }
}