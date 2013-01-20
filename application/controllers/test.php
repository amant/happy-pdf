<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {
	
	private $output_dir = './docs/lab/img/';
	private $resolutions = array(
            '138',
            //'240',
            //'400',
            //'507',
            '800',
            //'1034',
            //'1200'
        );
	
	public function index()
	{   
		$density   = '72x72';
        $quality   = '60';
        $resolution = 138;
        		
		$command = "sh /opt/lampp/htdocs/happy-pdf/bin/convert.sh";
		
		system($command);
		die();
		
		//$out = array();
		//$ret = 0;
		//exec($command, $out, $ret);
		
		//print_r($ret);
		
		//http://www.imagemagick.org/Usage/api/#personal
		//putenv("PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin");
		//putenv("LD_LIBRARY_PATH=/usr/local/lib");
		
		//system('gs -dNOPAUSE -sDEVICE=jpeg -dFirstPage='.$params['startPage'].' -dLastPage='.$params['endPage'].' -sOutputFile=image'.$params['thread'].'-%d.jpg -dJPEGQ=100 -r300x300 -q book.pdf -c quit', $result);
		//system('gs -dNOPAUSE -sDEVICE=jpeg -dFirstPage=1 -dLastPage=1 -sOutputFile=trouble.jpg -dJPEGQ=100 -r300x300 -q a.pdf -c quit', $result);
		//$command = "gs -q -sDEVICE=jpeg -dBATCH -dNOPAUSE -dFirstPage=1 -dLastPage=1 -r300 -sOutputFile=trouble.jpg trouble.pdf";
		//system($command);
		
		//exec("PATH=/usr/bin");
		//putenv("PATH=/usr/local/bin:/usr/bin:/bin");
		//print_r(getenv("PATH"));
		//echo "\n";
		//die();
		
		$density   = '72x72';
        $quality   = '60';
        $extension = '.jpg';
        $resolution = 138;
        
		$input_filename = './trouble.pdf';		
		$output_filename = './trouble.jpg';
				
		//$command = "gs -dNOPAUSE -sDEVICE=jpeg -dFirstPage=1 -dLastPage=237 -sOutputFile=image%d.jpg -dJPEGQ=100 -r300x300 -q $input_filename.pdf -c quit 2>&1";		
		$command = "gs -dNOPAUSE -sDEVICE=jpeg -dFirstPage=1 -dLastPage=1 -sOutputFile=$output_filename -dJPEGQ=100 -r300x300 -q $input_filename.pdf -c quit 2>&1";		
		//$command = "convert -units PixelsPerInch -density $density -quality $quality -resize $resolution -quiet -background white -layers merge $input_filename $output_filename 2>&1";		
		$output  = array();
		$return  = 0;
		print_r($command);
		flush();				
		system($command);
		
		//exec($command, $output, $return);
				
		print_r($output);
		flush();
		die();
				
       
        
		echo '<img src="http://localhost/happy-pdf/pdf/img/a716a22081e137d1af4b4ef0ffdd9f57/4ff0714e45729/1/138" />';
		die();
		
        // https://docs.google.com/viewer?url=http%3A%2F%2Fwww.stluciadance.com%2Fprospectus_file%2Fsample.pdf        
        $this->_load_cache();
	}
	
	private function _count_file($dir)
    {
		$out = '';
		$ret = '';
		$command = 'ls ' . $dir . '*-138.jpg|wc -l';
        exec($command, $out, $ret);
        
        if(isset($out[0])) 
        {
			return $out[0];
		}
        else
        {
			return false;
		}
	}
	
	private function _get_first_file($dir)
	{
		$out = '';
		$ret = '';
		$command = 'ls ' . $dir . ' |head -n 1';
        exec($command, $out, $ret);
        
        if(isset($out[0])) 
        {
			return $out[0];
		}
        else
        {
			return false;
		}
	}
    
    private function _load_cache()
    {
		$this->load->helper('url_helper');
		
        $thumbnails = array();
        
        $pages = $this->_count_file($this->output_dir);        
        
        // get one of the file from hard-disk, *-138.jpg
        $first_file = $this->_get_first_file($this->output_dir);
        $name = explode('-', $first_file);
        $filename = $name[0];        
        
        for($i = 0 ; $i < $pages; $i++) 
        {            
            $count = 0;
            
            foreach($this->resolutions as $resolution)
            {
                $image_details = getimagesize($this->output_dir . "$filename-$i-$resolution.jpg");
                
                $thumbnails[$i][$count] = array(
                    'src' => base_url() . trim($this->output_dir, ".") . "$filename-$i-$resolution.jpg",
                    'w' => $image_details[0].'px',
                    'h' => $image_details[1].'px'
                );
                
                $count += 1;
            }
        }
                
        $data = array(                    
                    'images' => $thumbnails,
                    'pages' => $pages
                    );
        
		return $data;
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
