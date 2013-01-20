<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Viewer extends CI_Controller {
    
    public function __construct() 
    {
      parent::__construct();
      $this->load->library('template');
    }
	
    public function index()
    {
      $pdf_url = urldecode( $this->input->get('url') );
      
      if(empty($pdf_url)) 
      {
        $this->template->set('sitename', 'Happy-PDF-Viewer - ' . basename($pdf_url)); 
        redirect('viewer/generate_link');
      }
      else 
      {          
        $this->template->set('sitename', 'Happy-PDF-Viewer'); 
        $this->template->render();          
      }
    }
    
    public function generate_link()
    {      
      $this->load->view('viewer/generate_link');
    }
}
