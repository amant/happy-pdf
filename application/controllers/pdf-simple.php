<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pdf extends CI_Controller {

    private $output_dir = './public/output/pdf/';
    private $download_dir = './public/output/download/';
    private $pdf_pages = 0;
    
    private $resolutions = array(
            '138',
            '240',
            '400',
            '507',
            '800',
            '1034',
            //'1200'
        );

	function __construct()
	{
       parent::__construct();
	}
    
    // TODO: just for testing, remove later
    private function _get_image_data($pdf_file, $page)
    {
        $thumbnails = array();
        $page = 5;
        
        for($i = 0 ; $i < $page; $i++) 
        {            
            $page_no = $i + 1;                
            
            $count = 0;
            
            foreach($this->resolutions as $resolution)
            {
                $image_details = getimagesize("./template/images/4fc3997f26ddc-$page_no-$resolution.jpg");
                
                $thumbnails[$i][$count] = array(
                    'src' => base_url() . "template/images/4fc3997f26ddc-$page_no-$resolution.jpg",
                    'w' => $image_details[0].'px',
                    'h' => $image_details[1].'px'
                    
                );
                
                $count += 1;
            }        
        }
      
        return $thumbnails;
    }
    
    // construct thumbnail element from image_data
    private function _get_thumb_element(array $image_data) 
    {
      $images = array();
      
      // only get the thumbnails version of image from image_data
      $count = 0;
      foreach ($image_data as $key => $value) {        
        $images[$count]  = $value[0];        
        $count += 1;
      }
            
      $data['images'] = $images;
      
      ob_start();
      $this->load->view('viewer/_thumbpane', $data);
      $out = ob_get_contents();
      ob_end_clean();
      
      return $out;      
    }
    
    private function _get_page_element(array $image_data, $pagefit) {
      $images = array();
      
      // get best fit resolution by comparing with pagefit value
      $resolution = 0;
      foreach ($this->resolutions as $key => $value) {
        if($value > $pagefit) {
          break;
        }
        
        if($pagefit > $value) {
          $resolution = $key;          
        }
      }
      
      // only get the thumbnails version of image from image_data
      $count = 0;
      foreach ($image_data as $key => $value) {        
        $images[$count]  = $value[$resolution];        
        $images[$count]['thumb_src'] = $value[0]['src'];
        $count += 1;
      }
      
      $data['images'] = $images;
      
      ob_start();
      $this->load->view('viewer/_pagepane', $data);
      $out = ob_get_contents();
      ob_end_clean();
      
      return $out;    
    }

    public function index()
    {
        $pdf_url = urldecode( $this->input->get('url') );
        
        $pagefit = urldecode( $this->input->get('pagefit') );    
        
        $data = array(
            'err' => true,
            'msg' => 'Could not read pdf file'
        );

        $pdf_file = $this->_download_pdf($pdf_url);
        
        if ($pdf_file !== false)
        {
            // split pdf documents into single pdf page
            $this->pdf_pages = $this->_split_pdf_page($pdf_file);
            
            if ($this->pdf_pages !== false)
            {                
                // convert pdf into different resolution jpg files
                $image_data = $this->_cnv_pdf_to_img($pdf_file, $this->pdf_pages); 
                
                if ($image_data !== array())
                {
                  $data = array(
                      'thumb-element-html' => $this->_get_thumb_element($image_data),
                      'page-pane-html' => $this->_get_page_element($image_data, $pagefit),
                      'images' => $image_data,
                      'resolution' => $this->resolutions,
                      'total-page' => $this->pdf_pages
                  );                    
                }
            }
        }

        echo json_encode($data);      
    }
        
//	public function index()
//	{
//        $data = aray(
//                    'err' => true,
//                    'msg' => 'Could not read pdf file'
//                );
//
//		$pdf_url = urldecode( $this->input->get('url') );
//
//        $pdf_file = $this->_download_pdf($pdf_url);
//
//        if ($pdf_file !== false)
//        {
//            $this->pdf_pages = $this->_split_pdf_page($pdf_file);
//            if ($this->pdf_pages !== false)
//            {
//                $thumbnails = $this->_cnv_pdf_to_img($pdf_file, $this->pdf_pages);
//
//                if ($thumbnails !== array())
//                {
//                    $data = array(
//                        'err' => false,
//                        'msg' => 'PDF file okay',
//                        'images' => $thumbnails,
//                        'pages' => $this->pdf_pages
//                    );
//                }
//            }
//        }
//
//        echo json_encode($data);
//	}

	private function _split_pdf_page($pdf_file)
	{
		$total_pages = $this->_get_pdf_total_page($pdf_file);

		if ($total_pages !== false)
		{
            // Pattern for output filename:
            // example: outputfile-1.pdf, outputfile-3.pdf, outputfile-3.pdf
            // The %d is where we be putting page numbers
            $filename  = basename($pdf_file, ".pdf");
            $output_filename  = $this->output_dir. $filename . "-%d.pdf";

            // Get the executable for PDFTK
            $pdftk = $this->config->item('pdftk', 'bin');

            $command = "$pdftk $pdf_file burst output $output_filename 2>&1";

            //TODO: fix doc_data.txt that is generated by pdftk and tried to store in readonly dir
            $output  = array();
            $return  = 0;
            exec($command, $output, $return);

            exec("chmod 777 {$this->output_dir}$filename-*.pdf");
		}

		return $total_pages;
	}

    private function _download_pdf($url_pdf)
    {
        $filename = uniqid();
        $newfile = $this->download_dir . $filename . '.pdf';

        if (! copy($url_pdf, $newfile) )
        {
            return false;
        }

        chmod($newfile, 0777);

        return $newfile;
    }

	private function _get_pdf_total_page($filename)
	{
		$pdftk = $this->config->item('pdftk', 'bin');
        $text_search = $this->config->item('grep', 'bin') . ' NumberOfPages';

		$command = $pdftk . ' ' . $filename . ' dump_data|' . $text_search;

		$output  = array();
		$return  = 0;
		exec($command, $output, $return);

		if (preg_match('/^NumberOfPages: ([0-9]{1,})$/m', $output[0], $matches))
		{
			return (int) $matches[1];
		}
		else
		{
			return false;
		}
	}

    // TODO: make this thing work in parallel, at the moment it's a bottelneck
	private function _cnv_pdf_to_img($pdf_file, $page)
	{
        $file = basename($pdf_file, '.pdf');

        $data = array();

        $count = 0;
        for($n = 1; $n <= $page; $n++)
        {
            $output_filename = $this->output_dir . $file . "-$n.pdf";
            $data[$count] = $this->_create_thumbnails($output_filename);
            $count += 1;
        }

        return $data;
	}

    // TODO: make this function parallel and faster
	private function _create_thumbnails($filename)
	{        
        $density = '72x72';
        $quality = '60';
        $extension = '.jpg';

        $data = array();

        $count = 0;
        
        foreach($this->resolutions as $resolution)
        {
            $input_filename = $filename;
            
            $file = basename($filename, '.pdf');
            $output_filename = $this->output_dir . $file . '-' . $resolution . $extension;

            $convert = $this->config->item('convert', 'bin');
            $command = "$convert -units PixelsPerInch -density $density -quality $quality -resize $resolution $input_filename $output_filename 2>&1";
            $output  = array();
            $return  = 0;
            exec($command, $output, $return);

            $image_detail = getimagesize($output_filename);            
            
            $data[$count] = array(
                'src' => base_url() . "public/output/pdf/" . basename($output_filename),
                'w' => $image_detail[0].'px',
                'h' => $image_detail[1]. 'px'
            );
            
            $count += 1;

            chmod($output_filename, 0777);
        }

        return $data;
	}
    
    // TODO: delete
    function test() {
      
      $pdf_url = urldecode( $this->input->get('url') );
      $pagefit = urldecode( $this->input->get('pagefit') );    
      $pages = 5;

      $image_data = $this->_get_image_data();

      $data = array(
          'thumb-element-html' => $this->_get_thumb_element($image_data),
          'page-pane-html' => $this->_get_page_element($image_data, $pagefit),
          'images' => $image_data,
          'resolution' => $this->resolutions,
          'total-page' => $pages
      );
        
        
    }
    
    // TODO: delete
    function test_pdf() 
    {        
        $this->load->helper('url_helper');
        
        $resolutions = array(
            '138',
            '240',
            '400',
            '507',
            '800',
            '1034',
            '1200'
        );
        
        $thumbnails = array();
        $pages = 5;
        
        for($i = 0 ; $i < $pages; $i++) 
        {            
            $page_no = $i + 1;                
            
            $count = 0;
            
            foreach($resolutions as $resolution)
            {
                $image_details = getimagesize("./template/images/4fc3997f26ddc-$page_no-$resolution.jpg");
                
                $thumbnails[$i][$count] = array(
                    'src' => base_url() . "template/images/4fc3997f26ddc-$page_no-$resolution.jpg",
                    'w' => $image_details[0].'px',
                    'h' => $image_details[1].'px'
                    
                );
                
                $count += 1;
            }        
        }
                
        $data = array(
                    'err' => false,
                    'msg' => 'PDF file okay',
                    'images' => $thumbnails,
                    'pages' => $pages
                    );
        
        echo json_encode($data);        
    }
}