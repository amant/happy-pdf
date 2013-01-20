<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pdf extends CI_Controller
{
    private $output_dir = './public/output/pdf/';
    private $download_dir = './public/output/download/';
    private $pdf_pages = 0;

    private $resolutions = array('138', '240', '400', '507', '800', '1034', '1200');

    function __construct()
    {
        parent::__construct();
    }

    // TODO: make this thing work in parallel, at the moment it's a bottelneck
    private function _cnv_pdf_to_img($pdf_file, $page)
    {
        $file = basename($pdf_file, '.pdf');

        $data = array();

        $count = 0;
        for ($n = 1; $n <= $page; $n++)
        {
            $output_filename = $this->output_dir . $file . "-$n.pdf";
            $data[$count]    = $this->_create_thumbnails($output_filename);
            $count += 1;
        }

        return $data;
    }

    private function _count_file($dir)
    {
        $out     = '';
        $ret     = '';
        $command = 'ls ' . $dir . '*-138.jpg|wc -l';
        exec($command, $out, $ret);

        if (isset($out[0]))
        {
            return $out[0];
        }
        else
        {
            return false;
        }
    }

    // TODO: make this function parallel and faster
    private function _create_thumbnails($filename)
    {
        $density   = '72x72';
        $quality   = '60';
        $extension = '.jpg';

        $data = array();

        $count = 0;

        foreach ($this->resolutions as $resolution)
        {
            $input_filename = $filename;

            $file            = basename($filename, '.pdf');
            $output_filename = $this->output_dir . $file . '-' . $resolution . $extension;

            $convert = $this->config->item('convert', 'bin');
            $command = "$convert -units PixelsPerInch -density $density -quality $quality -resize $resolution -quiet -background white -layers merge $input_filename $output_filename 2>&1";
            $output  = array();
            $return  = 0;
            exec($command, $output, $return);

            $image_detail = getimagesize($output_filename);

            $data[$count] = array(
                'src' => base_url() . trim($this->output_dir, ".") . basename($output_filename),
                'w' => $image_detail[0] . 'px',
                'h' => $image_detail[1] . 'px'
            );

            $count += 1;

            chmod($output_filename, 0777);
        }

        return $data;
    }

    private function _download_pdf($url_pdf)
    {
      /*$filename = uniqid();
        $newfile  = $this->download_dir . $filename . '.pdf';*/
        
        // create unique name using to download pdf's url
        $token = md5($url_pdf);
        $newfile  = $this->download_dir . $token . '.pdf';

        if (!copy($url_pdf, $newfile))
        {
            return false;
        }

        chmod($newfile, 0777);

        return $newfile;
    }

    private function _get_first_file($dir)
    {
        $out     = '';
        $ret     = '';
        $command = 'ls ' . $dir . ' |head -n 1';
        exec($command, $out, $ret);

        if (isset($out[0]))
        {
            return $out[0];
        }
        else
        {
            return false;
        }
    }

    // TODO: just for testing, remove later
    private function _get_image_data($pdf_file, $page)
    {
        $thumbnails = array();
        $page       = 5;

        for ($i = 0; $i < $page; $i++)
        {
            $page_no = $i + 1;

            $count = 0;

            foreach ($this->resolutions as $resolution)
            {
                $image_details = getimagesize("./template/images/4fc3997f26ddc-$page_no-$resolution.jpg");

                $thumbnails[$i][$count] = array(
                    'src' => base_url() . "template/images/4fc3997f26ddc-$page_no-$resolution.jpg",
                    'w' => $image_details[0] . 'px',
                    'h' => $image_details[1] . 'px'

                );

                $count += 1;
            }
        }

        return $thumbnails;
    }

    private function _get_page_element(array $image_data, $pagefit)
    {
        $images = array();

        // get best fit resolution by comparing with pagefit value
        $resolution = 0;
        foreach ($this->resolutions as $key => $value)
        {
            if ($value > $pagefit)
            {
                break;
            }

            if ($pagefit > $value)
            {
                $resolution = $key;
            }
        }

        // only get the thumbnails version of image from image_data
        $count = 0;
        foreach ($image_data as $key => $value)
        {
            $images[$count]              = $value[$resolution];
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

    private function _get_pdf_total_page($filename)
    {
        $pdftk       = $this->config->item('pdftk', 'bin');
        $text_search = $this->config->item('grep', 'bin') . ' NumberOfPages';

        $command = $pdftk . ' ' . $filename . ' dump_data|' . $text_search;

        $output = array();
        $return = 0;
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

    // construct thumbnail element from image_data
    private function _get_thumb_element(array $image_data)
    {
        $images = array();

        // only get the thumbnails version of image from image_data
        $count = 0;
        foreach ($image_data as $key => $value)
        {
            $images[$count] = $value[0];
            $count += 1;
        }

        $data['images'] = $images;

        ob_start();
        $this->load->view('viewer/_thumbpane', $data);
        $out = ob_get_contents();
        ob_end_clean();

        return $out;
    }

    private function _load_cache($pagefit)
    {
        $this->load->helper('url_helper');

        $image_data = array();

        $pages = $this->_count_file($this->output_dir);

        // get one of the file from hard-disk, *-138.jpg
        $first_file = $this->_get_first_file($this->output_dir);
        $name       = explode('-', $first_file);
        $filename   = $name[0];

        $guid = basename($this->output_dir);

        for ($i = 0; $i < $pages; $i++)
        {
            $count   = 0;
            $page_no = $i + 1;
            foreach ($this->resolutions as $resolution)
            {
				$image_data[$i][$count] = array(
                    'src' => base_url() . "pdf/img/$guid/$filename/$page_no/$resolution",
                    'w' => $resolution . 'px',
                    'h' => '195px'
                );

                /*$image_details = getimagesize($this->output_dir . "$filename-$page_no-$resolution.jpg");

                $image_data[$i][$count] = array(
                    'src' => base_url() . trim($this->output_dir, ".") . "$filename-$page_no-$resolution.jpg",
                    'w' => $image_details[0] . 'px',
                    'h' => $image_details[1] . 'px'
                );*/

                $count += 1;
            }
        }

        $data = array(
            'guid' => $guid,
            'thumb-element-html' => $this->_get_thumb_element($image_data),
            'page-pane-html' => $this->_get_page_element($image_data, $pagefit),
            'images' => $image_data,
            'resolution' => $this->resolutions,
            'total-page' => $pages
        );

        return $data;
    }

    // TODO:
    // create cache of file data so it can be retrive easily without having to re-process file
    // to speed up process and to save CPU cycle using mysql or mongodb ?
    private function _process_file($pdf_url, $pagefit)
    {
        mkdir($this->output_dir);
        exec("chmod 777 {$this->output_dir}");

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

                    return $data;
                }
            }
        }

        return false;
    }

    private function _process_file_fast($pdf_url, $pagefit)
    {
        mkdir($this->output_dir);
        exec("chmod 777 {$this->output_dir}");

        $pdf_file = $this->_download_pdf($pdf_url);

        if ($pdf_file !== false)
        {
            $out     = '';
            $ret     = '';
            $command = "sudo ./bin/split_pdf.rb $pdf_file {$this->output_dir}";
            exec($command, $out, $ret);

            if (isset($out[0]))
            {
              return $this->_load_cache($pagefit);
            }
            else
            {
                return false;
            }
        }

        return false;
    }

    private function _property_output_dir($dir_name = "")
    {
        if ($dir_name !== "")
        {
            $this->output_dir .= $dir_name . '/';
        }
        else
        {
            return $this->output_dir;
        }
    }

    private function _split_pdf_page($pdf_file)
    {
        $total_pages = $this->_get_pdf_total_page($pdf_file);

        if ($total_pages !== false)
        {
            // Pattern for output filename:
            // example: outputfile-1.pdf, outputfile-3.pdf, outputfile-3.pdf
            // The %d is where we be putting page numbers
            $filename        = basename($pdf_file, ".pdf");
            $output_filename = $this->output_dir . $filename . "-%d.pdf";

            // Get the executable for PDFTK
            $pdftk = $this->config->item('pdftk', 'bin');

            $command = "$pdftk $pdf_file burst output $output_filename 2>&1";

            //TODO: fix doc_data.txt that is generated by pdftk and tried to store in readonly dir
            $output = array();
            $return = 0;
            exec($command, $output, $return);

            exec("chmod 777 {$this->output_dir}$filename-*.pdf");
        }

        return $total_pages;
    }

    public function index()
    {
        $pdf_url = urldecode($this->input->get('url'));

        $pagefit = urldecode($this->input->get('pagefit'));

        $data = array(
            'err' => true,
            'msg' => 'Could not read pdf file'
        );

        // set output directory
        $token = md5($pdf_url);
        $this->_property_output_dir($token);

        // check if particular file from that url has been already processed
        if (file_exists($this->_property_output_dir()) === true)
        {
            // load from cache
            $data = $this->_load_cache($pagefit);
        }
        else
        {
            // process file
            //$data = $this->_process_file($pdf_url, $pagefit);
            $data = $this->_process_file_fast($pdf_url, $pagefit);
        }

        echo json_encode($data);
    }

    // TODO: delete
    function test()
    {
        $pdf_url = urldecode($this->input->get('url'));
        $pagefit = urldecode($this->input->get('pagefit'));
        $pages   = 5;

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
        $pages      = 5;

        for ($i = 0; $i < $pages; $i++)
        {
            $page_no = $i + 1;

            $count = 0;

            foreach ($resolutions as $resolution)
            {
                $image_details = getimagesize("./template/images/4fc3997f26ddc-$page_no-$resolution.jpg");

                $thumbnails[$i][$count] = array(
                    'src' => base_url() . "template/images/4fc3997f26ddc-$page_no-$resolution.jpg",
                    'w' => $image_details[0] . 'px',
                    'h' => $image_details[1] . 'px'

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

	function img($guid, $file, $pageno, $resolution)
	{
		$this->_property_output_dir($guid);

		$path = $this->output_dir . "$file-$pageno-$resolution.jpg";

		// check if file exist
		if( !file_exists($path) )
		{
			$pdf = $this->output_dir . "$file-$pageno.pdf";
			if ( $this->_create_thumbnail($pdf, $resolution) === false )
			{
				show_404();
				die();
			}
		}

		// get image content
		if ( $this->get_img_content($path) === false )
		{
			show_404();
		}

		die();
	}

	function get_img_content($path)
	{
		if ( !file_exists($path) )
		{
			return false;
		}

		$x = explode('.', $path);
		$extension = end($x);


		// Load the mime types
		if (defined('ENVIRONMENT') AND is_file(APPPATH.'config/'.ENVIRONMENT.'/mimes.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/mimes.php');
		}
		elseif (is_file(APPPATH.'config/mimes.php'))
		{
			include(APPPATH.'config/mimes.php');
		}

		// Set a default mime if we can't find it
		if ( ! isset($mimes[$extension]))
		{
			$mime = 'application/octet-stream';
		}
		else
		{
			$mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
		}

		$data = file_get_contents($path);

		header("Content-Type: " . $mime);
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . strlen($data));
		exit($data);
	}

	private function _create_thumbnail($filename, $resolution)
    {
        $density   = '72x72';
        $quality   = '60';
        $extension = '.jpg';

        $data = array();

		$input_filename = $filename;

		$file            = basename($filename, '.pdf');
		$output_filename = $this->output_dir . $file . '-' . $resolution . $extension;

		$output  = array();
		$return  = 0;

//		$convert = $this->config->item('convert', 'bin');
//		$command = "convert -units PixelsPerInch -density $density -quality $quality -resize $resolution -quiet -background white -layers merge $input_filename $output_filename 2>&1";
//		exec($command, $output, $return);

		$command = "sudo ./bin/convert.sh $density $quality $resolution $input_filename $output_filename 2>&1";
		exec($command, $output, $return);

		if ($return === 0)
		{
//			$image_detail = getimagesize($output_filename);
//			chmod($output_filename, 0777);
			return $output_filename;
		}

		return false;
    }

  //convert pdf2xml
  public function to_xml($guid)
  {    
    $guid = urldecode($guid);
    $targ = $this->download_dir . "{$guid}.pdf";
    $dest = $this->output_dir . "{$guid}/{$guid}.xml";

    if ( file_exists($targ) && file_exists($dest) )
    {
      // read from cache
      $this->_get_pdf_text($dest);
    }
    else
    {
      // process pdftohtml
      $command = "sudo ./bin/pdf2html.sh $targ $dest 2>&1";
      exec($command, $output, $return);

      if ($return === 0)
      {
        $this->_get_pdf_text($dest);
      }
      else
      {
        echo "Error reading pdf text file";
      }
    }
  }
  
  // output pdf xml file
  private function _get_pdf_text($path) 
  {
    header('Content-Type: text/xml');
    echo file_get_contents($path);
  }
}
