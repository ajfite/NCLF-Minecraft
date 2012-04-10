<?php
/*
 * NCLFMinecraft.org
 * Copyright (C) 2012  A.J. Fite
 */
class News extends CI_Controller {
	
    public function __construct() {
		parent::__construct();
		$this->load->model('news_model');
		$this->load->library('ion_auth');
		$this->load->library('session');
	}
	
	public function index() {
		$data['news'] = $this->news_model->get_news();
		$data['title'] = 'News archive';
		$data['style'] = array('SiteWide','Header','Navigation','News','Footer','Body');
		
		$this->load->view('templates/header',$data);
		$this->load->view('templates/navigation',$data);
		$this->load->view('templates/Body/start');
		$this->load->view('news/index', $data);
		$this->load->view('templates/Body/end');
		$this->load->view('templates/footer', $data);
	}
	
	public function view($slug) {
		$data['news_item'] = $this->news_model->get_news($slug);
		
		if (empty($data['news_item'])) {
			show_404();
		}
		
		$data['title'] = $data['news_item']['title'];
		$data['style'] = array('SiteWide','Header','Navigation','News','Footer','Body');
				
		$this->load->view('templates/header',$data);
		$this->load->view('templates/navigation',$data);
		$this->load->view('templates/Body/start');
		$this->load->view('news/view', $data);
		$this->load->view('templates/Body/end');
		$this->load->view('templates/footer', $data);
	}
	
	public function create() {
		$this->load->helper('form');
		$this->load->library('form_validation');
				
		$data['title'] = 'create a news item';
		$data['style'] = array('SiteWide','Header','Navigation','News','Footer','Body');		
		
		//FIXME: Author field and date
		$this->form_validation->set_rules('title', 'Title', 'required');
		$this->form_validation->set_rules('text', 'text', 'required');
		
		if($this->ion_auth->is_admin()) {				
			if($this->form_validation->run() === FALSE) {
				$this->load->view('templates/header', $data);
				$this->load->view('templates/navigation',$data);
				$this->load->view('templates/Body/start');
				$this->load->view('news/create');
				$this->load->view('templates/Body/end');
				$this->load->view('templates/footer');
			} else {
				$this->news_model->set_news();
				//FIXME: Better success page
				$this->load->view('news/success');
			}
		} elseif($this->ion_auth->logged_in()) {
			//FIXME: Error page necessary
			redirect('/error/notadmin.html');
		} else {
			//FIXME: You must be logged in error
			redirect('/auth/login.html');
		} 
	}
	
	public function feed() {
		$this->load->helper('xml');
		
		$data['feed_name'] = 'NCLF Minecraft News';
		$data['encoding'] = 'utf-8';
		$data['feed_url'] = 'http://mc.nclf.net/news.html';
		$data['page_description'] = 'News and alerts relating to the NCLF Minecraft server';
		$data['page_language'] = 'en-us';
		$data['creator_email'] = 'webmaster@nclf.net';
		$data['feed_category'] = 'Minecraft/news/alerts';
		$data['page_ttl'] = '30';
		$data['posts'] = $this->news_model->get_news(FALSE,15);
		header("Content-Type: application/rss+xml");
		
		$this->load->view('news/RSS', $data);
	}
}