<?php

require APPPATH . '/libraries/redis_bootstrap.php';

class Messages extends CI_Controller {

	public function Messages()
	{
		parent::__construct();	

	    $this->load->helper('url');
        $this->load->model('message_model');
	}
		
	public function index()
	{
	    $recent = $this->message_model->get_messages();
	    
	    $data = array(
	       'recent' => $recent,
	       'template' => 'messages/index.php'
	    );
	    
		$this->load->view('layout', $data);	
	}
	
	public function show($id)
	{
        $message = $this->message_model->get_message($id);

        $data = array(
            'message' => $message,
            'template' => 'messages/show.php'
        );
        
        $this->load->view('layout', $data);
	}
	
	public function new_message()
	{
	    $this->load->view('layout.php', array(
	       'template' => 'messages/new.php'
	    ));
	}

    public function create()
    {
        // save the message after post
        // redirect back to the home page
        $this->message_model->create($this->input->post('message'));
        
        redirect('/');
    }
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */